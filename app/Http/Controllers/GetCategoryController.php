<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use SellingPartnerApi;

class GetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCategory(Request $request)
    {

        $catId  = isset($request->catId) ? $request->catId : 'root';
        $id     = Subscriber::where('user_id', '=', auth()->user()->email)
        ->where('site_code','=','GB')
        ->get();

        $config = get_amazon_config($id[0]->user_id,$id[0]->amz_seller_id,'GB');

        try {

			$reportApi = new \SellingPartnerApi\Api\ReportsApi($config);

            $reportTypeValue = \SellingPartnerApi\ReportType::GET_XML_BROWSE_TREE_DATA;
            $marketplace_ids = [config('amz.marketplaces.GB')];

			$data = [
				'report_type' => $reportTypeValue['name'],
				'marketplace_ids' => $marketplace_ids,
				'data_start_time' => $dateFrom, //$this->getDateTime('- '. $numDays .' days - 1 hours'),
				'data_end_time' => $dateTo,
                'RootNodesOnly' => true
			];

			$body = new \SellingPartnerApi\Model\Reports\CreateReportSpecification($data);

            try {
                $report_id = $reportApi->createReport($body)->getReportId();
            }
            catch (Exception $e) {
                throw $e;
                 return redirect()->route('home')->with('error',__METHOD__.': '.$e->getMessage());   
            }


            $report_document_id = "";
            $counter=0;
            $rptPrepared = false;
            try {
                do {
                    set_status('__progress__', 'Waiting for Amazon response...');
                    sleep(30);
                    $report = $reportApi->getReport($report_id);
                    $report_document_id = $report->getReportDocumentId();
                    $reportStatus = $report->getProcessingStatus();

                    if ($reportStatus == 'DONE') {
                        $rptPrepared =true;
                        $counter = 30;
                    } elseif ($reportStatus == 'CANCELLED') {
                        return redirect()->route('home')->with('error', __METHOD__.': Report is cancelled');
                    }else{
                        $counter++;
                    }
                } while (($counter <= 30) && (!$rptPrepared));
            } catch (\Exception $e) {
                set_status('__progress__', $e->getMessage());
                // throw $e;
                return redirect()->route('home')->with('error', __METHOD__.': '.$e->getMessage());
            }



            if (!empty($report_document_id)) {
                $reportDocumentInfo = null;
                try {
                    $reportDocumentInfo = $reportApi->getReportDocument($report_document_id,  $reportTypeValue);
                } catch (\Throwable $e) {
                    return response()->json(['response'=>['status'=>'failure','error'=>$e->getMessage(),'info'=>'']]);
            
                }
                $docToDownload = new \SellingPartnerApi\Document($reportDocumentInfo,  $reportTypeValue);
                $buffer = $docToDownload->download();

                $path = storage_path($report_path . '/') . $fileName . ".txt";
                file_put_contents($path, $buffer);


                //Remove the report from XML
                $this->removeReport($report_id);

                return response()->json(['response'=>['status'=>'success','error'=>'','info'=>'report is DOWNLOADED']]);
                    

            }
            


            // $listingDef = new \SellingPartnerApi\Api\ProductTypeDefinitionsApi($config);

            // $def = $listingDef->searchDefinitionsProductTypes(config('amz.marketplaces.GB'), 'phone');

            // $detail = $listingDef->getDefinitionsProductType('RECREATION BALL', config('amz.marketplaces.GB'), null, 'LATEST', 'LISTING');
            // var_dump($detail);
            // $str = $def;



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
