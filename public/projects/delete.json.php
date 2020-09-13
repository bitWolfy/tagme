<?php

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

require_once ROOT . "/public/projects/_data.php";

header ('Content-Type: application/json');
$response = [
    "success" => false,
    "data" => null,
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


$restore = isset($_GET["restore"]) && $_GET["restore"] == "true";

$response["success"] = true;
$response["data"] = deleteProject($projectID, $restore);
echo json_encode ($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
