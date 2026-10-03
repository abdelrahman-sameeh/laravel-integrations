<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FakePaymentWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'string', 'max:255'],
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'event_type' => [
                'required',
                'string',
                Rule::in([
                    'payment.succeeded',
                    'payment.failed',
                    'payment.cancelled',
                ]),
            ],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => [
                'required',
                'string',
                'size:3',
                'regex:/^[A-Za-z]{3}$/',
            ],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $payment = Payment::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($validated['payment_id']);

            $event = PaymentEvent::query()->firstOrCreate(
                [
                    'provider' => 'fake',
                    'provider_event_id' => $validated['event_id'],
                ],
                [
                    'payment_id' => $payment->id,
                    'event_type' => $validated['event_type'],
                    'payload' => $validated,
                ]
            );

            if (!$event->wasRecentlyCreated) {
                return [
                    'duplicate' => true,
                    'payment' => $payment,
                ];
            }

            abort_if(
                $payment->amount !== (int) $validated['amount'],
                422,
                'Payment amount does not match.'
            );

            abort_if(
                $payment->currency !== strtoupper($validated['currency']),
                422,
                'Payment currency does not match.'
            );

            match ($validated['event_type']) {
                'payment.succeeded' => $this->markAsPaid($payment),
                'payment.failed' => $this->markAsFailed($payment),
                'payment.cancelled' => $this->markAsCancelled($payment),
            };

            $event->update([
                'processed_at' => now(),
            ]);

            return [
                'duplicate' => false,
                'payment' => $payment->fresh(),
            ];
        });

        return response()->json([
            'message' => $result['duplicate']
                ? 'Event already processed.'
                : 'Event processed successfully.',
            'data' => $result['payment'],
        ]);
    }

    private function markAsPaid(Payment $payment): void
    {
        $payment->update([
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'failure_code' => null,
            'failure_message' => null,
        ]);

        $payment->order->update([
            'status' => OrderStatus::Confirmed,
        ]);
    }

    private function markAsFailed(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Failed,
            'failure_code' => 'fake_failure',
            'failure_message' => 'Fake payment failure.',
        ]);
    }

    private function markAsCancelled(Payment $payment): void
    {
        if ($payment->status === PaymentStatus::Paid) {
            return;
        }

        $payment->update([
            'status' => PaymentStatus::Cancelled,
        ]);
    }
}