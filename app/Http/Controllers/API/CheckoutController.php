<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\pharmacy_medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items')->where('user_id', $user->id)->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        // التحقق من البيانات المطلوبة
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|in:cash,card,wallet',
        ]);

        // جلب pharmacy_id من أول عنصر في السلة
        $pharmacyId = $cart->items->first()->pharmacy_id;

        // التحقق من أن جميع العناصر من نفس الصيدلية
        foreach ($cart->items as $item) {
            if ($item->pharmacy_id !== $pharmacyId) {
                return response()->json([
                    'message' => 'All items in cart must be from the same pharmacy'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // === 1. أولاً: التحقق من توفر جميع الأدوية والمخزون ===
            $itemsData = [];
            $total = 0;

            foreach ($cart->items as $item) {
                $pharmacyMedicine = pharmacy_medicine::where('pharmacy_id', $item->pharmacy_id)
                    ->where('medicine_id', $item->medicine_id)
                    ->lockForUpdate() // لمنع التنافس على المخزون
                    ->first();

                if (!$pharmacyMedicine) {
                    throw new \Exception('Medicine not available in pharmacy: ' . $item->medicine_id);
                }

                if ($pharmacyMedicine->stock < $item->quantity) {
                    throw new \Exception('Insufficient stock for medicine: ' . $item->medicine_id .
                        '. Available: ' . $pharmacyMedicine->stock .
                        ', Requested: ' . $item->quantity);
                }

                // حساب السعر
                $lineTotal = $item->quantity * $item->price_at_time;
                $total += $lineTotal;

                // تخزين البيانات للاستخدام لاحقاً
                $itemsData[] = [
                    'item' => $item,
                    'pharmacy_medicine' => $pharmacyMedicine,
                    'line_total' => $lineTotal,
                ];
            }

            // === 2. إنشاء الطلب بعد التأكد من كل شيء ===
            $order = Order::create([
                'user_id' => $user->id,
                'pharmacy_id' => $pharmacyId,
                'address_id' => $request->address_id,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'total_amount' => $total,
            ]);

            // === 3. إنشاء عناصر الطلب وتحديث المخزون ===
            foreach ($itemsData as $data) {
                $item = $data['item'];
                $pharmacyMedicine = $data['pharmacy_medicine'];

                // إنشاء عنصر الطلب
                OrderItem::create([
                    'order_id' => $order->id,
                    'medicine_id' => $item->medicine_id,
                    'pharmacy_medicine_id' => $pharmacyMedicine->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_time,
                ]);

                // تحديث المخزون
                $pharmacyMedicine->decrement('stock', $item->quantity);
            }

            // === 4. تفريغ السلة ===
            $cart->items()->delete();

            DB::commit();

            // تحميل العلاقات للإرجاع
       //$order->load('address');
            $order->load(['address' => function($query) {
                $query->select('id', 'title', 'area', 'street', 'building_number',
                    'floor', 'apartment', 'additional_info', 'is_default');
            }]);

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'total_amount' => $total,
                'address' => $order->address,
                'payment_method' => $order->payment_method,
                'items_count' => count($itemsData)
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            // Business-rule failures (insufficient stock, product no longer
            // available) are normal, expected client-facing conditions -
            // they were previously all returned as 500 (server error),
            // which is misleading and prevents the frontend from
            // distinguishing "you can't do that right now" from "something
            // actually broke".
            if (str_contains($e->getMessage(), 'not available')) {
                return response()->json([
                    'message' => 'Product no longer available',
                    'error' => $e->getMessage(),
                ], 422);
            }

            if (str_contains($e->getMessage(), 'Insufficient stock')) {
                return response()->json([
                    'message' => 'Insufficient stock for some items',
                    'error' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'message' => 'Error creating order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
