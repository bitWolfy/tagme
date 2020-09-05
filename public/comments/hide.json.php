<?php

require_once ROOT . "/public/comments/_data.php";

use TagMe\Database;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

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
    $response["error"] = "login";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


// FAILURE - Missing post data
if(!isset($_POST["action_hide"]) || is_null($_POST["action_hide"])) {
    $response["error"] = "input.query";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


$comment_id = \TagMe\PageRouter :: getVars("comment_id");
$is_hidden = $_POST["action_hide"] == "true" ? 1 : 0;
$user_id = User :: getUserID();


// FAILURE - Comment does not exist
$lookup = getCommentByID($comment_id);
if($lookup["count"] == 0) {
    $response["error"] = "lookup";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


// FAILURE - Permissions do not match
if($lookup["data"]["user_id"] != $user_id && !User :: rankMatches(UserRank :: JANITOR)) {
    $response["error"] = "auth";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


$db = Database :: connect();
$db -> update(
    "comment",
    [ "is_hidden" => $is_hidden ],
    [ "id" => $comment_id ]
);


$response["success"] = true;
$response["data"] = $comment_id;
echo json_encode ($response, JSON_PRETTY_PRINT);

?>
