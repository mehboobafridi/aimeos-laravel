<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $table = 'labels';

    protected $fillable = [
        'seller_email',
        'AmazonOrderId',
        'ShippingServiceName',
        'CarrierName',
        'ShippingServiceId',
        'ShipDate',
        'TrackingId',
        'created_at',
        'updated_at',
    ];

    public function amazonOrder()
    {
        return $this->belongsTo(AmazonOrder::class, 'AmazonOrderId', 'amazon_order_id');
    }
}
