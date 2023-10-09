<?php

namespace App\Http\Controllers;

use App\Models\AmazonOrder;
use App\Models\OrderDetail;
use App\Models\Label;
use App\Models\LabelItem;
use App\Models\SellerOrderHistory;
use App\Models\ReportsHistory;
use App\Models\Site;
use App\Models\Subscriber;
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
use Illuminate\Support\Facades\Schema;

class oldOrdersController extends Controller
{
    public function RequestReport()
    {
        //insert or update sellers in sellers_orders_history table if not exist
        try {
            $this->set_sellers_orders_history();
        } catch (\Throwable $th) {
            throw $th;
        }
        $sellers = SellerOrderHistory::orderBy('last_download_date', 'asc')->get();

        foreach ($sellers as $seller) {

            try {
                $seller_email = $seller['seller_email'];
                $seller_amz_id = $seller['seller_amz_id'];
                $site_code = $seller['site_code'];
                $site_id = $seller['site_id'];

                //check if the current seller's report_id is already in que to download
                $reporst_count = ReportsHistory::where([
                    'seller_email' => $seller_email,
                    'seller_amz_id' => $seller_amz_id,
                    'is_downloaded' => 0,
                    ])->count();

                if ($reporst_count > 0) {
                    // there is already one report_id of this seller is in que to download // skip the current itearaion
                    continue;
                }

            } catch (\Throwable $th) {
                throw $th;
            }

            $config = get_amazon_config($seller_email, $seller_amz_id, $site_code);

            $reportApi = new \SellingPartnerApi\Api\ReportsApi($config);
            // $marketplace_ids = [config('amz.marketplaces.GB')];
            $marketplace_ids = [0 => $site_id];

            $data = [
                'report_type' => config('amz.feed_type.order.name'),
                'marketplace_ids' => $marketplace_ids,
                'data_start_time' => Carbon::now()->subDays(config('amz.feed_type.order.interval'))->format('Y-m-d\TH:i:s.u\Z'),
                'data_end_time' => validateUTC('+ 2 hours'),
            ];

            $body = new \SellingPartnerApi\Model\Reports\CreateReportSpecification($data);
            try {
                $report_id = $reportApi->createReport($body)->getReportId();
            } catch (\Exception $th) {
                throw $th;
            }

            if ($report_id) {
                //store report_id in reports_histories table along with seller information
                ReportsHistory::create([
                    'seller_email' => $seller_email,
                    'seller_amz_id' => $seller_amz_id,
                    'report_id' => $report_id,
                    'is_downloaded' => 0,
                ]);

                //update the the 'last_download_date' in SellerOrderHistory table
                $now = now();
                SellerOrderHistory::where([
                    'seller_email' => $seller_email,
                    'seller_amz_id' => $seller_amz_id,
                    'site_id' => $seller->site_id,
                ])->update(['last_download_date' => $now]);
            }

            $s = 'abc';
        }

    }

    public function DownloadOrders()
    {
        $reports_history = ReportsHistory::where('is_downloaded', 0)->orderBy('created_at')->first();

        $seller_email = $reports_history['seller_email'];
        $seller_amz_id = $reports_history['seller_amz_id'];
        $report_id = $reports_history['report_id'];

        try {
            $seller = SellerOrderHistory::where(['seller_email' => $seller_email, 'seller_amz_id' => $seller_amz_id])->first();

            $site_code = $seller['site_code'];
            $site_id = $seller['site_id'];

            $config = get_amazon_config($seller_email, $seller_amz_id, $site_code);

            $reportApi = new \SellingPartnerApi\Api\ReportsApi($config);

            $marketplace_ids = [0 => $site_id];

        } catch (\Throwable $th) {
            throw $th;
        }

        $report_document_id = "";
        $counter = 0;
        $rptPrepared = false;

        try {
            do {
                $report = $reportApi->getReport($report_id);
                $report_document_id = $report->getReportDocumentId();
                $reportStatus = $report->getProcessingStatus();

                if ($reportStatus == 'DONE') {
                    $rptPrepared = true;
                } else {
                    $counter++;
                    if ($counter > 30) {
                        break;
                    }
                    sleep(10); // Wait for 10 seconds before checking the report status again
                }
            } while (!$rptPrepared);
        } catch (\Exception $th) {
            throw $th;
        }

        $reportType = config('amz.feed_type.order.obj');

        $arr_orders_buf = array();
        if (!empty($report_document_id)) {
            $reportDocumentInfo = null;
            try {
                $reportDocumentInfo = $reportApi->getReportDocument($report_document_id, $reportType);
                $docToDownload = new \SellingPartnerApi\Document($reportDocumentInfo, $reportType);
                $buffer = $docToDownload->download();
            } catch (\Exception $th) {
                throw $th;
            }

            file_put_contents(storage_path() . "\\xml-responses\\orders.xml", $buffer);
            die();

            try {
                $xml_buf = simplexml_load_string($buffer);
                $json_buf = json_encode($xml_buf);
                $arr_orders_buf = json_decode($json_buf, true);
                $orders = isset($arr_orders_buf['Message']) ? $arr_orders_buf['Message'] : [];
                // AmazonShippedOrder::truncate();
                foreach (array_chunk($orders, 30) as $chunk) {
                    $this->insert_orders_to_database($chunk, $seller_email, $seller_amz_id, $site_code);
                } catch (\Throwable $th) {
                    throw $th;
                }
            }
        }

        return redirect()->route('home')->with('success', 'Shipped and Unshipped orders comparison operation succeeded');
    }

