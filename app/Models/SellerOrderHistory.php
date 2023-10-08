<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrderHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_email','seller_amz_id','last_download_date', 'site_id', 'site_code'
    ];
}
