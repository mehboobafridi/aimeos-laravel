<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Subscriber;
use Auth;
use Illuminate\Http\Request;
use SimpleXMLElement;
use Yajra\DataTables;
use App\Models\Site;

class HomeController extends Controller
{
    public function home(Request $request)
    {

        try {
            
            $sites = Site::all();

 

            return view('index', compact('sites'));
        //code...
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return view('pages-404');
    }


    
    // download order and write to gsheet
    public function getListingsEssentials(Request $request)
    {

        $tokens        = request_amazon_new_token();
        $access_token  = $tokens[ 'access_token' ];
        $refresh_token = $tokens[ 'refresh_token' ];
        $expires_in    = $tokens[ 'expires_in' ];

        $configArray = array(
            'lwaClientId'           => config('amz.config.lwaClientId'),
            'lwaClientSecret'       => config('amz.config.lwaClientSecret'),
            'awsAccessKeyId'        => config('amz.config.awsAccessKeyId'),
            'awsSecretAccessKey'    => config('amz.config.awsSecretAccessKey'),
            'endpoint'              => config('amz.config.endpoint'),
            'accessToken'           => $access_token,
            'lwaRefreshToken'       => $refresh_token,
            'accessTokenExpiration' => $expires_in,
            'roleArn'               => env('AWS_ROLE_ARN'));

        $config = new \SellingPartnerApi\Configuration($configArray);

        try {

            $listingDef = new \SellingPartnerApi\Api\ProductTypeDefinitionsApi($config);

            $def = $listingDef->searchDefinitionsProductTypes(config('amz.marketplaces.GB'),'phone');

            $detail = $listingDef->getDefinitionsProductType('RECREATION BALL',config('amz.marketplaces.GB'),null,'LATEST','LISTING');
            var_dump($detail);
            $str = $def;

        } catch (\Throwable $th) {
            throw $th;
        }

        die();
        $orderApi = new \SellingPartnerApi\Api\OrdersApi($config);

        // $address = $orderApi->getOrder("114-5867791-9325056");
        $address = $orderApi->getOrder($order_id, array("buyerInfo", "shippingAddress"));

        $response_to_array = json_decode(response()->json($address)->content(), true);

        header('Content-type: text/xml');
        $xml_response = ArrayToXml::convert($response_to_array, [  ], true, 'UTF-8', '1.1');
        return response($xml_response, 200);

    }

}
