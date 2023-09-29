<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'user_id',
        'amz_seller_id',
        'state_id',
        'refresh_token',
        'access_token'
    ];

    protected $table = "subscribers";

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
