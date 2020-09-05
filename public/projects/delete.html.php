<?php

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

require_once ROOT . "/public/projects/_data.php";

$projectID = PageRouter :: getVars("project_id");
$projectData = getProjectByID($projectID);


// Check that the post exists
if($projectData["count"] == 0) {
    require_once ROOT . "/static/error/404.html.php";
    return;
}


// Check that the user has permissions to edit the post
if(!User :: rankMatches(UserRank :: JANITOR) && User :: getUserID() != $projectData["data"]["user"]) {
    require_once ROOT . "/static/error/403.html.php";
    return;
}


$restore = isset($_GET["restore"]) && $_GET["restore"] == "true";

deleteProject($projectID, $restore);

header("Location: /projects/" . $projectData["data"]["meta"]);

?>


