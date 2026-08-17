<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\pharmacy_medicine;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // عرض السلة للمستخدم الحالي
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.medicine','items.pharmacy');

        // تنسيق البيانات لإضافة اسم الصيدلية بشكل واضح
        $formattedCart = [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
            'items' => $cart->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'medicine_id' => $item->medicine_id,
                    'pharmacy_id' => $item->pharmacy_id,
                    'quantity' => $item->quantity,
                    'price_at_time' => $item->price_at_time,
                    'medicine' => $item->medicine ? [
                        'id' => $item->medicine->id,
                        'name' => $item->medicine->name,
                        'description' => $item->medicine->description,
                        'image' => $item->medicine->image,
                    ] : null,
                    'pharmacy' => $item->pharmacy ? [
                        'id' => $item->pharmacy->id,
                        'name' => $item->pharmacy->pharmacy_name,
                        'address' => $item->pharmacy->address,
                    ] : null,
                    'pharmacy_name' => $item->pharmacy ? $item->pharmacy->pharmacy_name : null, // 👈 إضافة مباشرة
                ];
            }),
            'total_items' => $cart->items->count(),
            'total_quantity' => $cart->items->sum('quantity'),
            'total_price' => $cart->items->sum(function($item) {
                return $item->quantity * $item->price_at_time;
            }),
        ];

        return response()->json($formattedCart);
    }

    // إضافة منتج للسلة أو زيادة الكمية
//    public function add(Request $request)
//    {
//        $request->validate([
//            'pharmacy_medicine_id' => 'required|exists:pharmacy_medicines,id',
//            'quantity' => 'required|integer|min:1',
//        ]);
//
//        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
//
//        $pm = pharmacy_medicine::findOrFail($request->pharmacy_medicine_id);
//
//        // منع سلة من أكثر من صيدلية
//        if ($cart->items()->exists()) {
//            $existingPharmacy = $cart->items()->first()->pharmacy_id;
//            if ($existingPharmacy !== $pm->pharmacy_id) {
//                return response()->json([
//                    'message' => 'Cart can contain items from only one pharmacy'
//                ], 422);
//            }
//        }
//
//        $item = $cart->items()
//            ->where('medicine_id', $pm->medicine_id)
//            ->first();
//
//        if ($item) {
//            $item->increment('quantity', $request->quantity);
//        } else {
//            $cart->items()->create([
//                'pharmacy_id' => $pm->pharmacy_id,
//                'medicine_id' => $pm->medicine_id,
//                'quantity' => $request->quantity,
//                'price_at_time' => $pm->price,
//            ]);
//        }
//
//        return response()->json(['message' => 'Added to cart']);
//    }
    public function add(Request $request)
    {
        $request->validate([
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // إيجاد سجل pharmacy_medicine
        $pm = \App\Models\pharmacy_medicine::where('pharmacy_id', $request->pharmacy_id)
        ->where('medicine_id', $request->medicine_id)
        ->firstOrFail();


        // منع سلة من أكثر من صيدلية
        if ($cart->items()->exists()) {
            $existingPharmacy = $cart->items()->first()->pharmacy_id;
            if ($existingPharmacy !== $pm->pharmacy_id) {
                return response()->json([
                    'message' => 'Cart can contain items from only one pharmacy'
                ], 422);
            }
        }

        // البحث عن العنصر الموجود
        $item = $cart->items()
            ->where('medicine_id', $pm->medicine_id)
            ->where('pharmacy_id', $pm->pharmacy_id)
            ->first();

        // Checkout already enforces stock correctly (with row locking), but
        // this endpoint never checked it at all - a user could add far more
        // than available and only find out at checkout. Check here too so
        // the error surfaces immediately, with the actual quantity they'd
        // be trying to buy (existing line quantity + this addition).
        $requestedTotal = $request->quantity + ($item ? $item->quantity : 0);
        if ($requestedTotal > $pm->stock) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available_stock' => $pm->stock,
            ], 422);
        }

        if ($item) {
            $item->increment('quantity', $request->quantity);
        } else {
            $cart->items()->create([
                'pharmacy_id' => $pm->pharmacy_id,
                'medicine_id' => $pm->medicine_id,
                'quantity' => $request->quantity,
                'price_at_time' => $pm->price,
            ]);
        }

        return response()->json(['message' => 'Added to cart']);
    }
    // تحديث كمية عنصر
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::findOrFail($itemId);
        // تأكد أن العنصر ينتمي للمستخدم
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Same gap as add(): quantity could previously be set to anything,
        // with no stock check until checkout.
        $pm = \App\Models\pharmacy_medicine::where('pharmacy_id', $item->pharmacy_id)
            ->where('medicine_id', $item->medicine_id)
            ->first();
        if ($pm && $request->quantity > $pm->stock) {
            return response()->json([
                'message' => 'Insufficient stock',
                'available_stock' => $pm->stock,
            ], 422);
        }

        $item->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Updated']);
    }

    // حذف عنصر من السلة
    public function remove(Request $request, $itemId)
    {
        $item = CartItem::findOrFail($itemId);
        if ($item->cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $item->delete();

        return response()->json(['message' => 'Removed']);
    }

    // مسح السلة
    public function clear(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();
        if ($cart) $cart->items()->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}
