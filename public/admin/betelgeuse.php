<?php

use TagMe\Configuration;
use Tagme\Database;
use TagMe\Auth\User;

// Fill in the $_POST data
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

// Prepare the response
header ('Content-Type: application/json');
$response = [
    "success" => false,
    "data" => null,
];

// FAILURE - Authentication missing
if(!User :: isLoggedIn()) {
    $response["error"] = "auth";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

// FAILURE - Missing post data
if(!isset($_POST["summoner"]) || intval($_POST["summoner"]) != User :: getUserID()) {
    $response["error"] = "input.query";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

$set_banned = (User :: getStrikes() + 1) >= Configuration :: $user_max_strikes;

$db = Database :: connect();
$db -> update(
    "user",
    [
        "strikes[+]" => 1,
        "is_banned" => (User :: isBanned() || $set_banned) ? 1 : 0,
    ],
    [
        "user_id" => User :: getUserID(),
    ]
);

$response["success"] = true;
$response["data"] = User :: getStrikes() + 1;
echo json_encode ($response, JSON_PRETTY_PRINT);
?>