    public function set_sellers_orders_history()
    {
        try {

            $sellers = DB::table('users')
            ->join('subscribers', 'users.email', '=', 'subscribers.user_id')
            ->join('sites', 'subscribers.site_code', '=', 'sites.site_code')
            ->whereNotNull('subscribers.amz_seller_id')
            ->whereNotNull('subscribers.refresh_token')
            ->select('users.email', 'subscribers.site_code', 'subscribers.amz_seller_id', 'sites.site_id')
            ->get();

            // dd($sellers);

            $currentdate = now();
            foreach ($sellers as $seller) {
                DB::connection()->enableQueryLog();

                $insert_seller_history = SellerOrderHistory::updateOrCreate(
                    [
                        'seller_email' => $seller->email,
                    ],
                    [
                        'seller_amz_id' => $seller->amz_seller_id,
                        'site_code' => $seller->site_code,
                        'site_id' => $seller->site_id,
                        //'last_download_date' // null by default,
                    ]
                );

                $queries = DB::getQueryLog();
                $s = 'abc';

            }

            // return 'data inserted';
        } catch (\Throwable $th) {
            throw $th;
        }




    }

    public function insert_orders_to_database($orders, $seller_email, $seller_amz_id)
    {

        foreach ($orders as $item) {
            
            $order = $item['Order'] ?? '';
            
            $orders_array = [
                'AmazonOrderID' => $order['AmazonOrderID'] ?? '',
                'PurchaseDate' => substr($order['PurchaseDate'] ?? date('Y-m-i'), 0, 10),
                'MerchantOrderID' => $order['MerchantOrderID'] ?? '',
                'LastUpdatedDate' => substr($order['LastUpdatedDate'] ?? date('Y-m-i'), 0, 10),
                'OrderStatus' => $order['OrderStatus'] ?? '',
                'SalesChannel' => $order['SalesChannel'] ?? '',
                'FulfillmentChannel' => $order['FulfillmentData']['FulfillmentChannel'] ?? '',
                'ShipServiceLevel' => $order['FulfillmentData']['ShipServiceLevel'] ?? '',
                'City' => $order['Address']['FulfillmentData']['City'] ?? '',
                'State' => $order['Address']['FulfillmentData']['State'] ?? '',
                'PostalCode' => $order['Address']['FulfillmentData']['PostalCode'] ?? '',
                'Country' => $order['Address']['FulfillmentData']['Country'] ?? '',
                'IsBusinessOrder' => $order['IsBusinessOrder'] ?? '',
                'AddressType' => $order['AddressType'] ?? '',
            ];


            $orderItems = $order['OrderItem'] ?? '';

            // Check if 'OrderItem' key exists in $order
            if (isset($order['OrderItem'])) {
                $orderItems = $order['OrderItem'];

                // Create an array to store all order items
                $all_order_items = [];

                // Loop through each order item
                foreach ($orderItems as $orderItem) {
                    $order_item_array = [
                        'AmazonOrderItemCode' => $orderItem['AmazonOrderItemCode'] ?? '',
                        'ASIN' => $orderItem['ASIN'] ?? '',
                        'SKU' => $orderItem['SKU'] ?? '-',
                        'ItemStatus' => $orderItem['ItemStatus'] ?? '',
                        'ProductName' => $orderItem['ProductName'] ?? '',
                        // 'Quantity' => $orderItem['Quantity'] ?? '',
                        // 'Promotion' => $orderItem['Promotion'] ?? '',
                        // 'Price' => $orderItem['ItemPrice']['Amount'] ?? '',
                        // 'currency' => $orderItem['ItemPrice']['currency'] ?? '',
                        // 'Type' => $orderItem['ItemPrice']['Type'] ?? '',

                        
                        'ItemPrice' => $orderItem['ItemPrice']['Amount'] ?? '0',
                        'ItemPriceCurrencyCode' => $orderItem['ItemPrice']['CurrencyCode'] ?? '',
                        'ItemTaxAmount' => $orderItem['ItemTax']['Amount'] ?? '0',
                        'ItemTaxCurrencyCode' => $orderItem['ItemTax']['CurrencyCode'] ?? '',

                        'ShippingPrice' => $orderItem['ShippingPrice']['Amount'] ?? '0',
                        'ShippingPriceCurrencyCode' => $orderItem['ShippingPrice']['CurrencyCode'] ?? '',

                        'ShippingTax' => $orderItem['ShippingTax']['Amount'] ?? '0',
                        'ShippingTaxCurrencyCode' => $orderItem['ShippingTax']['CurrencyCode'] ?? '',

                        'PromotionDiscountAmount' => $orderItem['PromotionDiscount']['Amount'] ?? '',
                        'PromotionDiscountCurrencyCode' => $orderItem['PromotionDiscount']['CurrencyCode'] ?? '',
                        'PromotionDiscountTaxAmount' => $orderItem['PromotionDiscountTax']['Amount'] ?? '',
                        'PromotionDiscountTaxCurrencyCode' => $orderItem['PromotionDiscountTax']['CurrencyCode'] ?? '',
                        'Quantity' => $orderItem['QuantityOrdered'],
                        'QuantityShipped' => $orderItem['QuantityShipped'],
                    ];

                    // Add the order item to the array of all order items
                    $all_order_items[] = $order_item_array;
                }
            }

        }


        $database_array[] = $market_order_item_db;

        $order = AmazonOrder::updateOrCreate(['amazon_order_id' => $orderData['AmazonOrderId'], 'user_id' => $user_id], [$market_order_item_db]);
        $order = AmazonOrder::updateOrCreate(
            ['amazon_order_id' => $orderData['AmazonOrderId'], 'user_id' => $user_id],
            $market_order_item_db
        );


    }

