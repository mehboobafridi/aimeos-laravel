<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AmazonOrder;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use DB;

class ShowOrdersController extends Controller
{
    public function load_amazon_orders(Request $request)
    {
        try {

            $totalFilteredRecord = $totalDataRecord = $draw_val = "";

            $whereStatment = '';
            $whereStatementOrderDetails = "";
            $isUnshipped = false;
            $request_data = $request->all();

            $page_order_status = $request_data['order_status'];

            // if(isset($request->isShipped)) {
            //     //shipped-orders
            //     $whereStatment = ' amazon_orders.order_status="Shipped" ';
            // } elseif(isset($request->isCanceled)) {
            //     //canceled-orders
            //     $whereStatment = '(amazon_orders.order_status="Canceled" OR amazon_orders.is_cancellation_requested="1" 
            //         AND amazon_orders.order_status != "Unshipped" AND amazon_orders.order_status != "PartiallyShipped" AND amazon_orders.order_status != "Shipped") ';
            // } elseif(isset($request->newOrders)) {
            //     try {
            //         $whereStatment = "(amazon_orders.order_status='Unshipped')";

            //     } catch (\Throwable $th) {
            //         throw $th;
            //     }
            // }


            if($page_order_status == 'Shipped') {
                //shipped-orders
                $whereStatment = ' amazon_orders.order_status="Shipped" ';
            } elseif($page_order_status == 'Canceled') {
                //canceled-orders
                $whereStatment = '(amazon_orders.order_status="Canceled" OR amazon_orders.is_cancellation_requested="1" 
                    AND amazon_orders.order_status != "Unshipped" AND amazon_orders.order_status != "PartiallyShipped" AND amazon_orders.order_status != "Shipped") ';
            } elseif($page_order_status == 'Unshipped') {
                try {
                    // $whereStatment = "(amazon_orders.order_status='Unshipped') OR amazon_orders.order_status='PartiallyShipped' ";
                    $whereStatment = "(amazon_orders.order_status='Unshipped')";

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
}
