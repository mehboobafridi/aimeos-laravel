<?php

namespace App\Classes;

use DB;
use App\Classes\Config;
use SellingPartnerApi\Model\ProductPricingV0\HttpMethod;
use SellingPartnerApi\Model\ProductPricingV0\ItemCondition;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\Listing;
use App\GSheet;
use App\Amz;
use Session;
use App\Models\GetProgress;


class GetPrices
{
    protected $_asins;
    // protected Storage $_storeObject;

    public function __construct($asins = null)
    {
        $this->_asins = $asins;
        // $this->_storeObject = new Storage();
    }

    // public function getStoreObj()
    // {
    //     // // $listings = DB::select(DB::raw(" select i.* from listings i inner join amzn_repriceables a on i.sku=a.sku and i.asin=a.asin where a.status='1';"));
    //     // // return $listings;
    //     // return DB::table('listings')->get();
    // }

    /**
     * Operation getItemOffersBatch
     *
     * @param  array of asins optional here.
     *
     * @return \SellingPartnerApi\Model\ProductPricingV0\GetItemOffersBatchResponse
     */
    public function get_items_offers($asins = null)
    {
        // dd($asins);
        if ($asins != null && count($asins) > 0) {
            $this->_asins = $asins;
        }

        if ($this->_asins != null & count($this->_asins) > 0) {
            $amzConfig = Config::getConfiguration();

            $productPrice = new \SellingPartnerApi\Api\ProductPricingV0Api($amzConfig);

            $chunk = array_chunk($this->_asins, 20)[0];
            // $chunk = $this->_asins->chunk(100);
            // $chunk = $this->_asins;

            try {
                $batchArray = array();
                $asins_string='';

                //create batch of items;
                try {
                    foreach ($chunk as $item) {
                        // dd($item->ASIN);

                        $asins_string .= "'" . $item->ASIN . "',";
                        // $asins_string .= "'" . $item['0']->ASIN . "',";

                        // dd($asins_string);
                        $batchArray[] = new \SellingPartnerApi\Model\ProductPricingV0\ItemOffersRequest(
                            [
                                'uri' => '/products/pricing/v0/items/' . $item->ASIN . '/offers',
                                'marketplace_id' => Config::$siteID,
                                'method' => HttpMethod::GET,
                                'item_condition' => ItemCondition::_NEW,
                                ]
                        );
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
                // dd($batchArray);
                $offerBatchReq = new \SellingPartnerApi\Model\ProductPricingV0\GetItemOffersBatchRequest();
                $offerBatchReq->setRequests($batchArray);

                try {
                    $offers = $productPrice->getItemOffersBatch($offerBatchReq)->getResponses();

                    //array to hold ready to insert database records
                    //this array is passed by reference to the function.
                    $offers_array = array();

                    //create array for database insert
                    foreach ($offers as $offer) {
                        try {
                            //convert std class to array
                            $offer_detail = json_decode(json_encode($offer->getBody()->getPayload()), true);

                            $this->get_ready_to_db($offer_detail, $offers_array);
                        } catch (\Throwable $th) {

                            $s=$th->getMessage();
                            // throw $th;
                        }
                    }
                    $asins_string = substr_replace(trim($asins_string), "", -1);
                    $asins_string = str_replace("'", "", $asins_string);
                    // dd( $asins_string = "B08PD9J8B3, B07Z8CG6G8");
                    // DB::table('asins_prices_details')->whereIn('asin', $asins_string)->delete();



                    // $query = 'Delete from asins_prices_details where asin in (' . $asins_string . ')';
                    try {
                        $explode_id =  explode(',', $asins_string);
                        // dd($explode_id);
                        foreach ($explode_id as $value) {
                            # code...
                            DB::table('asins_prices_details')->where('asin', $value)->delete();
                        }
                        // dd($explode_id);
                        //   \App\Models\AsinsPricesDetails::destroy($asins_string);
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                    // $offers_array;
                    try {
                        DB::table('asins_prices_details')->insert($offers_array);
                    } catch (\Throwable $th) {
                        throw $th;
                    }
                } catch (\Throwable $th) {
                    throw $th;
                }
            } catch (\Throwable $th) {
                throw $th;
            }
        } else {
            return false;
        }
        // return 'comp';
    }
    // public function get_items_offers($asins = null)
    // {
    //     // dd($asins);
    //     if ($asins != null && count($asins) > 0) {
    //         $this->_asins = $asins;
    //     }

    //     if ($this->_asins != null & count($this->_asins) > 0) {
    //         $amzConfig = Config::getConfiguration();

    //         // $productPrice = new \SellingPartnerApi\Api\ProductPricingApi($amzConfig);
    //         $productPrice = new \SellingPartnerApi\Api\ProductPricingV0Api($amzConfig);

    //         // $chunk = array_chunk($this->_asins, 20)[0];
    //         // $chunk = $this->_asins->chunk(20);
    //         $chunk = $this->_asins;

    //         try {
    //             $batchArray = array();
    //             $asins_string='';

    //             //create batch of items
    //             try {
    //                 foreach ($chunk as $item) {
    //                     $asins_string .= "'" . $item->ASIN . "',";
    //                     // $batchArray[] = new \SellingPartnerApi\Model\ProductPricing\ItemOffersRequest(
    //                     $batchArray[] = new \SellingPartnerApi\Model\ProductPricingV0\ItemOffersRequest(
    //                         [
    //                             'uri' => '/products/pricing/v0/items/' . $item->ASIN . '/offers',
    //                             'marketplace_id' => Config::$siteID,
    //                             'method' => HttpMethod::GET,
    //                             'item_condition' => ItemCondition::_NEW,
    //                             ]
    //                     );
    //                 }
    //             } catch (\Throwable $th) {
    //                 // dd($th->getMessage());
    //                 throw $th;
    //             }
    //             // dd($batchArray);
    //             try {
    //                 $offerBatchReq = new \SellingPartnerApi\Model\ProductPricingV0\GetItemOffersBatchRequest();
    //                 $offerBatchReq->setRequests($batchArray);
    //                 //code...
    //             } catch (\Throwable $th) {
    //                 throw $th;
    //             }

    //             try {
    //                 $offers = $productPrice->getItemOffersBatch($offerBatchReq)->getResponses();

    //                 //array to hold ready to insert database records
    //                 //this array is passed by reference to the function.
    //                 $offers_array = array();

    //                 // dd($offers);
    //                 //create array for database insert
    //                 foreach ($offers as $offer) {
    //                     try {
    //                         //convert std class to array
    //                         $offer_detail = json_decode(json_encode($offer->getBody()->getPayload()), true);

    //                         $this->get_ready_to_db($offer_detail, $offers_array);
    //                     } catch (\Throwable $th) {
    //                         dd($th->getMessage());
    //                         // throw $th;
    //                     }
    //                 }

    //                 //delete old record
    //                 // $con = $this->_storeObject->get_connection();

    //                 // dd($asins_string);
    //                 $asins_string = substr_replace(trim($asins_string), "", -1);
    //                 $asins_string = str_replace("'", "", $asins_string);

    //                 // dd($offers_array);

    //                 // $query = 'Delete from asins_prices_details where asin in (' . $asins_string . ')';
    //                 DB::table('asins_prices_details')->where('asin', $asins_string)->delete();

    //                 DB::table('asins_prices_details')->insert($offers_array);
    //             } catch (\Throwable $th) {
    //                 // return $th->getMessage();
    //                 throw $th;
    //             }
    //             // ========================

    //             // ========================
    //         } catch (\Throwable $th) {
    //             // return $th->getMessage();
    //             throw $th;
    //         }
    //     } else {
    //         return false;
    //     }
    //     // return 'comp';
    // }

    public function setASINs($asinsArray)
    {
        $this->_asins = $asinsArray;
    }

    public function getASINs()
    {
        return $this->_asins;
    }

    public function get_ready_to_db($array, & $records)
    {
        try {
            if (!is_array($array) || count($array)<1) {
                return false;
            }


            $asin = $array['ASIN'];

            $offers = (isset($array['Offers']) && (count($array['Offers'])>0)) ? $array['Offers'] : '';

            if (!is_array($offers)) {
                return false;
            }
            // dd($offers);

            foreach ($offers as $key => $value) {
                $record = array();

                $record['asin'] = $asin;
                $record['seller_id'] = (isset($value['SellerId'])) ? $value['SellerId'] : '';
                $record['item_condition'] = (isset($value['SubCondition'])) ? $value['SubCondition'] : '';
                $record['feedback_rating'] = (isset($value['SellerFeedbackRating']['SellerPositiveFeedbackRating'])) ? $value['SellerFeedbackRating']['SellerPositiveFeedbackRating'] : '0';
                $record['feedback_count'] = (isset($value['SellerFeedbackRating']['FeedbackCount'])) ? $value['SellerFeedbackRating']['FeedbackCount'] : '0';
                $record['shipping_min_hours'] = (isset($value['ShippingTime']['minimumHours'])) ? $value['ShippingTime']['minimumHours'] : '0';
                $record['shipping_max_hours'] = (isset($value['ShippingTime']['maximumHours'])) ? $value['ShippingTime']['maximumHours'] : '0';
                $record['listing_price'] = (isset($value['ListingPrice']['Amount'])) ? $value['ListingPrice']['Amount'] : '0';
                $record['shipping_cost'] = (isset($value['Shipping']['Amount'])) ? $value['Shipping']['Amount'] : '0';
                $record['currency'] = (isset($value['ListingPrice']['CurrencyCode'])) ? $value['ListingPrice']['CurrencyCode'] : '';
                $record['is_fba'] = (isset($value['IsFulfilledByAmazon'])) ? $value['IsFulfilledByAmazon'] : '0';
                $record['is_prime'] = (isset($value['PrimeInformation']['IsPrime'])) ? $value['PrimeInformation']['IsPrime'] : '0';
                $record['is_buybox_winner'] = (isset($value['IsBuyBoxWinner'])) ? $value['IsBuyBoxWinner'] : '0';
                $record['is_featured'] = (isset($value['IsFeaturedMerchant'])) ? $value['IsFeaturedMerchant'] : '0';

                $records[] = $record;
            }
            // dd($records);

            return $records;
        } catch (\Throwable $th) {
            $s = $th->getMessage();
        }
    }


    public function array_flatten($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function generatePriceFeed_XML($productList, $to_increase=false)
    {
        $feed_header = '<?xml version="1.0" encoding="iso-8859-1"?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
		 <Header>
			<DocumentVersion>1.01</DocumentVersion>
			<MerchantIdentifier></MerchantIdentifier>
		  </Header>
		 <MessageType>Price</MessageType>';

        $feed_footer = '</AmazonEnvelope>';
        $feed_body = '';
        $intCounter = 1;
        // $fbastep = (float)Config::$fbastep;
        // $step = (float)Config::$step;
        foreach ($productList as $product) {
            if (!isset($product->SKU) || !isset($product->listing_price) || ($product->our_price == $product->listing_price)) {
                continue;
            }
            $myPrice=(float)$product->listing_price;
            $min_price = (float) $product->min_price;
            $max_price = (float) $product->max_price;

            // $feed_body .= self::create_price_message($intCounter, $product->SKU, $myPrice, $min_price, $max_price);
            $feed_body .= self::create_price_message($intCounter, $product->SKU, $myPrice);

            // // insert data to history table
            // $current_datetime = Carbon::now();
            // $current_datetime = $current_datetime->toDateTimeString();
            // try {
            //     $arrayTemp = [];
            //     $arrayTemp['SKU'] = $product->SKU;
            //     $arrayTemp['ASIN'] = $product->asin;
            //     $arrayTemp['seller_id'] = $product->seller_id;
            //     $arrayTemp['listing_price'] = $product->listing_price;
            //     $arrayTemp['created_at'] =  $current_datetime;
            //     DB::table('amzn_rep_histories')->insert($arrayTemp);
            // } catch (\Throwable $th) {
            //     throw $th;
            // }

            $intCounter++;
        }
        // dd($feedContents);

        $feedContents = $feed_header . $feed_body . $feed_footer;
        return $feedContents;
    }

    public static function create_price_message($message_id, $sku, $price, $min_price = null, $max_price = null)
    {
        $msg = '<Message>
		<MessageID>' . $message_id . '</MessageID>
		<OperationType>Update</OperationType>
			<Price>
				<SKU>' . $sku . '</SKU>
				<StandardPrice currency="'. Config::$CurrecnyCode . '">' . $price . '</StandardPrice>';
        if ($min_price != null && is_numeric(trim($min_price))) {
            $msg .= '<MinimumSellerAllowedPrice currency="'. Config::$CurrecnyCode . '">' . $min_price . '</MinimumSellerAllowedPrice>';
        }

        if ($max_price != null && is_numeric(trim($max_price))) {
            $msg .= '<MaximumSellerAllowedPrice currency="'. Config::$CurrecnyCode . '">' . $max_price . '</MaximumSellerAllowedPrice>';
        }

        $msg .= '
			</Price>
  		</Message>';
        return $msg;
    }

    public static function get_feeds($AmzConfig)
    {
        $createFeedSpecs = [
            'feed_type' => 'GET_AMAZON_FULFILLED_SHIPMENTS_DATA_GENERAL',
            'marketplace_ids' => [config('amz.marketplaces.US')],
        ];
        try {
            $feedsApi = new \SellingPartnerApi\Api\FeedsV20210630Api($AmzConfig);
            $feedIDs = $feedsApi->getFeeds(['POST_FLAT_FILE_INVLOADER_DATA'], ['ATVPDKIKX0DER']);
            $g = $feedsApi->cancelFeed('212669019031');
            $s = '';
        } catch (\Exception $e) {
            throw $e;
            //dd(__METHOD__.': '.$e->getMessage());
        }
    }

    public static function updatePrice($productList=null)
    {
        try {
            if ($productList==null || !is_array($productList)) {
                $seller_id = Config::$mySellerID;
                $tostep = Config::$step;


        \DB::table('pricing_info')->truncate();

        \DB::statement("SET SQL_MODE=''");
                // $productList = DB::select(DB::raw("SELECT listings.SKU, asins_prices_details.asin , asins_prices_details.is_fba, asins_prices_details.seller_id, asins_prices_details.shipping_cost, listings.min_price,listings.max_price, listings.price as our_price , CASE WHEN (SELECT COUNT(DISTINCT seller_id) FROM asins_prices_details WHERE asin = listings.ASIN) = 1 THEN CAST(listings.max_price AS DECIMAL(10,2)) ELSE (SELECT MIN(listing_price + shipping_cost)   FROM asins_prices_details WHERE asin = listings.ASIN AND seller_id != '".$seller_id."' AND listing_price + shipping_cost > CAST(listings.min_price AS DECIMAL(10,2)) ) END AS listing_price FROM listings JOIN asins_prices_details ON asins_prices_details.asin = listings.asin JOIN amzn_repriceables ON listings.SKU = amzn_repriceables.SKU LEFT JOIN tbl_ignore ON asins_prices_details.seller_id = tbl_ignore.seller_id WHERE amzn_repriceables.status = '1' AND tbl_ignore.seller_id IS NULL GROUP BY listings.SKU;"));

        //move operationable ASINs to pricing_info for further processing.
        $productList = DB::statement(DB::raw("INSERT INTO pricing_info
                (SKU, asin, is_fba,shipping_cost,min_price,max_price,our_price,seller_id,listing_price)
                SELECT
                listings_temp.SKU,
                asins_prices_details.asin,
                asins_prices_details.is_fba,
                asins_prices_details.shipping_cost,
                listings_temp.min_price,
                listings_temp.max_price,
                listings_temp.price AS our_price,
                '' as seller_id,
                CASE WHEN(
                SELECT
                COUNT(DISTINCT seller_id)
                FROM
                asins_prices_details
                WHERE
                ASIN = listings_temp.ASIN
                ) = 1 THEN CAST(
                listings_temp.max_price AS DECIMAL(10, 2)
                ) ELSE(
                SELECT
                CASE WHEN (MIN(listing_price + shipping_cost) - ".$tostep.") < listings_temp.min_price THEN listings_temp.min_price ELSE MIN(listing_price + shipping_cost) - ".$tostep." END AS suggested_price
                FROM
                asins_prices_details
                WHERE
                ASIN = listings_temp.ASIN AND seller_id != '".$seller_id."' AND listing_price + shipping_cost > CAST(
                listings_temp.min_price AS DECIMAL(10, 2)
                ) AND seller_id NOT IN (SELECT seller_id FROM tbl_ignore)
                )
                END AS listing_price
                FROM
                listings_temp
                JOIN asins_prices_details ON asins_prices_details.asin = listings_temp.asin
                GROUP BY listings_temp.SKU;"));
            }

            // //do upsert on pric_fetch_history. We will compare the last update for ASIN later for selection purpose.
            // $strSQL = "INSERT INTO price_fetch_history
            // SELECT asin FROM pricing_info
            // ON DUPLICATE KEY UPDATE updated_at ='" . date("Y-m-d H:i:s") . "'";

            // \DB::statement(DB::raw($strSQL));


            //update pricing_info with top_seller, second_seller, top_price and second_price

            $strSQL = "UPDATE pricing_info p
            SET p.top_seller = (SELECT seller_id
                                FROM asins_prices_details ap
                                WHERE ap.ASIN = p.ASIN
                                ORDER BY ap.listing_price + ap.shipping_cost
                                LIMIT 1),
                p.top_price = (SELECT ap.listing_price + ap.shipping_cost
                               FROM asins_prices_details ap
                               WHERE ap.ASIN = p.ASIN
                               ORDER BY ap.listing_price + ap.shipping_cost
                               LIMIT 1),
                p.second_seller = (SELECT seller_id
                                   FROM asins_prices_details ap
                                   WHERE ap.ASIN = p.ASIN
                                   ORDER BY ap.listing_price + ap.shipping_cost
                                   LIMIT 1, 1),
                p.second_price = (SELECT ap.listing_price + ap.shipping_cost
                                  FROM asins_prices_details ap
                                  WHERE ap.ASIN = p.ASIN
                                  ORDER BY ap.listing_price + ap.shipping_cost
                                  LIMIT 1, 1)";

            \DB::statement(DB::raw($strSQL));



            //update pricing_info table with buybox info
            $strSQL = "UPDATE pricing_info
            JOIN asins_prices_details ON pricing_info.ASIN = asins_prices_details.ASIN
            SET pricing_info.is_buybox_winner = asins_prices_details.is_buybox_winner
            WHERE asins_prices_details.seller_id = '".$seller_id."'";

            \DB::statement(DB::raw($strSQL));


            //update pricing_info table with total seller for each ASIN
            $strSQL = "UPDATE pricing_info
            SET total_sellers = (
                SELECT COUNT(seller_id)
                FROM asins_prices_details
                WHERE asins_prices_details.ASIN = pricing_info.ASIN
            )";

            \DB::statement(DB::raw($strSQL));



            //remove records from pricing_info table where there is no suggested price or our_price=listing_price.
            $strSQL = "DELETE from pricing_info where sku not in (select sku from (
                SELECT pf.*,
                case
                when top_seller in (select seller_id from tbl_ignore) and top_price > (min_price+shipping_cost)+".$tostep." and total_sellers=2 THEN top_price+".$tostep."
                when second_seller in (select seller_id from tbl_ignore) and second_price > (min_price+shipping_cost)+".$tostep." and total_sellers=2 and is_buybox_winner=1 THEN second_price+".$tostep."
                else listing_price
                end as suggested_price
                FROM `pricing_info` pf
                where our_price <> listing_price
                and top_seller IS NOT NULL
                ORDER BY `suggested_price`  DESC) result
                where result.`suggested_price` is not null) ";

            \DB::statement(DB::raw($strSQL));


            //remove records from pricing_info table where ignore seller is as a top seller.
            //remove records from pricing_info table where I am as a top seller.
            //remove records from pricing_info table where I am as a buybox winner and there is no chance for price increase.
            // $strSQL = "DELETE from pricing_info where sku not in (select sku from (
            //     SELECT pf.*,
            //     case
            //     when top_seller in (select seller_id from tbl_ignore) and top_price > (min_price+shipping_cost)+".$tostep." and total_sellers=2 THEN top_price+".$tostep."
            //     when second_seller in (select seller_id from tbl_ignore) and second_price > (min_price+shipping_cost)+".$tostep." and total_sellers=2 and is_buybox_winner=1 THEN second_price+".$tostep."
            //     else listing_price
            //     end as suggested_price
            //     FROM `pricing_info` pf
            //     where our_price <> listing_price
            //     and top_seller IS NOT NULL
            //     ORDER BY `suggested_price`  DESC) result
            //     where result.`suggested_price` is not null) ";

            // \DB::statement(DB::raw($strSQL));




            //get the remaning products for repricing
            $productList = DB::select(DB::raw("SELECT * FROM pricing_info;"));

            if(!isset($productList) || count($productList)<1)
            {
                return true;
            }





            //============================

            $feedContents = self::generatePriceFeed_XML($productList);
            $feedType = Config::getPriceConfig()['obj'];
            $createFeedDocSpec = new \SellingPartnerApi\Model\FeedsV20210630\CreateFeedDocumentSpecification(['content_type' => $feedType['contentType']]);

            // die();
            try {
                $feedsApi = new \SellingPartnerApi\Api\FeedsV20210630Api(Config::getConfiguration());

                $feedDocumentInfo = $feedsApi->createFeedDocument($createFeedDocSpec);
                $feedDocumentId = $feedDocumentInfo->getFeedDocumentId();
                $docToUpload = new \SellingPartnerApi\Document($feedDocumentInfo, $feedType);

                $docToUpload->upload($feedContents);

                $createFeedSpecs = [
                    'feed_type' => $feedType['name'],
                    'input_feed_document_id' => $feedDocumentId,
                    'marketplace_ids' => [Config::$siteID],
                ];

                $productFeed = $feedsApi->createFeed(
                    new \SellingPartnerApi\Model\FeedsV20210630\CreateFeedSpecification($createFeedSpecs)
                );
                $productFeedId = $productFeed->getFeedId();
                $result = $feedsApi->getFeed($productFeedId);
                $status = $result->getProcessingStatus();
                $result_feed_document_id = "";
                $attempts = 1;

                try {

                      //insert into history table
                      $fields = "`SKU`, `asin`, `is_fba`, `shipping_cost`, `min_price`, `max_price`,
                      `our_price`, `seller_id`, `listing_price`, `buybox_status`, `top_seller`,
                      `second_seller`, `top_price`, `second_price`, `is_buybox_winner`,
                      `total_sellers`";

                      //push the information to history.
                      $strSQL = "INSERT INTO `amzn_rep_histories`(" . $fields . ") SELECT " . $fields . " FROM `pricing_info` ";
                      \DB::statement(DB::raw($strSQL));

                      //code...
                    } catch (\Throwable $th) {
                        throw $th;
                    }



                set_time_limit(1200);

                // while ($status != 'DONE' && $attempts < 25) {
                //     sleep(30);
                //     $result = $feedsApi->getFeed($productFeedId);
                //     $status = $result->getProcessingStatus();

                //     $attempts++;
                // }

                // $result_feed_document_id = $result->getResultFeedDocumentId();
                // $reportDocumentInfo = null;
                // try {
                //     $reportDocumentInfo = $feedsApi->getFeedDocument($result_feed_document_id, $feedType);
                // } catch (\Throwable $th) {
                //     throw $th;
                //     // return false;
                // }

                // $feedType['contentType'] = 'application/json';
                // $docToDownload = new \SellingPartnerApi\Document($reportDocumentInfo, $feedType);
                // $buffer = $docToDownload->download();

                // // insert data to history table
                // $current_datetime = Carbon::now();
                // $current_datetime = $current_datetime->toDateTimeString();
                // try {
                //     $arrayTemp = [];
                //     $historyArray = [];
                //     foreach ($productList as $item) {
                //         $arrayTemp['SKU'] = $item->SKU;
                //         $arrayTemp['ASIN'] = $item->asin;
                //         $arrayTemp['seller_id'] = $item->seller_id;
                //         $arrayTemp['listing_price'] = $item->our_price;
                //         $arrayTemp['created_at'] =  $current_datetime;
                //         $historyArray[] = $arrayTemp;
                //     }

                //     DB::table('amzn_rep_histories')->insert($historyArray);
                // } catch (\Throwable $th) {
                //     throw $th;
                // }

                return true;
            } catch (\Throwable $th) {
                throw $th;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }




    /**
     * The function downloads current listings and insert into listings table
     */

    public function getListings()
    {
        try {
            $sid=env('G_WORK_SHEET');

            $reportApi = new \SellingPartnerApi\Api\ReportsV20210630Api($this->getAMZConfig());
            // $reportApi = new \SellingPartnerApi\Api\ReportsApi($this->getAMZConfig());

            // set_status('__progress__','Getting Product List. Please wait...');

            $report_id='';
            $marketplace_ids = [Config::$siteID];
            $data = [
                'report_type' => Config::getListingsConfig()['name'],
                'marketplace_ids' => $marketplace_ids,
                'data_start_time' => $this->getDateTime('- ' . Config::getListingsConfig()['interval'] . ' days'),
                'data_end_time' => $this->getDateTime(),
                'report_options' => array('custom' => 'true'),
            ];

            $body = new \SellingPartnerApi\Model\ReportsV20210630\CreateReportSpecification($data);
            // $body = new \SellingPartnerApi\Model\Reports\CreateReportSpecification($data);
            // dd($body);

            try {
                $report_id = $reportApi->createReport($body)->getReportId();
                // dd($report_id);
            } catch (\Exception $e) {
                // set_status('__progress__',$e->getMessage());
                $s = $e->getMessage();
            }

            $report_document_id = "";
            $counter = 0;
            $rptPrepared = false;
            try {
                set_time_limit(1200);
                do {
                    // set_status('__progress__','Waiting for Amazon response...');
                    sleep(30);
                    $report = $reportApi->getReport($report_id);
                    $report_document_id = $report->getReportDocumentId();
                    $reportStatus = $report->getProcessingStatus();
                    // dd($reportStatus);

                    if ($reportStatus == 'DONE') {
                        $rptPrepared = true;
                        $counter = 10;
                    } else {
                        $counter++;
                    }
                } while (($counter <= 30) && (!$rptPrepared));
            } catch (\Exception $e) {
                // set_status('__progress__',$e->getMessage());
                $s = $e->getMessage();
            }

            $reportType = Config::getListingsConfig()['obj'];

            $arr_orders_buf = array();
            $database_array = array();
            if (!empty($report_document_id)) {
                $reportDocumentInfo = null;
                try {
                    $reportDocumentInfo = $reportApi->getReportDocument($report_document_id, $reportType);
                } catch (\Exception $e) {
                    // set_status('__progress__',$e->getMessage());
                    // sleep(20);
                    $s = $e->getMessage();
                }
                $docToDownload = new \SellingPartnerApi\Document($reportDocumentInfo, $reportType);

                try {
                    try {
                        $buffer = $docToDownload->download();
                        // file_put_contents('d:\\test.txt', $buffer);
                    } catch (\SellingPartnerApi\ApiException $th) {
                        // set_status('__progress__',$th->getMessage());
                        // sleep(20)
                        throw $th;
                    }
                } catch (\Throwable $th) {
                    // set_status('__progress__',$th->getMessage());
                    // 	sleep(20)
                    $s = $th->getMessage();
                }
                // set_status('__progress__', 'Report Received. Process for database and google sheet.');


                $_index_title = 0;
                $_index_sku = 0;
                $_index_quantity = 0;
                $_index_price = 0;
                $_index_min_price = 0;
                $_index_max_price = 0;

                if ($buffer) {
                    $_buf = explode(PHP_EOL, trim($buffer));
                    $_buf = explode("\n", $buffer);

                    $titles = str_getcsv($_buf[0], "\t", "\t");

                    $_index_title = array_search('item-name', $titles);
                    $_index_sku = array_search('seller-sku', $titles);
                    $_index_asin = array_search('asin1', $titles);
                    $_index_quantity = array_search('quantity', $titles);
                    $_index_price = array_search('price', $titles);
                    $_index_min_price = array_search('minimum-seller-allowed-price', $titles);
                    $_index_max_price = array_search('maximum-seller-allowed-price', $titles);

                    $listings = [];

                    for ($i = 1; $i < count($_buf); $i++) {
                        try {
                            //code...
                            $listings[] = str_getcsv($_buf[$i], "\t");
                            // dd($listings);
                        } catch (\Throwable $th) {
                            // set_status('__progress__',$th->getMessage());
                            // sleep(20);
                            $e = $th->getMessage();
                        }
                    }

                    foreach ($listings as $key) {
                        try {
                            if (count($key) > 15) {
                                $tempArr['SKU'] = $this->mysql_escape($key[$_index_sku]);
                                $tempArr['ASIN'] = $key[$_index_asin];
                                $tempArr['Title'] = $this->mysql_escape($key[$_index_title]);
                                $tempArr['Price'] = $key[$_index_price];
                                $tempArr['Quantity'] = $key[$_index_quantity];

                                if (is_numeric($_index_min_price) && ($_index_min_price >= 0)) {
                                    $tempArr['min_price'] = $key[$_index_min_price];
                                }

                                if (is_numeric($_index_max_price) && ($_index_max_price >= 0)) {
                                    $tempArr['max_price'] = $key[$_index_max_price];
                                }

                                $database_array[] = $tempArr;
                            // dd($database_array);
                            } else {
                                // dd('els');
                                if (isset($key[3]) && strlen($key[3]) > 50) {
                                    // dd('tempArr');
                                    $see = str_getcsv($key[3], "\t");
                                    $tempArr['SKU'] = $this->mysql_escape($key[$_index_sku]);
                                    $tempArr['ASIN'] = $key[$_index_asin];
                                    $tempArr['Title'] = $this->mysql_escape($see[$_index_title - $_index_title]);
                                    $tempArr['Price'] = $see[$_index_price - $_index_title - 1];
                                    $tempArr['Quantity'] = $see[$_index_quantity - $_index_title - 1];

                                    if (is_numeric($_index_min_price) && ($_index_min_price >= 0) && array_key_exists($_index_min_price, $key)) {
                                        $tempArr['min_price'] = $key[$_index_min_price];
                                    }

                                    if (is_numeric($_index_max_price) && ($_index_max_price >= 0) && array_key_exists($_index_max_price, $key)) {
                                        $tempArr['max_price'] = $key[$_index_max_price];
                                    }

                                    $database_array[] = $tempArr;
                                }
                            }
                            //code...
                        } catch (\Throwable $th) {
                            // set_status('__progress__',$th->getMessage());
                            // sleep(20);
                            throw $th;
                        }
                    }
                    try {
                        //empty table
                        DB::table('listings')->truncate();
                    } catch (\Throwable $th) {
                        throw $th;
                    }

                        try {
                            // //insert new values
                            $insert_data = collect($database_array); // Make a collection to use the chunk method

                            // it will chunk the dataset in smaller collections containing 500 values each.
                            // Play with the value to get best result
                            $chunks = $insert_data->chunk(100);

                            foreach ($chunks as $chunk) {
                                DB::table('listings')->insert($chunk->toArray());
                            }
                        } catch (\Throwable $th) {
                            // set_status('__progress__',$th->getMessage());
                            // sleep(20);
                            throw $th;
                        }

                    //update the repriceable table
                    try {
                        foreach (collect($database_array) as $item) {
                            $repricerTemp['SKU'] = $item['SKU'];
                            $repricerTemp['ASIN'] = $item['ASIN'];
                            $repricerArr[] = $repricerTemp;
                        }
                        DB::table('amzn_repriceables')->upsert($repricerArr,'SKU');

                    } catch(\Throwable $th) {
                        // set_status('__progress__',$th->getMessage());
                        // sleep(20);
                        throw $th;
                    }
                    try {
                        $sheets = GSheet::getGSheets($sid, 'title');
                        if (in_array(config('amz.feed_type.listing.sheet'), $sheets)) {
                            // clear
                            GSheet::clearSheet($sid, config('amz.feed_type.listing.sheet'));
                        } else {
                            GSheet::addGSheet($sid, config('amz.feed_type.listing.sheet'));
                        }

                        // set_status('__progress__', 'Data pushing to Google Sheet.');

                        // write
                        GSheet::appendSheet($sid, config('amz.feed_type.listing.sheet'), array_merge([$titles], $listings));

                        // set_status('__progress__', 'Data pushed successfully. ');
                        // sleep(20);

                        // set_status('__progress__', ' ');
                    } catch (\Throwable $th) {
                        throw $th;
                        // set_status('__progress__',$th->getMessage());
                        // sleep(20);
                    }
                }
            }
        } catch (\Throwable $th) {
            // set_status('__progress__',$th->getMessage());
            // sleep(20);
            throw $th;
        }
    }

    protected function getAMZConfig()
    {
        // dd('getAMZConfig');
        return Config::getConfiguration();
    }

    protected function getDateTime($input = '')
    {
        return (new \DateTime($input))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }

    public function mysql_escape($inp)
    {
        if (is_array($inp)) {
            return array_map(__METHOD__, $inp);
        }

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }


    /**
     * This function will return all asins that have some gap to increase price yet not to loose the buybox.
     */
    public function increaseBuyboxPrice()
    {
        try {
            $bulk_asins = DB::select(DB::raw("SELECT apd.asin, apd.seller_id, apd.listing_price, apd.shipping_cost,apd.currency,apd.is_buybox_winner,l.SKU, l.Price,l.min_price,l.max_price,l.Quantity
            FROM `asins_prices_details` apd inner join listings l on apd.asin=l.ASIN order by asin, listing_price+shipping_cost asc"));


            $asins_to_update = array();
            $temp = array();
            $is_next_to_take = false;

            if (isset($bulk_asins) && count($bulk_asins)>0) {
                foreach ($bulk_asins as $asin) {
                    // dd($asin->seller_id);
                    if (($asin->seller_id == Config::$mySellerID) && ($asin->is_buybox_winner =='1')) {
                        $temp['ASIN']  = $asin->asin ;
                        $temp['SKU '] = $asin->SKU ;
                        $temp['min_price']  = $asin->min_price ;
                        $temp['max_price']  = $asin->max_price ;
                        $temp['listing_price '] = $asin->listing_price  ;
                        $temp['my_shipping']  = $asin->shipping_cost ;
                        $is_next_to_take=true;
                        continue;
                    }

                    if (($is_next_to_take===true)) {
                        $temp['compititor_price']  = $asin->listing_price ;
                        $temp['compititor_shipping']  = $asin->shipping_cost ;
                        $temp['seller_id']  = $asin->seller_id ;

                        $asins_to_update[] = $temp;

                        //re-initiliaze array
                        $temp = array();

                        //make the is_next_to_take to false;
                        $is_next_to_take=false;
                    }
                }
            }
            // dd($asins_to_update);
            //Update price
            $this->updatePrice(collect($asins_to_update));
            return true;
        } catch (\Throwable $th) {
            throw $th;

            //throw $th;
        }
    }
}
