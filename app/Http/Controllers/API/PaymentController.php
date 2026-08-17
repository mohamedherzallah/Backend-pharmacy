<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Pay for an order.
     *
     * Fixed several issues:
     *  - This route was PUBLIC with no ownership check: any anonymous
     *    caller could pay (and mark as paid) any order_id (IDOR).
     *    It now requires auth:sanctum (see routes/api.php) and verifies
     *    the order belongs to the requesting user.
     *  - It read/wrote $order->payment_status and $order->total_price,
     *    neither of which are real columns (the migration only added
     *    orders.payment_method; the total column is orders.total_amount).
     *    Calling update(['payment_status' => 'paid']) threw a SQL
     *    "unknown column" error every time. Payment state now lives on
     *    the existing `payments` table via the Order::payment() relation.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
        ]);

        $order = Order::with('payment')->findOrFail($request->order_id);

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($order->payment && $order->payment->status === 'paid') {
            return response()->json(['message' => 'This order is already paid'], 400);
        }

        $payment = DB::transaction(function () use ($order, $request) {
            $transactionId = 'TXN_' . uniqid();

            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'status' => 'paid',
                    'amount' => $order->total_amount,
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $transactionId,
                ]
            );

            $order->update(['payment_method' => $request->payment_method]);

            return $payment;
        });

        return response()->json([
            'message' => 'Payment successful',
            'payment' => $payment
        ]);
    }
}
