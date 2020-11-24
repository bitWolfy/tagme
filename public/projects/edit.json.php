<?php

use TagMe\PageRouter;
use TagMe\Database;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

header ('Content-Type: application/json');
$response = [
    "query" => null,
    "success" => false,
    "data" => null,
    "error" => null,
];

$projectID = PageRouter :: getVars("project_id");
$projectData = getProjectByID($projectID);

// Check that the post exists
if($projectData["count"] == 0) {
    $response["error"] = "error.notfound";
    echo json_encode ($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return;
}


// Check that the user has permissions to edit the post
if(!User :: rankMatches(UserRank :: JANITOR) && User :: getUserID() != $projectData["data"]["user"]) {
    require_once ROOT . "/static/error/403.html.php";
    return;
}


// Fill in the $_POST data
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
$response["query"] = $_POST;


// Preliminary validation
if (
    (!isset($_POST["name"]) || !preg_match("/^[\d\w_:!@#&*()_\-+ ]{3,64}$/", $_POST["name"])) ||
    // (!isset($_POST["meta"]) || !preg_match("/^[\d\w_]{3,16}$/", $_POST["meta"])) ||
    (!isset($_POST["desc"]) || !preg_match("/^.{3,255}$/s", $_POST["desc"])) ||
    (!isset($_POST["text"]) || !preg_match("/^.{3,10000}$/s", $_POST["text"])) ||
    (!isset($_POST["tags"]) || !is_array($_POST["tags"])) ||
    (!isset($_POST["optmode"]) || !preg_match("/^(0|1)$/", $_POST["optmode"])) ||
    (!isset($_POST["options"]) || !is_array($_POST["options"])) || 
    (!isset($_POST["contags"]) || !is_array($_POST["contags"])) ||
    (!isset($_POST["private"]) || !preg_match("/^(0|1)$/", $_POST["private"]))
) {
    $response["error"] = "error.format";
    echo json_encode ($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return;
}


// Clean the input data
$tags = [];
foreach($_POST["tags"] as $entry)
    $tags[] = strip_tags($entry);

$options = [];
foreach($_POST["options"] as $entry) {
    $options[] = [
        "name" => $entry["name"],
        "tadd" => stripArrTags($entry["tadd"]),
        "trem" => stripArrTags($entry["trem"]),
    ];
}

// Push data to the database
$db = Database :: connect();
$db -> update(
    "project",
    [
        "name" => strip_tags($_POST["name"]),
        "desc" => strip_tags($_POST["desc"]),
        "text" => strip_tags($_POST["text"]),
        "tags[JSON]" => stripArrTags($_POST["tags"]),
        "optmode" => $_POST["optmode"] == "1" ? 1 : 0,
        "options[JSON]" => $options,
        "contags[JSON]" => stripArrTags($_POST["contags"]),
        "is_private" => $_POST["private"] == "1" ? 1 : 0,
    ],
    [ "meta" => $projectID ]);

$response["data"] = $projectID;
$response["success"] = true;
echo json_encode ($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


/** Strips tags from the array contents */
function stripArrTags($input) {
    if(!is_array($input)) return [];

    $output = [];
    foreach($input as $entry) {
        if(!is_string($entry)) continue;
        $output[] = strip_tags($entry);
    }
    return $output;
}
?>
