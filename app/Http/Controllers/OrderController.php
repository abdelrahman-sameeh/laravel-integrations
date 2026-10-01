<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ]);

        $order = Order::query()->create([
            'number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(
                Str::random(6)
            ),
            'user_id' => $request->user()->id,
            'total_amount' => (int) $validated['amount'],
            'currency' => Str::upper($validated['currency']),
        ]);

        return response()->json([
            'data' => $order,
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if(
            $order->user_id !== $request->user()->id,
            403
        );

        $order->load('payments');

        return response()->json([
            'data' => $order,
        ]);
    }
}
