<?php

namespace App\Http\Controllers;

use App\Models\AmazonOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SellingPartnerApi\Api\MerchantFulfillmentApi;
use SellingPartnerApi\Api\ShippingApi;
use SellingPartnerApi\Configuration;
use SellingPartnerApi\Model\MerchantFulfillment;
use SellingPartnerApi\Model\MerchantFulfillment\CarrierWillPickUpOption;
use SellingPartnerApi\Model\MerchantFulfillment\CreateShipmentRequest;
use SellingPartnerApi\Model\MerchantFulfillment\CurrencyAmount;
use SellingPartnerApi\Model\MerchantFulfillment\DeliveryExperienceOption;
use SellingPartnerApi\Model\MerchantFulfillment\DeliveryExperienceType;
use SellingPartnerApi\Model\MerchantFulfillment\FBMItem;
use SellingPartnerApi\Model\MerchantFulfillment\GetEligibleShipmentServicesRequest;
use SellingPartnerApi\Model\MerchantFulfillment\HazmatType;
use SellingPartnerApi\Model\MerchantFulfillment\LabelCustomization;
use SellingPartnerApi\Model\MerchantFulfillment\LabelFormat;
use SellingPartnerApi\Model\MerchantFulfillment\LabelFormatOptionRequest;
use SellingPartnerApi\Model\MerchantFulfillment\PackageDimensions;
use SellingPartnerApi\Model\MerchantFulfillment\ShipmentRequestDetails;
use SellingPartnerApi\Model\MerchantFulfillment\ShippingOfferingFilter;
use SellingPartnerApi\Model\MerchantFulfillment\ShippingServiceOptions;
use SellingPartnerApi\Model\MerchantFulfillment\StandardIdForLabel;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;
use DateTime;
use Illuminate\Support\Collection;

