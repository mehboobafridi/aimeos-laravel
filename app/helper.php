<?php

use Illuminate\Support\Facades\DB;
use App\Models\Token;
use SellingPartnerApi\Configuration;
use App\Models\Label;
use Illuminate\Support\Facades\Log;

if (!function_exists('get_label')) {
    function get_label($shipment_id)
    {
        try {
            // Retrieve the label record from the database
            $label = Label::where('ShipmentId', $shipment_id)->select('Label')->first();

            // Check if the label record exists
            if (!$label) {
                return false;
            }

            return $label;

        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            return false;
        }
    }
}

if (!function_exists('expired_amazon_token')) {
    function expired_amazon_token()
    {

        try {

            // Retrieve the token record from the database
            $token = Token::first();

            // Check if the token record exists
            if (!$token) {
                return true;
            }

            // Check if the token needs to be refreshed
            $now = now();
            $updated_at = $token->updated_at ?? $now;
            $diffInSeconds = $now->diffInSeconds($updated_at);

            if($diffInSeconds >= 3500) {
                // Delete the previous token
                if ($token->exists) {
                    $token->delete();
                }
                return true;
            } else {
                return false;
            }

        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            Log::info('An error occurred in expired_amazon_token function: ' . $th->getMessage());

            return false;
        }
    }
}

if (!function_exists('updateToken')) {
    function updateToken()
    {
        // Retrieve the token record from the database
        $token = Token::first();

        // Check if the token record exists
        if (!$token) {
            // If the token record does not exist, create a new one
            $token = new Token();
        }

        // Check if the token needs to be refreshed
        $now = now();
        $updated_at = $token->updated_at ?? $now;
        $diffInSeconds = $now->diffInSeconds($updated_at);
        $shouldRefresh = $diffInSeconds >= 3500;

        if ($shouldRefresh) {
            // If the token needs to be refreshed, get a new one
            $newToken = request_amazon();
            if (!$newToken) {
                // If the new token cannot be retrieved, return an error response
                return response()->json(['message' => 'Failed to refresh token'], 500);
            }

            // Delete the previous token
            if ($token->exists) {
                $token->delete();
            }

            // Create a new token record
            $token = new Token();
            $token->access_token = $newToken['access_token'];
            $token->refresh_token = $newToken['refresh_token'];
            $token->save();
        }

        // Return the access token
        return response()->json(['access_token' => $token->access_token]);
    }
}



if (!function_exists('request_amazon_new_token')) {
    function request_amazon_new_token()
    {

        try {

            $client = new \GuzzleHttp\Client();
            $res = null;
            try {
                $res = $client->request('POST', 'https://api.amazon.com/auth/o2/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => config('amz.config.lwaClientId'),
                        'client_secret' => config('amz.config.lwaClientSecret'),
                        'refresh_token' => config('amz.config.lwaRefreshToken'),
                    ],
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                return false;
            }

            $tokens = json_decode($res->getBody(), true);

            if(isset($tokens) && is_array($tokens) && count($tokens) > 0) {
                return $tokens;
            }
            //code...
        } catch (\Throwable $th) {
            Log::info('An error occurred in expired_amazon_token function: ' . $th->getMessage());
            return false;
            //throw $th;
        }
    }
}


if (!function_exists('get_amazon_token')) {
    function get_amazon_token()
    {

        try {

            if (expired_amazon_token() === true) {
                // If the token needs to be refreshed, get a new one
                $newToken = request_amazon_new_token();
                if (!$newToken) {
                    // If the new token cannot be retrieved, return an error response
                    return response()->json(['message' => 'Failed to refresh token'], 500);
                }

                // Create a new token record
                $token = new Token();
                $token->access_token = $newToken['access_token'];
                $token->refresh_token = $newToken['refresh_token'];
                $token->expires_in = $newToken['expires_in'];
                $token->save();

                // Return token object
                return $newToken;
            } else {
                return Token::first()->toArray();
            }


        } catch (\Throwable $th) {
            Log::info('An error occurred in get_amazon_token: ' . $th->getMessage());

            return false;
            //throw $th;
        }
    }
}