    // public function download_orders()
    // {
    //     $mainArray = array();
    //     try {

    //         $user_id = Auth::user()->id;

    //         ini_set('memory_limit', '1024M');
    //         set_time_limit(0);

    //         $config = get_amazon_config();

    //         $lastNumDaysOrders = Carbon::now()->subDays(config('amz.feed_type.order.interval'))->format('Y-m-d\TH:i:s.u\Z');

    //         $orderApi = new \SellingPartnerApi\Api\OrdersApi($config);

    //         set_time_limit(1200);

    //         $nextToken = null;

    //         $lastRequestTime = null;

    //         do {

    //             try {

    //                 // Check if enough time has passed since the last request
    //                 if ($lastRequestTime !== null) {
    //                     $timePassed = Carbon::now()->diffInSeconds($lastRequestTime);
    //                     $timeToWait = 60 - $timePassed;

    //                     if ($timeToWait > 0) {
    //                         sleep($timeToWait);
    //                     }
    //                 }

    //                 $lastRequestTime = Carbon::now();

    //                 $orders = $orderApi->getOrders(
    //                     array(config('amz.marketplaces.GB')),
    //                     $lastNumDaysOrders,
    //                     null,
    //                     null,
    //                     null,
    //                     array('Unshipped', 'PartiallyShipped','Canceled','Shipped'),
    //                     array('MFN'),
    //                     null,
    //                     null,
    //                     null,
    //                     null,
    //                     null,
    //                     $nextToken,
    //                     null,
    //                     null,
    //                     null,
    //                     null,
    //                     null,
    //                     // array("buyerInfo", "shippingAddress")
    //                 );


    //                 $nextToken = $orders->getPayload()->getNextToken();

    //                 $response_to_array = json_decode(response()->json($orders)->content(), true);

    //                 if (isset($response_to_array) &&
    //                 isset($response_to_array['payload']) &&
    //                 isset($response_to_array['payload']['Orders'])) {
    //                     $response_to_array = $response_to_array['payload']['Orders'];

    //                     foreach ($response_to_array as $orderData) {

