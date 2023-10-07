<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Seller;
use App\Http\Controllers\HomeController;
use SellingPartnerApi\Configuration;
use DateTime;
use Carbon\Carbon;

class TrackingController extends Controller
{
    //  * this function upload trackings to amazon orders

    public function update_trackings($feedArray)
    {
        try {
            // $config = $this->getAMZConfig();
            $config = get_amazon_config();


            $feedContents = $this->generateTrackingFeed_XML($feedArray, 1);

            $feedType = \SellingPartnerApi\FeedType::POST_ORDER_FULFILLMENT_DATA; //"POST_ORDER_FULFILLMENT_DATA";

            $createFeedDocSpec = new \SellingPartnerApi\Model\Feeds\CreateFeedDocumentSpecification(['content_type' => $feedType['contentType']]);

        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        try {
            $feedsApi = new \SellingPartnerApi\Api\FeedsApi($config);

            $feedDocumentInfo = $feedsApi->createFeedDocument($createFeedDocSpec);
            $feedDocumentId = $feedDocumentInfo->getFeedDocumentId();
            $docToUpload = new \SellingPartnerApi\Document($feedDocumentInfo, $feedType);
            // die();
            $docToUpload->upload($feedContents);

            $createFeedSpecs = [
                'feed_type' => $feedType['name'],
                'input_feed_document_id' => $feedDocumentId,
                'marketplace_ids' => [config('amz.marketplaces.GB')],
            ];

            $productFeed = $feedsApi->createFeed(
                new \SellingPartnerApi\Model\Feeds\CreateFeedSpecification($createFeedSpecs)
            );
            $productFeedId = $productFeed->getFeedId();
            $result = $feedsApi->getFeed($productFeedId);
            $status = $result->getProcessingStatus();
            $result_feed_document_id = "";
            $attempts = 1;

            while ($status != 'DONE' && $attempts < 15) {
                sleep(20);
                $result = $feedsApi->getFeed($productFeedId);
                $status = $result->getProcessingStatus();
                $attempts++;
            }

            $result_feed_document_id = $result->getResultFeedDocumentId();
            $reportDocumentInfo = null;
            try {
                $reportDocumentInfo = $feedsApi->getFeedDocument($result_feed_document_id, $feedType);
            } catch (\Exception $e) {
                return $e->getMessage();
            }

            $feedType['contentType'] = 'application/json';
            $docToDownload = new \SellingPartnerApi\Document($reportDocumentInfo, $feedType);
            $buffer = $docToDownload->download();

            $directory = storage_path('uploaded-tracking/');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $filePath = $directory . 'buffer_result.xml';
            file_put_contents($filePath, $buffer);


            return true;

        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }


    public function generateTrackingFeed_XML($productList)
    {
        try {
            $feed_header = '<?xml version="1.0" encoding="UTF-8"?>
		<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
		 <Header>
			<DocumentVersion>1.01</DocumentVersion>
			<MerchantIdentifier></MerchantIdentifier>
		  </Header> 
		 <MessageType>OrderFulfillment</MessageType>';

            $feed_footer = '</AmazonEnvelope>';
            $feed_body = '';
            $intCounter = 1;

            if(is_array($productList) && count($productList) > 0) {
                // foreach ($productList as $product) {

                $shipping_method = null;

                if(isset($productList['shipping-method'])) {
                    $shipping_method = $productList['shipping-method'];
                }

                $feed_body .= $this->create_tracking_message($intCounter, $productList['order-id'], $productList['shipdate'], $productList['CarrierCode'], $productList['tracking'], $shipping_method);
                $intCounter++;
                // }

            }

            $feedContents = $feed_header . $feed_body . $feed_footer;

            return $feedContents;
        } catch (\Throwable $th) {
            return $th->getMessage();

        }
    }


    public function create_tracking_message($message_id, $order_id, $ship_date, $carriercode, $tracking = null, $shipping_method = null)
    {
        try {
            $msg = '<Message>
			<MessageID>' . $message_id . '</MessageID>
				<OrderFulfillment>
					<AmazonOrderID>' . $order_id . '</AmazonOrderID>
					<FulfillmentDate>' . $this->getDateTime($ship_date) . '</FulfillmentDate>
					<FulfillmentData>
						<CarrierCode>' . $carriercode . '</CarrierCode>';

            if($shipping_method != null) {
                $msg .= '<ShippingMethod>' . $shipping_method . '</ShippingMethod>';
            }
            if($tracking != null) {
                $msg .= '<ShipperTrackingNumber>' . $tracking . '</ShipperTrackingNumber>';
            }
            $msg .= '</FulfillmentData>
				</OrderFulfillment>
			  </Message>';
            return $msg;

        } catch (\Throwable $th) {
            return $th->getMessage();

        }

    }

    protected function getDateTime($input = '')
    {
        return (new \DateTime($input))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }

    // AMZ-Config
    protected function getAMZConfig()
    {
        try {
            return new Configuration(config('amz.config'));
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

}