if (!function_exists('get_amazon_config')) {
    function get_amazon_config()
    {

        try {

            $tokens = get_amazon_token();
            if(isset($tokens) && is_array($tokens) && count($tokens) > 0) {
                $access_token = $tokens['access_token'];
                $refresh_token = $tokens['refresh_token'];
                $expires_in = $tokens['expires_in'];

                $configArray = array(
                    'lwaClientId' => config('amz.config.lwaClientId'),
                    'lwaClientSecret' => config('amz.config.lwaClientSecret'),
                    'awsAccessKeyId' => config('amz.config.awsAccessKeyId'),
                    'awsSecretAccessKey' => config('amz.config.awsSecretAccessKey'),
                    'endpoint' => config('amz.config.endpoint'),
                    'accessToken' => $access_token,
                    'lwaRefreshToken' => $refresh_token,
                    'accessTokenExpiration' => 3000,
                    'roleArn' => env('AWS_ROLE_ARN'));

                $config = new Configuration($configArray);
                return $config;
            } else {
                return false;
            }

            //code...
        } catch (\Throwable $th) {
            Log::info('An error occurred in get_amazon_config: ' . $th->getMessage());

        }


    }
}

if (!function_exists('get_token_by_id')) {

    function get_token_by_id($id = '')
    {

        if(empty($id)) {
            $id = auth()->user()->email;
        }

        $token = DB::table('tokens')->select('refreshtoken')->get();

        if(isset($token) && (count($token) > 0)) {
            return $token[0]->refreshtoken;
        } else {
            return '';
        }

    }
}




if (!function_exists('set_status')) {
    function set_status($id = '', $status = '')
    {

        DB::delete("Delete from tbl_progress");
        // sleep(1);
        DB::insert("INSERT INTO tbl_progress(progress) Values ('" . $status . "');");
    }
}



if (!function_exists('get_status')) {
    function get_status()
    {

        $prog = DB::table('tbl_progress')->select('progress')->get();

        $msg = $prog[0]->refreshtoken;


        return response()->json([
            "status" => 200,
            "__progress__" => $msg,
        ]);

    }
}


if (!function_exists('get_designation_color')) {
    function get_designation_color($designation_name)
    {
        switch (strtolower($designation_name)) {
            case "nsm":
                return '#32CD32';
                break;
            case "zsm":
                return '#20B2AA';
                break;
            case "asm":
                return '#FFD700';
                break;
                break;
            case "tm":
                return '#F4A460';
                break;
                break;
            case "som":
                return '#D2691E';
                break;
                break;
            case "sc":
                return '#BC8F8F';
                break;
                break;
            case "tse":
                return '#00BFFF';
                break;
            case "mdo":
                return '#FF8C00';
                break;
            case "ss":
                return '#DC143C';
                break;
            case "se":
                return '#48D1CC';
                break;
            default:
                return '#32CD32';
        }
    }
}

