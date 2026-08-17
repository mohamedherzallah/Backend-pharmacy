<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // طلبات المستخدم الحالية
    //مش المفروض يعرض

    //لمين المفروض يعرض الطلبات ؟؟؟؟؟
    //ماذا عن العناوين
    //ماذا عن التوصيل ؟؟

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'pharmacy') {
            // طلبات لصيدلية
            $orders = Order::where('pharmacy_id', $user->pharmacy->id)->with('items.medicine')->latest()->paginate(20);
        } else {
            $orders = Order::where('user_id', $user->id)->with('items.medicine')->latest()->paginate(20);
        }

        return OrderResource::collection($orders);
    }

    public function show($id, Request $request)
    {
        $order = Order::with('items.medicine')->findOrFail($id);
        // Authorization: user or pharmacy only
        $user = $request->user();
        if ($user->role === 'user' && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        if ($user->role === 'pharmacy' && $order->pharmacy_id !== $user->pharmacy->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return new OrderResource($order);
    }
//اللي استنتجته لحالي ان الدالتين اللي فوق ممكن يلزمو فقط الصيدلية اما المستخدم العادي ملهش دخل انه يظهرله الطلبات لانه لسا بطلب
    /**
     * Store a newly created resource in storage.
     */
//    public function store(Request $request)
//    {
//        //ليش فاضية ؟؟؟؟؟؟ كيف بدنا خززن الطلب في جدول الطلبات يا زعيم ام هي من الشيك اوت ؟
//    }


    // تحديث حالة (للصيدلية أو الادمن)
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $user = $request->user();

        // Only the owning pharmacy (or an admin) may change an order's status.
        // Previously ANY authenticated user could update ANY order.
        $isOwningPharmacy = $user->role === 'pharmacy' && $user->pharmacy && $order->pharmacy_id === $user->pharmacy->id;
        if ($user->role !== 'admin' && !$isOwningPharmacy) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Must match the actual `orders.status` enum in the database.
        // The previous list (pending,approved,cancelled,delivered) didn't
        // match the migrated enum (pending,accepted,rejected,delivering,
        // completed,cancelled), so a valid-looking request like
        // {"status":"approved"} would pass validation here and then fail
        // with a SQL error when saved.
        $request->validate([
            'status' => 'required|in:pending,accepted,rejected,delivering,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return new OrderResource($order->load('items.medicine'));
    }

    // إلغاء طلب (من قبل العميل صاحب الطلب، أو الصيدلية/الأدمن)
    public function destroy(Request $request, $id)
    {
        $order = Order::with('items')->findOrFail($id);
        $user = $request->user();

        $isOwner = $user->role === 'user' && $order->user_id === $user->id;
        $isOwningPharmacy = $user->role === 'pharmacy' && $user->pharmacy && $order->pharmacy_id === $user->pharmacy->id;
        if ($user->role !== 'admin' && !$isOwner && !$isOwningPharmacy) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (in_array($order->status, ['completed', 'delivering', 'cancelled'])) {
            return response()->json([
                'message' => "Order cannot be cancelled while status is '{$order->status}'"
            ], 422);
        }

        // Cancelling restores the stock that checkout deducted, and keeps
        // the order as a record instead of hard-deleting it (the previous
        // implementation permanently deleted the row with no ownership
        // check and without ever returning stock).
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->pharmacy_medicine_id) {
                    \App\Models\pharmacy_medicine::where('id', $item->pharmacy_medicine_id)
                        ->increment('stock', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
        });

        return response()->json(['message' => 'Order cancelled', 'order' => new OrderResource($order->fresh('items.medicine'))]);
    }
}
