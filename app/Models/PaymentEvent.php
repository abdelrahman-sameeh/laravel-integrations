<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'provider',
        'provider_event_id',
        'event_type',
        'payload',
        'processed_at',
        'failure_message',
    ];

    protected $casts = [
        'payment_id' => 'integer',
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
