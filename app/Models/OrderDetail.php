<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AmazonOrder;

class OrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_email',
        'seller_amz_id',
        'seller_site_code',
        'amazon_order_id',
        'AmazonOrderItemCode',
        'ASIN',
        'SKU',
        'ProductName',
        'ItemPrice',
        'ItemPriceCurrencyCode',
        'ItemTaxAmount',
        'ItemTaxCurrencyCode',
        'ShippingPrice',
        'ShippingPriceCurrencyCode',
        'ShippingTax',
        'ShippingTaxCurrencyCode',
        'PromotionDiscountAmount',
        'PromotionDiscountTaxAmount',
        'PromotionDiscountTaxCurrencyCode',
        'PromotionDiscountCurrencyCode',
        'Quantity',
        'QuantityShipped',
        'ItemStatus',
        'is_label_purchased',
        'is_actually_shipped',
        'is_google_pushed',
        'is_cancellation_requested',
        'cancel_reason',
    ];

    public function amazonOrder()
    {
        return $this->belongsTo(AmazonOrder::class, 'amazon_order_id', 'amazon_order_id');
    }
}
