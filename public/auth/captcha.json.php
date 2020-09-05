<?php

require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as RequestException;

header ('Content-Type: application/json');
$response = [
    "success" => false,
];

// Fill in the $_POST data
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
$response["query"] = $_POST;

// Missing input, aborting
if (!isset($response["query"]["captcha"]) || is_null($response["query"]["captcha"])) {
    $response["error"] = "error.input";
    echo json_encode ($response, JSON_PRETTY_PRINT);
}

try {
    $client = new Client([
        "base_uri" => "https://google.com/recaptcha/api/",
        "timeout"  => 2.0,
    ]);
    
    $serverResponse = $client -> request("POST", "siteverify", [
        "headers" => [
            "User-Agent" => \TagMe\Configuration :: $agent_userfixr,
            "Accept"     => "application/json",
        ],
        "form_params" => [
            "secret" => \TagMe\Configuration :: $recaptcha_secret,
            "response" => $response["query"]["captcha"],
        ]
    ]);
} catch (RequestException $exception) {
    // Request returned an http error code
    $response["error"] = "error.request";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
} catch(Exception $error) {
    $response["error"] = "error.connection";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}
$data = json_decode($serverResponse -> getBody() -> getContents());
$response["success"] = $data -> success;
$response["error"] = isset($data -> {"error-codes"}) ? $data -> {"error-codes"} : null;

echo json_encode ($response, JSON_PRETTY_PRINT);
?>
