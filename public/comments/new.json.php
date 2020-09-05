<?php

use TagMe\Database;
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
if( (!isset($_POST["content"]) || is_null($_POST["content"]) || !is_string($_POST["content"])) ||
    (!isset($_POST["project_id"]) || is_null($_POST["project_id"]) || !is_numeric($_POST["project_id"])) ) {
    $response["error"] = "input.query";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

$content = $_POST["content"];
$project_id = $_POST["project_id"];
$user_id = User :: getUserID();


$db = Database :: connect();
$db -> insert(
    "comment",
    [
        "project_id" => $project_id,
        "user_id" => $user_id,
        "added_on" => time(),
        "edited_on" => time(),
        "content" => $content,
    ]
);

$response["success"] = true;
$response["data"] = $db -> id();
echo json_encode ($response, JSON_PRETTY_PRINT);

?>
