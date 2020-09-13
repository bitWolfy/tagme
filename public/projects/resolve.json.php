<?php

require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";
require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/changes/_data.php";

use GuzzleHttp\Client;
use TagMe\Configuration;
use TagMe\PageRouter;
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
if(!User :: rankMatches(UserRank :: MEMBER)) {
    $response["error"] = "auth";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}


// FAILURE - Missing post data
if( (!isset($_POST["tags"]) || is_null($_POST["tags"]) || !is_string($_POST["tags"])) ||
    (!isset($_POST["postID"]) || is_null($_POST["postID"]) || !is_numeric($_POST["postID"])) ) {
    $response["error"] = "input.query";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

$projectID = PageRouter :: getVars("project_id");
$projectData = getProjectByID($projectID);
$tags = $_POST["tags"];
$postID = $_POST["postID"];

// FAILURE - Invalid Project
if($projectData["count"] == 0) {
    $response["success"] = false;
    $response["error"] = "project";
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

// Send the POST request to the API
$client = new Client([
    "base_uri" => "https://e621.net/",
    "timeout"  => 2.0,
]);
try {
    $apiresponse = $client -> request("PUT", "posts/" . $postID . ".json", [
        "headers" => [
            "User-Agent"    => Configuration :: $agent_resolver,
            "Content-Type"  => "application/x-www-form-urlencoded",
        ],
        "auth" => [ User :: getUsername(), User :: getAPIKey() ],
        "form_params" => [
            "post[tag_string]" => $tags,
            "post[edit_reason]" => "TagMe! Utility Edit tagme.dev/p/" . $projectData["data"]["project_id"] . "/" . $projectData["data"]["meta"],
        ]
    ]);
} catch (RequestException $exception) {
    // Request returned an http error code
    $response["error"] = "error.request";
    $response["trace"] = $error -> getTrace();
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
} catch(Exception $error) {
    // Likely failed authentication
    $response["error"] = "error.connection";
    $response["trace"] = $error -> getTrace();
    echo json_encode ($response, JSON_PRETTY_PRINT);
    return;
}

$data = json_decode($apiresponse -> getBody() -> getContents());
$response = [
    "success" => true,
    "data" => $data -> post -> id,
];

// Increment the user's changes count
commitProjectChange($projectData["data"]["project_id"], User :: getUserID(), $postID);

// Return response
echo json_encode ($response, JSON_PRETTY_PRINT);

?>
