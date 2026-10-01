<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    use HasFactory;

    protected $table = 'refresh_tokens';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'hash_token',
        'expires_at',
        'created_at',
    ];

    protected $hidden = [
        'hash_token',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
