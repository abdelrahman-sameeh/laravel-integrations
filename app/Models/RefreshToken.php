<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    use HasFactory;

    protected $table = 'refresh_tokens';
    public $timestamps = false;

    protected $fillable=[
        'user_id',
        'hash_token',
        'expires_at',
        'created_at'
    ];

}
