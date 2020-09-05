<?php

require_once ROOT . "/public/betelgeuse/_data.php";

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

// Fill in the $_POST data
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
$response["query"] = $_POST;

// FAILURE - Missing post data
if( (!isset($_POST["project_id"]) || is_null($_POST["project_id"]) || !is_numeric($_POST["project_id"])) ||
    (!isset($_POST["post_id"]) || is_null($_POST["post_id"]) || !is_numeric($_POST["post_id"])) ||
    (!isset($_POST["old_tags"]) || is_null($_POST["old_tags"]) || !is_string($_POST["old_tags"])) ||
    (!isset($_POST["new_tags"]) || is_null($_POST["new_tags"]) || !is_string($_POST["new_tags"])) ) {
    $response["error"] = "input.query";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


$strikes = summon($_POST["project_id"], $_POST["post_id"], $_POST["old_tags"], $_POST["new_tags"]);

$response["success"] = true;
$response["data"] = $strikes;
echo json_encode ($response, JSON_PRETTY_PRINT);
?>