    //                         try {
    //                             // $order = AmazonOrder::updateOrCreate(['amazon_order_id' => $orderData['AmazonOrderId']], [
    //                             $order = AmazonOrder::updateOrCreate(['amazon_order_id' => $orderData['AmazonOrderId'], 'user_id' => $user_id], [
    //                                 'user_id' => $user_id,
    //                                 'amazon_order_id' => $orderData['AmazonOrderId'],
    //                                 'purchase_date' => $orderData['PurchaseDate'],
    //                                 'last_update_date' => $orderData['LastUpdateDate'],
    //                                 'order_status' => $orderData['OrderStatus'],
    //                                 'fulfillment_channel' => $orderData['FulfillmentChannel'],
    //                                 'sales_channel' => $orderData['SalesChannel'],
    //                                 'ship_service_level' => $orderData['ShipServiceLevel'],
    //                                 'order_total_currency_code' => $orderData['OrderTotal']['CurrencyCode'] ?? '',
    //                                 'order_total_amount' => $orderData['OrderTotal']['Amount'] ?? '0',
    //                                 'number_of_items_shipped' => $orderData['NumberOfItemsShipped'],
    //                                 'number_of_items_unshipped' => $orderData['NumberOfItemsUnshipped'],
    //                                 'marketplace_id' => $orderData['MarketplaceId'],
    //                                 'shipment_service_level_category' => $orderData['ShipmentServiceLevelCategory'],
    //                                 'order_type' => $orderData['OrderType'],
    //                                 'earliest_ship_date' => $orderData['EarliestShipDate'],
    //                                 'latest_ship_date' => $orderData['LatestShipDate'],
    //                                 'earliest_delivery_date' => $orderData['EarliestDeliveryDate'] ?? '',
    //                                 'latest_delivery_date' => $orderData['LatestDeliveryDate'] ?? '',
    //                                 'is_business_order' => $orderData['IsBusinessOrder'],
    //                                 'is_prime' => $orderData['IsPrime'],
    //                                 'is_premium_order' => $orderData['IsPremiumOrder'],
    //                                 'is_global_express_enabled' => $orderData['IsGlobalExpressEnabled'],
    //                                 'is_replacement_order' => $orderData['IsReplacementOrder'],
    //                                 'is_sold_by_ab' => $orderData['IsSoldByAB'],
    //                                 'default_ship_from_location_address_name' => isset($orderData['DefaultShipFromLocationAddress']['Name']) ? $orderData['DefaultShipFromLocationAddress']['Name'] : '',
    //                                 'default_ship_from_location_address_line_1' => isset($orderData['DefaultShipFromLocationAddress']['AddressLine1']) ? $orderData['DefaultShipFromLocationAddress']['AddressLine1'] : '',
    //                                 'default_ship_from_location_city' => isset($orderData['DefaultShipFromLocationAddress']['City']) ? $orderData['DefaultShipFromLocationAddress']['City'] : '',
    //                                 'default_ship_from_location_state_or_region' => isset($orderData['DefaultShipFromLocationAddress']['StateOrRegion']) ? $orderData['DefaultShipFromLocationAddress']['StateOrRegion'] : '',
    //                                 'default_ship_from_location_postal_code' => isset($orderData['DefaultShipFromLocationAddress']['PostalCode']) ? $orderData['DefaultShipFromLocationAddress']['PostalCode'] : '',
    //                                 'default_ship_from_location_country_code' => isset($orderData['DefaultShipFromLocationAddress']['CountryCode']) ? $orderData['DefaultShipFromLocationAddress']['CountryCode'] : '',
    //                                 'default_ship_from_location_phone' => isset($orderData['DefaultShipFromLocationAddress']['Phone']) ? $orderData['DefaultShipFromLocationAddress']['Phone'] : '',
    //                                 'shipping_address_name' => isset($orderData['ShippingAddress']['Name']) ? $orderData['ShippingAddress']['Name'] : '',
    //                                 'shipping_address_line_1' => isset($orderData['ShippingAddress']['AddressLine1']) ? $orderData['ShippingAddress']['AddressLine1'] : '',
    //                                 'shipping_address_city' => isset($orderData['ShippingAddress']['City']) ? $orderData['ShippingAddress']['City'] : '',
    //                                 'shipping_address_state_or_region' => isset($orderData['ShippingAddress']['StateOrRegion']) ? $orderData['ShippingAddress']['StateOrRegion'] : '',
    //                                 'shipping_address_postal_code' => isset($orderData['ShippingAddress']['PostalCode']) ? $orderData['ShippingAddress']['PostalCode'] : '',
    //                                 'shipping_address_country_code' => isset($orderData['ShippingAddress']['CountryCode']) ? $orderData['ShippingAddress']['CountryCode'] : '',
    //                                 'shipping_address_phone' => isset($orderData['ShippingAddress']['Phone']) ? $orderData['ShippingAddress']['Phone'] : '',
    //                                 'buyer_info_buyer_email' => isset($orderData['BuyerInfo']['BuyerEmail']) ? $orderData['BuyerInfo']['BuyerEmail'] : '',
    //                                 'buyer_info_buyer_name' => isset($orderData['BuyerInfo']['BuyerName']) ? $orderData['BuyerInfo']['BuyerName'] : '',
    //                                 // add more fields as needed
    //                             ]);

    //                             $this->get_order_items($orderData['AmazonOrderId'], $config);

    //                         } catch (\Throwable $th) {
    //                             throw $th;
    //                         }

    //                     }
    //                 }

    //             } catch (\Throwable $th) {
    //                 $s = $th->getMessage();
    //                 return $s;
    //             }
    //         } while ($nextToken != null);