if (!function_exists('get_colors_array')) {
    function get_colors_array()
    {

        return array('nsm' => '32CD32', 'zsm' => '20B2AA', 'asm' => 'FFD700', 'tm' => 'F4A460', 'som' => 'D2691E', 'sc' => 'BC8F8F', 'tse' => '00BFFF', 'mdo' => 'FF8C00', 'ss' => 'DC143C', 'se' => '48D1CC', 'aliceblue' => 'f0f8ff', 'antiquewhite' => 'faebd7', 'aqua' => '00ffff', 'aquamarine' => '7fffd4', 'azure' => 'f0ffff', 'beige' => 'f5f5dc', 'bisque' => 'ffe4c4', 'black' => '000000', 'blanchedalmond' => 'ffebcd', 'blue' => '0000ff', 'blueviolet' => '8a2be2', 'brown' => 'a52a2a', 'burlywood' => 'deb887', 'cadetblue' => '5f9ea0', 'chartreuse' => '7fff00', 'chocolate' => 'd2691e', 'coral' => 'ff7f50', 'cornflowerblue' => '6495ed', 'cornsilk' => 'fff8dc', 'crimson' => 'dc143c', 'cyan' => '00ffff', 'darkblue' => '00008b', 'darkcyan' => '008b8b', 'darkgoldenrod' => 'b8860b', 'dkgray' => 'a9a9a9', 'darkgray' => 'a9a9a9', 'darkgrey' => 'a9a9a9', 'darkgreen' => '006400', 'darkkhaki' => 'bdb76b', 'darkmagenta' => '8b008b', 'darkolivegreen' => '556b2f', 'darkorange' => 'ff8c00', 'darkorchid' => '9932cc', 'darkred' => '8b0000', 'darksalmon' => 'e9967a', 'darkseagreen' => '8fbc8f', 'darkslateblue' => '483d8b', 'darkslategray' => '2f4f4f', 'darkslategrey' => '2f4f4f', 'darkturquoise' => '00ced1', 'darkviolet' => '9400d3', 'deeppink' => 'ff1493', 'deepskyblue' => '00bfff', 'dimgray' => '696969', 'dimgrey' => '696969', 'dodgerblue' => '1e90ff', 'firebrick' => 'b22222', 'floralwhite' => 'fffaf0', 'forestgreen' => '228b22', 'fuchsia' => 'ff00ff', 'gainsboro' => 'dcdcdc', 'ghostwhite' => 'f8f8ff', 'gold' => 'ffd700', 'goldenrod' => 'daa520', 'gray' => '808080', 'grey' => '808080', 'green' => '008000', 'greenyellow' => 'adff2f', 'honeydew' => 'f0fff0', 'hotpink' => 'ff69b4', 'indianred' => 'cd5c5c', 'indigo' => '4b0082', 'ivory' => 'fffff0', 'khaki' => 'f0e68c', 'lavender' => 'e6e6fa', 'lavenderblush' => 'fff0f5', 'lawngreen' => '7cfc00', 'lemonchiffon' => 'fffacd', 'lightblue' => 'add8e6', 'lightcoral' => 'f08080', 'lightcyan' => 'e0ffff', 'lightgoldenrodyellow' => 'fafad2', 'ltgray' => 'd3d3d3', 'lightgray' => 'd3d3d3', 'lightgrey' => 'd3d3d3', 'lightgreen' => '90ee90', 'lightpink' => 'ffb6c1', 'lightsalmon' => 'ffa07a', 'lightseagreen' => '20b2aa', 'lightskyblue' => '87cefa', 'lightslategray' => '778899', 'lightslategrey' => '778899', 'lightsteelblue' => 'b0c4de', 'lightyellow' => 'ffffe0', 'lime' => '00ff00', 'limegreen' => '32cd32', 'linen' => 'faf0e6', 'magenta' => 'ff00ff', 'maroon' => '800000', 'mediumaquamarine' => '66cdaa', 'mediumblue' => '0000cd', 'mediumorchid' => 'ba55d3', 'mediumpurple' => '9370d8', 'mediumseagreen' => '3cb371', 'mediumslateblue' => '7b68ee', 'mediumspringgreen' => '00fa9a', 'mediumturquoise' => '48d1cc', 'mediumvioletred' => 'c71585', 'midnightblue' => '191970', 'mintcream' => 'f5fffa', 'mistyrose' => 'ffe4e1', 'moccasin' => 'ffe4b5', 'navajowhite' => 'ffdead', 'navy' => '000080', 'oldlace' => 'fdf5e6', 'olive' => '808000', 'olivedrab' => '6b8e23', 'orange' => 'ffa500', 'orangered' => 'ff4500', 'orchid' => 'da70d6', 'palegoldenrod' => 'eee8aa', 'palegreen' => '98fb98', 'paleturquoise' => 'afeeee', 'palevioletred' => 'd87093', 'papayawhip' => 'ffefd5', 'peachpuff' => 'ffdab9', 'peru' => 'cd853f', 'pink' => 'ffc0cb', 'plum' => 'dda0dd', 'powderblue' => 'b0e0e6', 'purple' => '800080', 'red' => 'ff0000', 'rosybrown' => 'bc8f8f', 'royalblue' => '4169e1', 'saddlebrown' => '8b4513', 'salmon' => 'fa8072', 'sandybrown' => 'f4a460', 'seagreen' => '2e8b57', 'seashell' => 'fff5ee', 'sienna' => 'a0522d', 'silver' => 'c0c0c0', 'skyblue' => '87ceeb', 'slateblue' => '6a5acd', 'slategray' => '708090', 'slategrey' => '708090', 'snow' => 'fffafa', 'springgreen' => '00ff7f', 'steelblue' => '4682b4', 'tan' => 'd2b48c', 'teal' => '008080', 'thistle' => 'd8bfd8', 'tomato' => 'ff6347', 'turquoise' => '40e0d0', 'violet' => 'ee82ee', 'wheat' => 'f5deb3', 'white' => 'ffffff', 'whitesmoke' => 'f5f5f5', 'yellow' => 'ffff00', 'yellowgreen' => '9acd32');

    }
}


