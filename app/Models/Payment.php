<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'provider',
        'provider_payment_id',
        'provider_session_id',
        'provider_transaction_id',
        'amount',
        'currency',
        'status',
        'failure_code',
        'failure_message',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'amount' => 'integer',
        'status' => PaymentStatus::class,
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
