<h1 id="project-new-header">New Project</h1>
<?php

require_once ROOT . "/public/projects/_data.php";

use TagMe\PageRouter;
use TagMe\Auth\User;

$projectID = PageRouter :: getVars("project_id");
if(!is_null($projectID)) {
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

    $edit = [
        "action" => "/projects/new.json",
        "name" => $project["name"] . " (" . User :: getUsername() . ")",
        "meta" => $project["meta"] . "_" . User :: getURLUsername(),
        "hide_meta" => false,
        "desc" => $project["desc"],
        "text" => $project["text"],
        "tags" => $project["tags"],
        "optmode" => $project["optmode"],
        "options" => $project["options"],
        "contags" => $project["contags"],
        "is_private" => $project["is_private"],
    ];
} else {
    $edit = [
        "action" => "/projects/new.json",
    ];
}

include ROOT . "/public/util_common/edit.partial.php";

return [ "title" => "New Project - TagMe!" ];
?>
