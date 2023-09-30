<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Region;
use App\Models\Subscriber;
use Auth;
use SimpleXMLElement;
use App\Models\Site;

use Yajra\DataTables\DataTables;

class SubscriberController extends Controller
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
        try {
            $record = Subscriber::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting record'], 500);
        }
    }


    
    public function auth(Request $request)
    {
        try {
            $site_code = isset($request->site_code) ? $request->site_code : 'US';
            $state_id = bin2hex(random_bytes(32));
            $sites = Site::where('site_code','=',$site_code)->get();
    
            $this->insertOrUpdateSubscriber($site_code, $state_id);
    
            if (!session_id()) {
                session_start();
            }
    
            $site_url;
            
            if (count($sites) > 0) {
                $site_url = $sites[0]->site_url;
            } else {
                $site_url = 'https://sellercentral.amazon.com';
            }

            $_SESSION['spapi_auth_state'] = $state_id;
            $_SESSION['spapi_auth_time'] = time();
    
            $query = [
                'application_id' => env('SPAPI_APP_ID'),
                'state' => $state_id,
                // 'redirect_uri' => env('REDIRECT_URL'),
                'version' => 'beta',
            ];
            
            $site_url.= '/apps/authorize/consent';
            $site_url .= '?' . http_build_query($query);
    
            return response()->json(['uri'=>$site_url]);
            // Use JavaScript to open the URL in a new window
            // echo '<script>window.open("' .  . '", "_blank");</script>';
    
            // // Exit the PHP script
            // exit;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    
    public function insertOrUpdateSubscriber($site_code, $state_id)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        if (!Auth::check()) {
            // If not authenticated, redirect to the login page
            return redirect('/login');
        }
        // Get the user's name
        $user_id = $user->email;

        // Check if a subscriber record with the same user name and site_code exists
        $existingSubscriber = Subscriber::where('user_id', $user_id)
            ->where('site_code', $site_code)
            ->first();

        if ($existingSubscriber) {
            // If a record exists, update it
            $existingSubscriber->update([
                // Update any other fields as needed
                'state_id' => $state_id,
             ]);

        } else {
            // If no record exists, create a new one
            Subscriber::create([
                'user_id'   => $user_id,
                'site_code' => $site_code,
                'state_id'  => $state_id,
                // Add other fields as needed
             ]);

        }
    }

    public function amzCallBack(Request $request)
    {
        try {

            if (!session_id()) {
                session_start();
            }

            $time = 1800;

            // oauth to get token
            $selling_partner_id = isset($_GET[ 'selling_partner_id' ]) ? $_GET[ 'selling_partner_id' ] : '';

            if (empty($selling_partner_id)) {
                echo ('Seller ID not returned');
                die();
            }

            //we get the spapi_oauth_code from redirect response from Amazon
            $spapi_oauth_code = isset($_GET[ 'spapi_oauth_code' ]) ? $_GET[ 'spapi_oauth_code' ] : '';
            if (empty($spapi_oauth_code)) {
                echo ('Auth code not returned');
                die();
            }


             //we get the spapi_oauth_code from redirect response from Amazon
             $state_id = isset($_GET[ 'state' ]) ? $_GET[ 'state' ] : '';
             if (empty($state_id)) {
                 echo ('State code not returned');
                 die();
             }

             $lwc_id = env('AMZ_CLIENT_ID');
             $lwc_sec = env('AMZ_CLIENT_SECRET');
             
            try {

                //now passing the auth code for token exchange. for this we need to pass auth code, lwa client id, and lwa client secret.
                $token = $this->get_auth_code_to_access_token($spapi_oauth_code, $selling_partner_id, $lwc_id, $lwc_sec);

                if ($token === false) {
                    echo ('Token not found.');
                    die();
                }

                $check_existance = Subscriber::Where('state_id', '=', $state_id)->pluck('id');

                if (count($check_existance) > 0) {

                    Subscriber::Where("state_id", $state_id)->update(
                        array(
                            "access_token"     => $token['access_token' ],
                            "refresh_token"    => $token['refresh_token' ],
                            "updated_at" => date('Y-m-d H:i:s'),
                            "amz_seller_id" => $selling_partner_id,
                        )
                    );

                    return response()->json([
                        'status' => 200,
                        'msg'    => 'Seller saved successfully!',
                     ]);
                }

                
                return response()->json([
                    'status' => 200,
                    'msg'    => 'Seller saved successfully!',
                 ]);

            } catch (\Exception $e) {
                print_r($e);
                exit;
            }

        } catch (\Throwable $th) {
            throw $th;
        }

    }



    public function get_refresh_token($auth_code)
    {

        $client = new \GuzzleHttp\Client();
        $res    = null;
        try {
            $res = $client->request('POST', 'https://api.amazon.com/auth/o2/token', [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $auth_code,
                    'client_id'     => config('amz.config.lwaClientId'),
                    'client_secret' => config('amz.config.lwaClientSecret'),
                    'redirect_uri'  => 'http://localhost:8080/frtplus/public/',
                 ],
             ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $info = json_decode($e->getResponse()->getBody()->getContents(), true);
            if ($info[ 'error' ] === 'invalid_grant') {
                return redirect()->route('amzAuth')->with('error', 'bad_oauth_token');
            } else {
                throw $e;
            }
        }

        $body = json_decode($res->getBody(), true);

        return $body;

    }


    public function get_auth_code_to_access_token($spapi_oauth_code, $selling_partner_id, $lwa_client, $lwa_secret)
    {

        try {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.amazon.com/auth/o2/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'grant_type'    => 'authorization_code',
                'code'          => $spapi_oauth_code,
                'client_id'     => $lwa_client,
                'client_secret' => $lwa_secret,
                'version' => 'beta',
             ]));

            $output = curl_exec($ch);
            // echo($output);
            curl_close($ch);

            $body = json_decode($output, true);

            if (isset($body[ 'error' ]) === true) {
                return false;
            }

            $access_token  = $body[ 'access_token' ];
            $refresh_token = $body[ 'refresh_token' ];
            $expires_in    = $body[ 'expires_in' ];

           

            return [ 'refresh_token' => $refresh_token, 'access_token' => $access_token, 'selling_partner_id' => $selling_partner_id ];

        } catch (\Throwable $e) {
            Throw $e;
        }

    }

    public function getData()
    {
        // $subscribers = Subscriber::select('*');
        $subscribers = Subscriber::whereNotNull('refresh_token')->with('user')->get();

        
        return DataTables::of($subscribers)->make(true);
    }

}
