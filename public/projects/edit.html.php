<?php

require_once ROOT . "/public/projects/_data.php";

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$projectID = PageRouter :: getVars("project_id");
$projectData = getProjectByID($projectID);

// ABORT - Invalid Project
if($projectData["count"] == 0) {
?>

<section class="project-error">
    <h2>Error - Project Not Found</h2>
    <p>This project does not exist. It could have been deleted, or has never existed in the first place.</p>
    <p>Either way, there is nothing more to be done here. <a href="/">Return to the home page</a>.</p>
</section>
    
<?php
    return [
        "title" => "Invalid Project - TagMe!",
    ];
}

$project = $projectData["data"];


if(!User :: rankMatches(UserRank :: JANITOR) && User :: getUserID() != $project["user"]) {
    require_once ROOT . "/static/error/403.html.php";
    return;
}

/*
echo "<pre>";
var_dump($project);
echo "</pre>";
*/

$edit = [
    "action" => "/projects/" . $projectID . "/edit.json",
    "name" => $project["name"],
    "meta" => $project["meta"],
    "hide_meta" => true,
    "desc" => $project["desc"],
    "text" => $project["text"],
    "tags" => $project["tags"],
    "optmode" => $project["optmode"],
    "options" => $project["options"],
    "is_private" => $project["is_private"],
];

?>

<h1 id="project-new-header">Edit Project</h1>

<?php

include ROOT . "/public/util_common/edit.partial.php";

return [ "title" => "Edit - " . $project["name"] . " - TagMe!" ];
?>
