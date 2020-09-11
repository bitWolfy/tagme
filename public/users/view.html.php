<?php

require_once ROOT . "/public/users/_data.php";
require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/changes/_data.php";

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$user_id = PageRouter :: getVars("user_id");
$userData = getUserByID($user_id);

// ABORT - Invalid Project
if($userData["count"] == 0) {
?>

<section class="project-error">
    <h2>Error - User Not Found</h2>
    <p>There is no record of a user with this ID. Either they have never logged in to TagMe!, or never existed at all.</p>
    <p>Either way, there is nothing more to be done here. <a href="/">Return to the home page</a>.</p>
</section>
    
<?php
    return [
        "title" => "Invalid Project - TagMe!",
    ];
}

$projectData = getProjectList([ "user" => $user_id ]);
$changesData = getChangesList([ "user_id" => $user_id, "order" => "changes" ]);

?>

<section class="page-title">
    <section-header>
        <?php echo $userData["data"]["username"]; ?>
        <?php if($userData["data"]["is_banned"]) { ?>
            <section-label class="rank-banned">Banned</section-label>
        <?php } else { ?>
            <section-label class="rank-<?php echo strtolower($userData["data"]["rank_string"]); ?>"><?php echo $userData["data"]["rank_string"]; ?></section-label>
        <?php } ?>
    </section-header>
    <div class="page-actions">
        <a href="https://e621.net/users/<?php echo $user_id; ?>/" class="action-e621">e621</a>
        
        <?php if(User :: rankMatches(UserRank :: JANITOR)) { ?>
            <a href="/users/<?php echo $user_id; ?>/ban.json" class="action-ban" id="action-user-ban"><?php echo $userData["data"]["is_banned"] ? "Unban" : "Ban" ?></a>
        <?php } ?>
    </div>
</section>


<section class="home-display">

    <section-header>Projects: <?php echo $projectData["count"]; ?></section-header>
    <section-header>Contributions: <?php echo $changesData["total"]; ?></section-header>

    <section class="home-project-list">
    <?php foreach($projectData["data"] as $entry) { ?>

        <div class="home-project-name"><a href="/projects/<?php echo $entry["meta"]; ?>"><?php echo $entry["name"]; ?></a></div>
        <div class="home-project-desc" title="<?php outprint(formatProjectText($entry)); ?>"><?php echo $entry["desc"]; ?></div>

    <?php } ?>
    </section>

    <section class="home-change-list">
    <?php foreach($changesData["data"] as $entry) { ?>

        <div class="home-change-name"><a href="/projects/<?php echo $entry["meta"]; ?>"><?php echo $entry["name"]; ?></a></div>
        <div class="home-change-desc"><?php echo $entry["changes"]; ?></div>

    <?php } ?>
    </section>

</section>

<?php

return [ "title" => $userData["data"]["username"] . " - TagMe!" ];

function formatProjectText($project) {
    return $project["name"] . " (" . $project["meta"] . ")\n"
        . $project["desc"] . "\n"
        . "[" . implode(" ", $project["tags"]) . "]";
}

?>
