<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'AmazonOrderId',
        'AmazonOrderItemCode',
        'SKU',
        'ASIN',
        'Quantity',
        'TrackingId',
    ];
}
