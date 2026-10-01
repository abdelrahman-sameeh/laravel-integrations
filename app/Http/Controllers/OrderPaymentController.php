<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderPaymentController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== OrderStatus::Pending) {
            return response()->json([
                'message' => 'This order cannot accept a payment.',
            ], 422);
        }

        $hasPaidPayment = $order->payments()
            ->where('status', PaymentStatus::Paid->value)
            ->exists();

        if ($hasPaidPayment) {
            return response()->json([
                'message' => 'Order has already been paid.',
            ], 422);
        }

        $hasActivePayment = $order->payments()
            ->whereIn('status', [
                PaymentStatus::Pending->value,
                PaymentStatus::RequiresAction->value,
                PaymentStatus::Processing->value,
            ])
            ->exists();

        if ($hasActivePayment) {
            return response()->json([
                'message' => 'Order already has an active payment attempt.',
            ], 422);
        }

        $payment = $order->payments()->create([
            'provider' => 'fake',
            'amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => PaymentStatus::Pending,
        ]);

        return response()->json([
            'data' => $payment,
        ], 201);
    }
}
