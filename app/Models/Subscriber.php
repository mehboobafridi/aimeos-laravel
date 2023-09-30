<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Site;
use App\Models\User;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'site_code',
        'user_id',
        'amz_seller_id',
        'state_id',
        'refresh_token',
        'access_token'
    ];

    protected $table = "subscribers";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'email');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class);
    }
}
