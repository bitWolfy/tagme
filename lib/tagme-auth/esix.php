<?php

namespace TagMe\Auth;

require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException as ClientException;

class ESix {

    public static function authenticate($username, $api_key) {

        $response = [
            "success" => false,
            "user_id" => 0,
            "updates" => 0,
        ];

        try {
            $client = new Client([
                "base_uri" => "https://e621.net/",
                "timeout"  => 2.0,
            ]);
            $apiresponse = $client -> request("GET", "users.json?search[name_matches]=" . $username . "&limit=1", [
                "headers" => [
                    "User-Agent" => \TagMe\Configuration :: $agent_userfixr,
                    "Accept"     => "application/json",
                ],
                "auth" => [ $username, $api_key ]
            ]);
        } catch (ClientException $exception) {
            return $response;
        } catch(Exception $error) {
            // FAILURE - Probably failed authentication
            return $response;
        }
        
        $data = json_decode($apiresponse -> getBody() -> getContents());
        
        // User has either been found or not
        if(isset($data[0])) {
            $response = [
                "success" => true,
                "user_id" => $data[0] -> id,
                "updates" => $data[0] -> post_update_count,
            ];
        }

        return $response;
    }

}

?>