    //         return redirect()->route('home')->with('success', 'Orders downloaded successfully');



    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }

    // public function get_order_items($orderID, $config)
    // {
    //     try {
    //         $order = AmazonOrder::where('amazon_order_id', $orderID)->first();
    //         if ($order->is_item_fetched == true) {
    //             return;
    //         }

    //         $items = $this->getOrderItemsPrepare($orderID, $config);
    //         // $items_array = array();

    //         if(is_array($items)) {
    //             //  $items_array = $items['OrderItems'];
    //             $allItemsDone = true;
    //             foreach ($items as $item) {
    //                 try {
    //                     $order_details = OrderDetail::updateOrCreate(
    //                         ['amazon_order_id' => $orderID,
    //                         'AmazonOrderItemCode' => $item['OrderItemId']],
    //                         [
    //                         'amazon_order_id' =>  $orderID,
    //                         'AmazonOrderItemCode' => $item['OrderItemId'],
    //                         'ASIN' => $item['ASIN'],
    //                         'SKU' => $item['SellerSKU'],
    //                         'ProductName' => $item['Title'],
    //                         'ItemPrice' => $item['ItemPrice']['Amount'] ?? '0',
    //                         'ItemPriceCurrencyCode' => $item['ItemPrice']['CurrencyCode'] ?? '',
    //                         'ItemTaxAmount' => $item['ItemTax']['Amount'] ?? '0',
    //                         'ItemTaxCurrencyCode' => $item['ItemTax']['CurrencyCode'] ?? '',

    //                         'ShippingPrice' => $item['ShippingPrice']['Amount'] ?? '0',
    //                         'ShippingPriceCurrencyCode' => $item['ShippingPrice']['CurrencyCode'] ?? '',

    //                         'ShippingTax' => $item['ShippingTax']['Amount'] ?? '0',
    //                         'ShippingTaxCurrencyCode' => $item['ShippingTax']['CurrencyCode'] ?? '',

    //                         'PromotionDiscountAmount' => $item['PromotionDiscount']['Amount'] ?? '',
    //                         'PromotionDiscountCurrencyCode' => $item['PromotionDiscount']['CurrencyCode'] ?? '',
    //                         'PromotionDiscountTaxAmount' => $item['PromotionDiscountTax']['Amount'] ?? '',
    //                         'PromotionDiscountTaxCurrencyCode' => $item['PromotionDiscountTax']['CurrencyCode'] ?? '',
    //                         'Quantity' => $item['QuantityOrdered'],
    //                         'QuantityShipped' => $item['QuantityShipped'],
    //                         ]
    //                     );

    //                 } catch (\Throwable $th) {
    //                     $msg = $th->getMessage();
    //                     $allItemsDone = false;
    //                 }
    //             }

    //             if($allItemsDone) {
    //                 try {
    //                     $order->update(['is_item_fetched' => true]);
    //                 } catch (\Throwable $th) {
    //                     $msg = $th->getMessage();

    //                 }
    //             }
    //         }
    //     } catch (\Throwable $th) {
    //         $msg = $th->getMessage();
    //         return $msg;
    //     }
    // }

    // public function getOrderItemsPrepare($order_id, $config = null)
    // {

    //     try {

    //         if (isset($order_id) && (strlen($order_id) > 5)) {

    //             $orderApi = new \SellingPartnerApi\Api\OrdersApi($config);

    //             $order_items = $orderApi->getOrderItems($order_id);
    //             $response_to_array = json_decode(response()->json($order_items)->content(), true);


    //             $items = $response_to_array['payload'];
    //             if(is_array($items) && isset($items['OrderItems'])) {
    //                 return $items['OrderItems'];
    //             } else {
    //                 return false;
    //             }

    //         }

    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }

    // }


    // LOAD AMAZON ORDERS TO VIEWS ON DATATABLES
    public function load_amazon_orders(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $totalFilteredRecord = $totalDataRecord = $draw_val = "";

            $whereStatment = '';
            $whereStatementOrderDetails = "";
            $isUnshipped = false;
            $request_data = $request->all();

            $page_order_status = $request_data['order_status'];

            if($page_order_status == 'Shipped') {
                //shipped-orders
                $whereStatment = '(amazon_orders.order_status="Shipped" AND amazon_orders.user_id = "' . $user_id . '")';
            } elseif($page_order_status == 'Canceled') {
                //canceled-orders
                $whereStatment = '(amazon_orders.order_status="Canceled" OR amazon_orders.is_cancellation_requested="1" AND amazon_orders.order_status != "Unshipped" AND amazon_orders.order_status != "PartiallyShipped" AND amazon_orders.order_status != "Shipped"  AND amazon_orders.user_id = "' . $user_id . '")';
            } elseif($page_order_status == 'Unshipped') {
                try {
                    // $whereStatment = "(amazon_orders.order_status='Unshipped') OR amazon_orders.order_status='PartiallyShipped' ";
                    $whereStatment = '(amazon_orders.order_status="Unshipped" OR amazon_orders.order_status="PartiallyShipped") AND (amazon_orders.user_id = "' . $user_id . '")';

                } catch (\Throwable $th) {
                    throw $th;
                }
            }

            $today = now()->toDateString();

            // if(isset($request->newOrders) && $request->newOrders == 'today') {
            //     $whereStatment .=  " AND (amazon_orders.earliest_ship_date = " . $today . ")" ;
            // }

            $columns_list = array(
                0 => 'id',
                1 => 'purchase_date',
                2 => 'amazon_order_id',
                3 => 'ASIN',
                4 => 'SKU',
                5 => 'earliest_ship_date',
                6 => 'buyer_info_buyer_name',
                7 => 'shipping_address_line1',
                8 => 'order_status',
                9 => 'shipping_address_city',
                10 => 'shipping_address_state_or_region',
                11 => 'shipping_address_postal_code',
                12 => 'shipping_address_country_code',
                13 => 'shipment_service_level_category',
                14 => 'is_actually_shipped',
            );


            try {
                $start_val = $request->input('start');
                $order_val = $columns_list[$request->input('order.0.column')];
                $dir_val = $request->input('order.0.dir');

                $modifiedStatement = str_replace(')', '),', $whereStatment);

                $parts = explode(',', $modifiedStatement);
                $filter_conditions = array_filter(array_map('trim', $parts), function ($value) {
                    return $value !== '';
                });
                $count_data_query = AmazonOrder::with(['orderDetails']);
                foreach ($filter_conditions as $condition) {

                    if (strpos($condition, 'AND') !== 0 && strpos($condition, 'OR') !== false) {

                        $or_conditions = explode('OR', $condition);

                        $count_data_query->orWhere(function ($subquery) use ($or_conditions) {

                            foreach ($or_conditions as $or_condition) {
                                if (strpos($or_condition, 'amazon_orders.') !== false) {
                                    $subquery->orWhereRaw($or_condition);
                                } elseif (strpos($or_condition, 'order_details.') !== false) {
                                    $subquery->orWhereHas('orderDetails', function ($detailQuery) use ($or_condition) {
                                        $detailQuery->WhereRaw($or_condition);
                                    });
                                }
                            }
                        });
                    } else {
                        // $condition = str_replace('AND', '', $condition);
                        if (strpos($condition, 'AND') === 0) {
                            $condition = substr($condition, 3);
                        }
                        if (strpos($condition, 'amazon_orders.') !== false) {
                            // $count_data_query->whereRaw($condition);
                            $count_data_query->whereRaw($condition);
                        } elseif (strpos($condition, 'order_details.') !== false) {
                            $count_data_query->whereHas('orderDetails', function ($detailQuery) use ($condition) {
                                $detailQuery->whereRaw($condition);
                            });
                        }
                    }
                }
                $totalDataRecord = $count_data_query->count();

                $totalFilteredRecord = $totalDataRecord;

                $limit_val = $request->input('length');

                if($limit_val < 0) {
                    $limit_val = $totalDataRecord;
                }

                $srt = $order_val;
            } catch (\Throwable $th) {
                throw $th;
            }


            if(empty($request->input('search.value'))) {


                try {
                    $modifiedStatement = str_replace(')', '),', $whereStatment);

                    // Split the string on ","
                    $parts = explode(',', $modifiedStatement);

                    $filter_conditions = array_filter(array_map('trim', $parts), function ($value) {
                        return $value !== '';
                    });

                    setQueryLog();
                    $post_data_query = AmazonOrder::with(['orderDetails']);
                    foreach ($filter_conditions as $condition) {

                        // $condition = str_replace(['(', ')'], '', $condition);

                        if (strpos($condition, 'AND') !== 0 && strpos($condition, 'OR') !== false) {


                            $or_conditions = explode('OR', $condition);

                            $post_data_query->orWhere(function ($subquery) use ($or_conditions) {

                                foreach ($or_conditions as $or_condition) {
                                    if (strpos($or_condition, 'amazon_orders.') !== false) {
                                        $subquery->orWhereRaw($or_condition);
                                    } elseif (strpos($or_condition, 'order_details.') !== false) {
                                        $subquery->orWhereHas('orderDetails', function ($detailQuery) use ($or_condition) {
                                            $detailQuery->WhereRaw($or_condition);
                                        });
                                    }
                                }
                            });
                        } else {
                            // $condition = str_replace('AND', '', $condition);
                            if (strpos($condition, 'AND') === 0) {
                                $condition = substr($condition, 3);
                            }
                            if (strpos($condition, 'amazon_orders.') !== false) {
                                $post_data_query->whereRaw($condition);
                            } elseif (strpos($condition, 'order_details.') !== false) {
                                $post_data_query->whereHas('orderDetails', function ($detailQuery) use ($condition) {
                                    $detailQuery->whereRaw($condition);
                                });
                            }
                        }
                    }
                    $post_data = $post_data_query->get();
                    $query = getQueryLog();
                    $seeQuer = $query;
                } catch (\Throwable $th) {
                    throw $th;
                }

            } else {
                try {
                    $search_text = $request->input('search.value');
                    $search_text = substr($search_text, -20);
                    $search_text = str_replace("}", "", $search_text);
                    $search_text = str_replace("{", "", $search_text);
                    $search_text = str_replace("[", "", $search_text);
                    $search_text = str_replace("]", "", $search_text);

                    $post_data = AmazonOrder::with(['orderDetails'])
                    ->where(function ($query) use ($search_text) {
                        $query->where('amazon_order_id', 'LIKE', "%{$search_text}%")
                            ->orWhere('buyer_info_buyer_name', 'LIKE', "%{$search_text}%")
                            ->orWhere('order_status', 'LIKE', "%{$search_text}%")
                            ->orWhereHas('orderDetails', function ($query) use ($search_text) {
                                $query->where('ASIN', 'LIKE', "%{$search_text}%")
                                    ->orWhere('ProductName', 'LIKE', "%{$search_text}%")
                                    ->orWhere('SKU', 'LIKE', "%{$search_text}%");
                            })
                            ->orWhere('purchase_date', 'LIKE', "%{$search_text}%")
                            ->orWhere('earliest_ship_date', 'LIKE', "%{$search_text}%");
                    })
                    // ->when(isset($request->newOrders) && $request->newOrders == 'today', function ($query) use ($today) {
                    //     return $query->whereDate('earliest_ship_date', '=', $today);
                    // })
                    ->whereRaw($whereStatment)
                    ->offset($start_val)
                    ->limit($limit_val)
                    ->orderByRaw($srt . " " . $dir_val)
                    ->get();

                } catch (\Throwable $th) {
                    throw $th;
                }
                try {
                    $subquery = AmazonOrder::with(['orderDetails'])
                        ->where(function ($query) use ($search_text) {
                            $query->where('amazon_order_id', 'LIKE', "%{$search_text}%")
                                ->orWhere('buyer_info_buyer_name', 'LIKE', "%{$search_text}%")
                                ->orWhere('order_status', 'LIKE', "%{$search_text}%")
                                ->orWhereHas('orderDetails', function ($query) use ($search_text) {
                                    $query->where('ASIN', 'LIKE', "%{$search_text}%")
                                        ->orWhere('ProductName', 'LIKE', "%{$search_text}%")
                                        ->orWhere('SKU', 'LIKE', "%{$search_text}%");
                                })
                                ->orWhere('purchase_date', 'LIKE', "%{$search_text}%")
                                ->orWhere('earliest_ship_date', 'LIKE', "%{$search_text}%");
                        })
                        ->whereRaw($whereStatment)
                        ->select('amazon_order_id')
                        ->distinct();

                    $totalFilteredRecord = AmazonOrder::fromSub($subquery, 'subquery')
                        ->count();

                    $s = 'some value';

                } catch (\Throwable $th) {
                    throw $th;
                }

            }

            $data_val = array();

            $order_status_g = '<a title="Order has not yet dispatched" class="" href="javascript:void(0);"  id="btn-status-g"><img src="' . asset('assets/images/status/8541568_20-g.png') . '"></a>';
            $order_status_b = '<a title="Order is dispatched"  class="" href="javascript:void(0);"  id="btn-status-g"><img src="' . asset('assets/images/status/8541568_20-b.png') . '"></a>';

            $feedback_leave_btn = '<a  title="leave positive feedback" class="ml-2 mr-2" href="javascript:void(0);" onclick="quick_feedback(\'__order-id__\',1);"  id="btn-positive-feedback"><img  src="' . asset('assets/images/feedback/happy.png') . '"></a>';
            $feedback_leave_btn .= '<a  title="leave negative feedback" class="ml-2 mr-2" href="javascript:void(0);" onclick="quick_feedback(\'__order-id__\',0);"  id="btn-negative-feedback"><img  src="' . asset('assets/images/feedback/upset.png') . '"></a>';
            $feedback_left_btn = '<a title="feedback already left" class="ml-2 mr-2" href="javascript:void(0);"  id="btn-amazon-all"><img src="__left_feedback_img__"></a>';
            $order_received_btn = '<a title="__order_status__" class="ml-2 mr-2" style="cursor:pointer;" href="javascript:void(0);"  onclick="toggle_receive(\'__order-id__\',1);"  id="btn-order-received"><img src="__order_received_img__"></a>';

            $i = $start_val + 1;
            $return_btn_red = "";

            if(!empty($post_data)) {
                foreach ($post_data as $post_val) {


                    $feedback_btn_composed = str_replace("__order-id__", $post_val->OrderID, $feedback_leave_btn);
                    $feedback_btn_composed = str_replace("__item-id__", $post_val->ItemID, $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__recepient_id__", $post_val->SellerEmail, $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__transaction-id__", $post_val->TransactionID, $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__site-id__", '.co.uk', $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__quantity__", $post_val->QuantityPurchased, $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__variations__", $post_val->VarDetail, $feedback_btn_composed);
                    $feedback_btn_composed = str_replace("__title__", $post_val->Title, $feedback_btn_composed);

                    $return_btn_red_composed = str_replace("__item-id__", $post_val->ItemID, $return_btn_red);
                    $return_btn_red_composed = str_replace("__transaction-id__", $post_val->TransactionID, $return_btn_red_composed);

                    $feedback_left_btn_composed = str_replace("__left_feedback_img__", ($post_val->comment_type == 'Positive') ? asset('assets/images/feedback/happy-g.png') : asset('assets/images/feedback/upset-g.png'), $feedback_left_btn);
                    $order_received_btn_composed = str_replace("__order_received_img__", ($post_val->is_order_received == '0') ? asset('assets/images/feedback/received-g.png') : asset('assets/images/feedback/received.png'), $order_received_btn);
                    $order_received_btn_composed = str_replace("__order_status__", ($post_val->is_order_received == '0') ? 'Order Not Received' : 'Order is received', $order_received_btn_composed);
                    $order_received_btn_composed = str_replace("__order-id__", $post_val->OrderID, $order_received_btn_composed);

                    $order_status_composed = empty($post_val->ShippedOnDate) ? $order_status_g : $order_status_b;


                    $feedback_path = empty($post_val->feedback_id) ? $feedback_btn_composed : $feedback_left_btn_composed;
                    $returns_path = empty($post_val->feedback_id) ? $return_btn_red_composed : $return_btn_gray;

                    $labels = isset($post_val->shippingLabels) ? $post_val->shippingLabels->ShipmentId : null;
                    $postnestedData['id'] = $i;

                    $postnestedData['order_detail'] = $post_val->orderDetails->toArray();
                    $postnestedData['ShipmentId'] = $labels;
                    $postnestedData['amazon_order_id'] = $post_val->amazon_order_id;
                    $postnestedData['ASIN'] = $post_val->ASIN;
                    $postnestedData['SKU'] = $post_val->SKU;
                    $postnestedData['ProductName'] = $post_val->ProductName . "<br>" . $post_val->SKU ;
                    $postnestedData['PurchaseDate'] = $post_val->purchase_date;
                    $postnestedData['earliest_ship_date'] = $post_val->earliest_ship_date;
                    $postnestedData['latest_ship_date'] = $post_val->latest_ship_date;
                    $postnestedData['earliest_delivery_date'] = $post_val->earliest_delivery_date;
                    $postnestedData['latest_delivery_date'] = $post_val->latest_delivery_date;
                    $postnestedData['shipment_service_level_category'] = $post_val->shipment_service_level_category;
                    $postnestedData['Amount'] = $post_val->order_total_amount;
                    $postnestedData['BuyerName'] = $post_val->buyer_info_buyer_name;
                    $postnestedData['shipping_address_name'] = $post_val->shipping_address_name;
                    $postnestedData['shipping_address_line_1'] = $post_val->shipping_address_line_1;
                    $postnestedData['shipping_address_city'] = $post_val->shipping_address_city;
                    $postnestedData['shipping_address_state_or_region'] = $post_val->shipping_address_state_or_region;
                    $postnestedData['shipping_address_postal_code'] = $post_val->shipping_address_postal_code;
                    $postnestedData['shipping_address_country_code'] = $post_val->shipping_address_country_code;
                    $postnestedData['shipping_address_phone'] = $post_val->shipping_address_phone;
                    $postnestedData['OrderStatus'] = $post_val->order_status;
                    $postnestedData['City'] = $post_val->City;
                    $postnestedData['State'] = $post_val->State;
                    $postnestedData['PostalCode'] = $post_val->PostalCode;
                    $postnestedData['Country'] = $post_val->Country;
                    $postnestedData['ShipServiceLevel'] = $post_val->ship_service_level;
                    $postnestedData['is_actually_shipped'] = $post_val->is_actually_shipped;
                    $postnestedData['is_label_purchased'] = $post_val->is_label_purchased;


                    $postnestedData['Feedback'] = $feedback_path;
                    $postnestedData['Received'] = $order_received_btn_composed;
                    // $postnestedData['options'] = $opt;
                    // $inr_btn = empty($post_val->amazon_order_id)? $return_btn_red_composed : "";
                    $postnestedData['PurchaseLabel'] = $post_val->amazon_order_id;


                    $data_val[] = $postnestedData;
                    $i++;

                }
            }

            $draw_val = $request->input('draw');



            return response()->json([
                "status" => 200,
                "draw"            => intval($draw_val),
                "recordsTotal"    => intval($totalDataRecord),
                "recordsFiltered" => intval($totalFilteredRecord),
                "data"            => $data_val
             ]);



        } catch(Exception $e) {
            $str = $e->getMessage();
        }
    }

    //get order-details
    public function getOrderDetails(Request $request)
    {
        try {
            $order_id = $request->order_id;
            if (isset($order_id) && (strlen($order_id) > 5)) {
                $orderDetail = AmazonOrder::where('amazon_order_id', $order_id)
                ->with([
                    'orderDetails'
                    // 'shippingLabels' => function ($query) {
                    //     $query->select('AmazonOrderId', 'ShipmentId');
                    // }
                ])
                ->first();
                // $shippingLabels = $orderDetail->shippingLabels;
                return response()->json($orderDetail);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    // mark order as shipped

    public function mark_order_shipped(Request $request)
    {
        $user_id = Auth::user()->id;
        $amazonOrderId = $request->input('order_id');
        $shipDate = Carbon::parse($request->input('shipDate_ml'))->format('Y-m-d H:i:s');
        $CarrierCode = $request->input('CarrierName');
        $TrackingId = $request->input('TrackingId') ?? null;

        //process the update_tracking on amazon
        try {
            $feedArray = [ 'order-id' => $amazonOrderId, 'shipdate' => $shipDate,'CarrierCode' => $CarrierCode, 'tracking' => $TrackingId ];
            $TrackingController = new TrackingController();
            $updateTracking = $TrackingController->update_trackings($feedArray);
            if ($updateTracking !== true) {
                // return response()->json([
                //     'message' => 'Order marked as shipped and Tracking Uploaded successfully'
                // ]);
                // } else {
                return response()->json([
                    'error' => $updateTracking
                ]);
            }
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }

        //change orders status is_actually_shipped to true order_status to 'shipped'
        try {
            AmazonOrder::where('amazon_order_id', $amazonOrderId)->update([
                'is_actually_shipped' => 1,
                'order_status' => 'Shipped',
                'trackingId' => $TrackingId,
                'carrier_code' => $CarrierCode,
                'shipped_date' => $shipDate,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }

        // store Label and label Items data

        try {
            $data = [
                'user_id' => $user_id,
                'AmazonOrderId' => $amazonOrderId,
                'ShipDate' => $shipDate,
                'ShippingServiceId' => $request->input('ShippingServiceId_ml'),
                'ShippingServiceName' => $request->input('serviceName'),
                'CarrierName' => $CarrierCode,
                'TrackingId' => $TrackingId,
            ];

            $existingOrder = Label::where('AmazonOrderId', $data['AmazonOrderId'])->first();

            if (!$existingOrder) {
                // AmazonOrderId does not exist in the table, proceed with insertion
                Label::create($data);
                // Label::updateOrCreate(['AmazonOrderId' => $amazonOrderId], $data);

                // STORE ORDER DETAILS IN LABELITEMS
                try {
                    $labelItems = $request->input('LabelItems');
                    $itemCodes = [];
                    $orderDetails = [];

                    foreach ($labelItems as $item) {
                        $itemCode = $item['OrderItemId'];
                        $itemCodes[] = $itemCode;
                        $orderDetail = OrderDetail::where('AmazonOrderItemCode', $itemCode)->first();
                        if ($orderDetail) {
                            $ItemsData = [
                                'ASIN' => $orderDetail->ASIN,
                                'SKU' => $orderDetail->SKU,
                                'AmazonOrderId' => $amazonOrderId,
                                'AmazonOrderItemCode' => $itemCode,
                                'Quantity' => $item['QuantityOrdered'],
                                'TrackingId' => $TrackingId,
                            ];
                            LabelItem::create($ItemsData);

                        }
                    }
                } catch (\Throwable $th) {
                    return response()->json([
                        'error' => $th->getMessage()
                    ]);
                }

            }
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                'error' => $th->getMessage()
            ]);
        }


        return response()->json(['message' => 'Order marked as shipped and Tracking Uploaded successfully']);

    }

    // protected function validateUTC($input = '')
    // {
    //     return (new \DateTime($input))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    // }

}