if (!function_exists('dateConvertFormtoDB')) {
    function dateConvertFormtoDB($date)
    {
        if (!empty($date)) {
            return date("Y-m-d", strtotime(str_replace('/', '-', $date)));
        }
    }
}

if (!function_exists('valDash')) {
    function valDash($val)
    {
        return (empty($val) || $val == 0) ? '--' : $val;
    }
}

if (!function_exists('setQueryLog')) {
    function setQueryLog()
    {
        DB::connection()->enableQueryLog();
    }
}

if (!function_exists('isFBA')) {
    function isFBA($channel_strings)
    {
        if(isset($channel_strings)) {
            if(strpos(strtolower($channel_strings), 'amazon') !== false) {
                return true;
            } else {
                return false;
            }

        } else {

            return null;
        }
    }
}

if (!function_exists('getQueryLog')) {
    function getQueryLog()
    {
        return DB::getQueryLog();
    }
}

if (!function_exists('centerme')) {
    function centerme()
    {
        return ' style=text-align:center';
    }
}

if (!function_exists('leftme')) {
    function leftme()
    {
        return ' style=text-align:left';
    }
}

if (!function_exists('rightme')) {
    function rightme()
    {
        return ' style=text-align:right';
    }
}



if (!function_exists('get_month_name')) {
    function get_month_name($num)
    {
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        if($num < 1) {
            $num = 1;
        }

        if($num > 12) {
            $num = 12;
        }

        return $months[$num - 1];

    }
}


if (!function_exists('get_last_year')) {
    function get_last_year($format = 'Y-m-d')
    {
        $currentDate = new DateTime(date($format));
        return $currentDate->modify('-1 year')->format($format);
    }
}


if (!function_exists('get_yesterday')) {
    function get_yesterday($format = 'Y-m-d')
    {
        $yesterday = new DateTime('yesterday');
        return $yesterday->format($format);
    }
}




if (!function_exists('validateDate')) {
    function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}


if (!function_exists('formatAUDate')) {
    function formatAUDate($date, $inputFormat, $outputFormat)
    {
        $test = new DateTimeZone('Australia/Sydney');
        $gmt = new DateTimeZone('AEDT');

        $dt =  DateTime::createFromFormat($inputFormat, $date, $test);
        // $date->setTimezone($gmt);
        return $dt->format($outputFormat);
    }
}


if (!function_exists('formatDate')) {
    function formatDate($date, $inputFormat, $outputFormat)
    {

        if ($date instanceof DateTime) {

            return $date->format($outputFormat);

        }

        // dd($date);
        if (strpos($inputFormat, '-') !== false) {
            $date = str_replace('/', '-', $date);
        }

        if (strpos($inputFormat, '/') !== false) {
            $date = str_replace('-', '/', $date);
        }


        try {
            $dt = DateTime::createFromFormat($inputFormat, $date);
            if ($dt instanceof DateTime) {

                return $dt->format($outputFormat);

            } else {
                return  date($outputFormat, strtotime($date));
            }
        } catch (Exception $e) {
            return  date($outputFormat, strtotime($date));
        }

        // dd('.');

    }
}


if (!function_exists('stringToDate')) {
    function stringToDate($date, $inputFormat, $outputFormat)
    {

        if ($date instanceof DateTime) {

            return DateTime::createFromFormat($inputFormat, $date->format($outputFormat));

        }

        // dd($date);
        if (strpos($inputFormat, '-') !== false) {
            $date = str_replace('/', '-', $date);
        }

        if (strpos($inputFormat, '/') !== false) {
            $date = str_replace('-', '/', $date);
        }


        try {
            $dt = DateTime::createFromFormat($inputFormat, $date);
            if ($dt instanceof DateTime) {

                return $dt;

            } else {
                return  date($outputFormat, strtotime($date));
            }
        } catch (Exception $e) {
            return  date($outputFormat, strtotime($date));
        }

        // dd('.');

    }
}



if (!function_exists('dateConvertDBtoForm')) {
    function dateConvertDBtoForm($date)
    {
        if (!empty($date)) {
            $date = strtotime($date);
            return date('d/m/Y', $date);
        }
    }
}
