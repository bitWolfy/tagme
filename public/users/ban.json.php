<?php

use TagMe\Pagerouter;
use TagMe\Database;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$user_id = PageRouter :: getVars("user_id");

// Prepare the response
header ('Content-Type: application/json');
$response = [
    "success" => false,
    "data" => null,
];

// FAILURE - Authentication missing
if(!User :: isLoggedIn() || !User :: rankMatches(UserRank :: JANITOR)) {
    $response["error"] = "auth";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

$unban = isset($_GET["unban"]) && $_GET["unban"] == "true";

$db = Database :: connect();
$db -> update(
    "user",
    [ "is_banned" => $unban ? 0 : 1, ],
    [ "user_id" => $user_id, ]
);

$response["success"] = true;
$response["data"] = $unban;
echo json_encode ($response, JSON_PRETTY_PRINT);
?>
