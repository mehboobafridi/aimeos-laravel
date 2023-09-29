<?php

namespace App\Classes\AmzRepClasses;

use SellingPartnerApi\Configuration;
use SellingPartnerApi\Endpoint;
use DB;
use Illuminate\Support\Facades\Auth;

class Config
{
    /**
     * marketplace ID i.e A1F83G8C2ARO7P is GB
     */

    public static $siteID; // = 'A1F83G8C2ARO7P';


    /**
     * Amazon seller id of the account you want the repricer to operate on.
     */
    public static $mySellerID; //= 'AVVKLE3L21ZDG';
    // "A32ISBB9JGDYQC"


    public static $pricing_chunk = 200;

    public static $frequency_wait = 5;
    /**
    * Currency code for price feed. i.e USD for US, GB for UK, EUR for Europeon Countries etc
    */
    public static $CurrecnyCode; // = 'GBP';


    /**
    * The step/gap you want to keep between yours and another seller.
    */
    // public $step; // = 0.05;
    public static $step;  // = 0.05;

    public static $fbastep;

    // public static $lwaClientId;
    // public static $lwaClientSecret;
    // public static $lwaRefreshToken;
    // public static $awsAccessKeyId;
    // public static $awsSecretAccessKey;
    // public static $roleArn;
    // // public static $endpoint = Endpoint::NA;
    // public static $endpoint = Endpoint::EU;

    public static function loadConfig()
    {
        // $loginCheck = auth()->user();
        // if (!(isset($loginCheck))) {
        //     // dd('not');
        //     redirect()->route('home');
        // // $currentUser = auth()->user()->email;
        // } else {
        //     dd('set');
        // }
        // $configData = DB::table("amzn_rep_settings")->where("user_id", "=", $currentUser)->get();
        $configData = DB::table("amzn_rep_settings")->first();
        self::$siteID = $configData->siteID;
        self::$mySellerID = $configData->mySellerID;
        self::$CurrecnyCode = $configData->CurrecnyCode;
        self::$step = $configData->step;
        self::$fbastep = $configData->fbastep;
    }

     /**
      * Amazon SP-API Keys.
      */
     

    public static $lwaClientId = "";
    public static $lwaClientSecret = "";
    public static $lwaRefreshToken = "";
    public static $awsAccessKeyId = "";
    public static $awsSecretAccessKey = "";
    public static $roleArn = "";
    public static $endpoint = Endpoint::NA;




    /**
     * Return Amazon Marketplace IDs
     */
    public static $MARKETPLACE = [
        'CA' => 'A2EUQ1WTGCTBG2',
        'MX' => 'A1AM78C64UM0Y8',
        'US' => 'ATVPDKIKX0DER',
        'AE' => 'A2VIGQ35RCS4UG',
        'DE' => 'A1PA6795UKMFR9',
        'EG' => 'ARBP9OOSHTCHU',
        'ES' => 'A1RKKUPIHCS9HS',
        'FR' => 'A13V1IB3VIYZZH',
        'GB' => 'A1F83G8C2ARO7P',
        'IN' => 'A21TJRUUN4KGV',
        'IT' => 'APJ6JRA9NG5V4',
        'NL' => 'A1805IZSGTT6HS',
        'PL' => 'A1C3SOZRARQ6R3',
        'SA' => 'A17E79C6D8DWNP',
        'SE' => 'A2NODRKZP88ZB9',
        'TR' => 'A33AVAJ2PDY3EV',
        'SG' => 'A19VAU5U5O7RUS',
        'AU' => 'A39IBJ37TRP1C6',
        'JP' => 'A1VC38T7YXB528',
    ];


    public static function getConfiguration()
    {
        // dd('getConfiguration');

        return new Configuration([
            "lwaClientId" => self::$lwaClientId,
            "lwaClientSecret" => self::$lwaClientSecret,
            "lwaRefreshToken" => self::$lwaRefreshToken,
            "awsAccessKeyId" => self::$awsAccessKeyId,
            "awsSecretAccessKey" => self::$awsSecretAccessKey,
            "endpoint" => self::$endpoint,
            'roleArn' => self::$roleArn
        ]);
    }


    /**
     * The step/gap you want to keep between yours and another seller.
     */
    // public static function getDBConfig()
    // {
    //     return [
    //         'server' => self::$_server,
    //         'database' => self::$_database,
    //         'username' => self::$_username,
    //         'password' => self::$_userpass,
    //     ];
    // }


     /**
     * configuration parameters for listings API.
     */
    public static function getListingsConfig()
    {
        return [
            'name' => 'GET_MERCHANT_LISTINGS_ALL_DATA',
            'obj' => \SellingPartnerApi\ReportType::GET_MERCHANT_LISTINGS_ALL_DATA,
            'interval' => 30,
        ];
    }

     /**
     * configuration parameters for price API.
     */
    public static function getPriceConfig()
    {
        return [
            'name' => 'POST_PRODUCT_PRICING_DATA',
            'obj' => \SellingPartnerApi\FeedType::POST_PRODUCT_PRICING_DATA,
            'interval' => 10
        ];
    }
}


// Call the loadConfig method to populate the static variables
Config::loadConfig();
