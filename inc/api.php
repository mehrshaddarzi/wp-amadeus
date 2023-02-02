<?php

namespace WP_AMADEUS;

use WP_AMADEUS\admin\Admin;

class WP_AMADEUS_REST
{

    public static $option_name = 'wp_amadeus';

    public static $option_name_token = 'wp_amadeus_token';

    public static $oauth2 = 'https://test.api.amadeus.com/v1/security/oauth2/token';

    public static $flight_offers = 'https://test.api.amadeus.com/v2/shopping/flight-offers';

    public function __construct()
    {
        add_action('init', function () {
            if (!isset($_GET['_amadeus'])) {
                return;
            }

            echo '<pre>';
            var_dump(WP_AMADEUS_REST::flight_offers());
            echo '<hr>';

            exit;
        });
    }

    public static function option($default = [])
    {
        return get_option(self::$option_name, $default);
    }

    public static function token()
    {
        // Check API Key and Secret
        $option = self::option();
        if (empty($option['api_key']) || empty($option['api_secret'])) {
            return ['status' => false, 'message' => 'Please fill API Key and Secret Option'];
        }

        // Check Token From option
        $token = get_option(self::$option_name_token, []);
        if (isset($token['access_token']) and !empty($token['access_token'])) {
            return ['status' => true, 'token' => $token['access_token']];
        }

        // Generate Request Body
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => $option['api_key'],
            'client_secret' => $option['api_secret'],
        ];

        // Get Token Request
        $request = wp_remote_request(
            self::$oauth2,
            [
                'httpversion' => '1.1',
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ],
                'method' => 'POST',
                'timeout' => 30,
                'sslverify' => false,
                'body' => build_query( $body )
            ]
        );

        // Check Connect Error
        if (is_wp_error($request)) {
            return array('status' => false, 'message' => $request->get_error_message());
        }

        // Get Body
        $body = wp_remote_retrieve_body($request);
        $json = json_decode($body, true);

        // Check Error
        if (isset($json['error_description']) and !empty($json['error_description'])) {
            return ['status' => false, 'message' => trim($json['error_description'])];
        }

        // Check Success
        if ($request['response']['code'] == "200" and isset($json['access_token'])) {
            // Save Token Option
            update_option(self::$option_name_token, $json, 'yes');

            // Return Token
            return ['status' => true, 'token' => $json['access_token']];
        }

        // Unknown
        return ['status' => false, 'message' => 'Invalid request response'];
    }

    public static function flight_offers($arg = [])
    {
        
        // Check Token
        $token = self::token();
        if ($token['status'] === false) {
            return $token;
        }

        // Prepare Default Params
        $default = [
            'originLocationCode' => 'SYD',
            'destinationLocationCode' => 'BKK',
            'departureDate' => '2023-05-02',
            'adults' => '1',
        ];
        $args = wp_parse_args($arg, $default);

        // Request
        $request = wp_remote_request(
            self::$flight_offers.'?'.build_query( $args ),
            [
                'httpversion' => '1.1',
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$token['token']
                ],
                'method' => 'GET',
                'timeout' => 30,
                'sslverify' => false
            ]
        );

        // Check Connect Error
        if (is_wp_error($request)) {
            return array('status' => false, 'message' => $request->get_error_message());
        }
        
        // Check 401 Refresh Token
        if (in_array($request['response']['code'], ["401", "403"])) {

            // remove current token
            update_option(self::$option_name_token, [], 'yes');
            
            // Generate Again Token
            $token = self::token();
            if ($token['status'] === false) {
                return $token;
            }

            return self::flight_offers($arg);
        }
        
        // Get Body
        $body = wp_remote_retrieve_body($request);
        $json = json_decode($body, true);

        // Check Error Message
        if(isset($json['errors']) and !empty($json['errors'][0]['title'])) {
            return ['status' => false, 'message' => $json['errors'][0]['title'].'<br />'.$json['errors'][0]['detail']];
        }

        // Return
        return [
            'status' => true,
            'data' => $json,
            'code' => $request['response']['code']
        ];
    }

    public static function log($message)
    {
        if (is_array($message)) {
            $message = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        $file = fopen(self::getLogFilePath(), "a");
        $date = Helper::eng_number(date_i18n('Y-m-d h:i:s', current_time('timestamp')));
        fwrite($file, "\n" . $date . " :: " . $message);
        fclose($file);
    }

}

new WP_AMADEUS_REST();