<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'updated_at'
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];
}