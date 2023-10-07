<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonOrder extends Model
{
    use HasFactory;

    
    protected $table = 'amazon_orders';

    protected $fillable = [
        'user_id',
        'amazon_order_id',
        'trackingId',
        'carrier_code',
        'shipped_date',
        'purchase_date',
        'last_update_date',
        'order_status',
        'fulfillment_channel',
        'sales_channel',
        'ship_service_level',
        'order_total_currency_code',
        'order_total_amount',
        'number_of_items_shipped',
        'number_of_items_unshipped',
        'marketplace_id',
        'shipment_service_level_category',
        'order_type',
        'earliest_ship_date',
        'latest_ship_date',
        'earliest_delivery_date',
        'latest_delivery_date',
        'is_business_order',
        'is_prime',
        'is_premium_order',
        'is_global_express_enabled',
        'is_replacement_order',
        'is_sold_by_ab',
        'default_ship_from_location_address_name',
        'default_ship_from_location_address_line_1',
        'default_ship_from_location_city',
        'default_ship_from_location_state_or_region',
        'default_ship_from_location_postal_code',
        'default_ship_from_location_country_code',
        'default_ship_from_location_phone',
        'shipping_address_name',
        'shipping_address_line_1',
        'shipping_address_city',
        'shipping_address_state_or_region',
        'shipping_address_postal_code',
        'shipping_address_country_code',
        'shipping_address_phone',
        'buyer_info_buyer_email',
        'buyer_info_buyer_name',
        'is_label_purchased',
        'is_cancellation_requested',
        'is_actually_cancelled',

    ];

    protected $casts = [
        'order_total_amount' => 'float',
        'is_business_order' => 'boolean',
        'is_prime' => 'boolean',
        'is_premium_order' => 'boolean',
        'is_global_express_enabled' => 'boolean',
        'is_replacement_order' => 'boolean',
        'is_sold_by_ab' => 'boolean',
    ];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'amazon_order_id', 'amazon_order_id');
    }
}
