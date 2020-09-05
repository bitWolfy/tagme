<?php

use TagMe\Auth\Session;
use TagMe\Auth\User;

header ('Content-Type: application/json');
$response = [
    "query" => null,
    "success" => false,
    "data" => null,
];

// Fill in the $_POST data
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
$response["query"] = $_POST;

if ((isset($response["query"]["username"]) && !is_null($response["query"]["username"])) &&
    (isset($response["query"]["password"]) && !is_null($response["query"]["password"]))) {

    $remember = isset($response["query"]["remember"]) ? filter_var($response["query"]["remember"], FILTER_VALIDATE_BOOLEAN) : false;

    // Check in with the esix server
    $serverResponse = Session :: create($response["query"]["username"], $response["query"]["password"], $remember);
    $response["success"] = $serverResponse["success"];
    $response["data"] = $serverResponse["data"];

}

echo json_encode ($response, JSON_PRETTY_PRINT);
?>