class DownloadOrdersController extends Controller
{
    public function download_orders()
    {
        $mainArray = array();
        try {

            $user_id = Auth::user()->id;

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            $config = get_amazon_config($user_id);

            $lastNumDaysOrders = Carbon::now()->subDays(config('amz.feed_type.order.interval'))->format('Y-m-d\TH:i:s.u\Z');

            $orderApi = new \SellingPartnerApi\Api\OrdersApi($config);

            set_time_limit(1200);

            $nextToken = null;

            $lastRequestTime = null;

            do {

                try {

                    // Check if enough time has passed since the last request
                    if ($lastRequestTime !== null) {
                        $timePassed = Carbon::now()->diffInSeconds($lastRequestTime);
                        $timeToWait = 60 - $timePassed;

                        if ($timeToWait > 0) {
                            sleep($timeToWait);
                        }
                    }

                    $lastRequestTime = Carbon::now();

                    $orders = $orderApi->getOrders(
                        array(config('amz.marketplaces.GB')),
                        $lastNumDaysOrders,
                        null,
                        null,
                        null,
                        array('Unshipped', 'PartiallyShipped','Canceled','Shipped'),
                        array('MFN'),
                        null,
                        null,
                        null,
                        null,
                        null,
                        $nextToken,
                        null,
                        null,
                        null,
                        null,
                        array("buyerInfo", "shippingAddress")
                    );


                    $nextToken = $orders->getPayload()->getNextToken();

                    $response_to_array = json_decode(response()->json($orders)->content(), true);

                    if (isset($response_to_array) &&
                    isset($response_to_array['payload']) &&
                    isset($response_to_array['payload']['Orders'])) {
                        $response_to_array = $response_to_array['payload']['Orders'];

                        foreach ($response_to_array as $orderData) {

                            try {

                                $order = AmazonOrder::updateOrCreate(['amazon_order_id' => $orderData['AmazonOrderId']], [
                                    'user_id' => $user_id,
                                    'amazon_order_id' => $orderData['AmazonOrderId'],
                                    'purchase_date' => $orderData['PurchaseDate'],
                                    'last_update_date' => $orderData['LastUpdateDate'],
                                    'order_status' => $orderData['OrderStatus'],
                                    'fulfillment_channel' => $orderData['FulfillmentChannel'],
                                    'sales_channel' => $orderData['SalesChannel'],
                                    'ship_service_level' => $orderData['ShipServiceLevel'],
                                    'order_total_currency_code' => $orderData['OrderTotal']['CurrencyCode'] ?? '',
                                    'order_total_amount' => $orderData['OrderTotal']['Amount'] ?? '0',
                                    'number_of_items_shipped' => $orderData['NumberOfItemsShipped'],
                                    'number_of_items_unshipped' => $orderData['NumberOfItemsUnshipped'],
                                    'marketplace_id' => $orderData['MarketplaceId'],
                                    'shipment_service_level_category' => $orderData['ShipmentServiceLevelCategory'],
                                    'order_type' => $orderData['OrderType'],
                                    'earliest_ship_date' => $orderData['EarliestShipDate'],
                                    'latest_ship_date' => $orderData['LatestShipDate'],
                                    'earliest_delivery_date' => $orderData['EarliestDeliveryDate'] ?? '',
                                    'latest_delivery_date' => $orderData['LatestDeliveryDate'] ?? '',
                                    'is_business_order' => $orderData['IsBusinessOrder'],
                                    'is_prime' => $orderData['IsPrime'],
                                    'is_premium_order' => $orderData['IsPremiumOrder'],
                                    'is_global_express_enabled' => $orderData['IsGlobalExpressEnabled'],
                                    'is_replacement_order' => $orderData['IsReplacementOrder'],
                                    'is_sold_by_ab' => $orderData['IsSoldByAB'],
                                    'default_ship_from_location_address_name' => isset($orderData['DefaultShipFromLocationAddress']['Name']) ? $orderData['DefaultShipFromLocationAddress']['Name'] : '',
                                    'default_ship_from_location_address_line_1' => isset($orderData['DefaultShipFromLocationAddress']['AddressLine1']) ? $orderData['DefaultShipFromLocationAddress']['AddressLine1'] : '',
                                    'default_ship_from_location_city' => isset($orderData['DefaultShipFromLocationAddress']['City']) ? $orderData['DefaultShipFromLocationAddress']['City'] : '',
                                    'default_ship_from_location_state_or_region' => isset($orderData['DefaultShipFromLocationAddress']['StateOrRegion']) ? $orderData['DefaultShipFromLocationAddress']['StateOrRegion'] : '',
                                    'default_ship_from_location_postal_code' => isset($orderData['DefaultShipFromLocationAddress']['PostalCode']) ? $orderData['DefaultShipFromLocationAddress']['PostalCode'] : '',
                                    'default_ship_from_location_country_code' => isset($orderData['DefaultShipFromLocationAddress']['CountryCode']) ? $orderData['DefaultShipFromLocationAddress']['CountryCode'] : '',
                                    'default_ship_from_location_phone' => isset($orderData['DefaultShipFromLocationAddress']['Phone']) ? $orderData['DefaultShipFromLocationAddress']['Phone'] : '',
                                    'shipping_address_name' => isset($orderData['ShippingAddress']['Name']) ? $orderData['ShippingAddress']['Name'] : '',
                                    'shipping_address_line_1' => isset($orderData['ShippingAddress']['AddressLine1']) ? $orderData['ShippingAddress']['AddressLine1'] : '',
                                    'shipping_address_city' => isset($orderData['ShippingAddress']['City']) ? $orderData['ShippingAddress']['City'] : '',
                                    'shipping_address_state_or_region' => isset($orderData['ShippingAddress']['StateOrRegion']) ? $orderData['ShippingAddress']['StateOrRegion'] : '',
                                    'shipping_address_postal_code' => isset($orderData['ShippingAddress']['PostalCode']) ? $orderData['ShippingAddress']['PostalCode'] : '',
                                    'shipping_address_country_code' => isset($orderData['ShippingAddress']['CountryCode']) ? $orderData['ShippingAddress']['CountryCode'] : '',
                                    'shipping_address_phone' => isset($orderData['ShippingAddress']['Phone']) ? $orderData['ShippingAddress']['Phone'] : '',
                                    'buyer_info_buyer_email' => isset($orderData['BuyerInfo']['BuyerEmail']) ? $orderData['BuyerInfo']['BuyerEmail'] : '',
                                    'buyer_info_buyer_name' => isset($orderData['BuyerInfo']['BuyerName']) ? $orderData['BuyerInfo']['BuyerName'] : '',
                                    // add more fields as needed
                                ]);

                                $this->get_order_items($orderData['AmazonOrderId'], $config);

                            } catch (\Throwable $th) {
                                throw $th;
                            }

                        }
                    }

                } catch (\Throwable $th) {
                    $s = $th->getMessage();
                    return $s;
                }
            } while ($nextToken != null);

            // Call the getShippedOrders function for checking SHIPPED orders in AmazonOrder
            // try {
            //     $this->getShippedOrders();
            // } catch (\Throwable $th) {
            //     throw $th;
            // }

            return redirect()->route('home')->with('success', 'Orders downloaded successfully');



        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
