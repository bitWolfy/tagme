<?php

require_once ROOT . "/public/users/_data.php";
require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/changes/_data.php";
require_once ROOT . "/public/comments/_data.php";
require_once ROOT . "/lib/parsedown-1.7.4/Parsedown.php";

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;
use Markdown\Parsedown;

$Parsedown = new Parsedown();
$Parsedown -> setSafeMode(true);

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
$commentData = getCommentList([ "user" => $user_id ]);

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


<section class="home-group">
    <div class="home-projects">
        <section-header>Projects: <?php echo $projectData["count"]; ?></section-header>
        <table>
        <?php foreach($projectData["data"] as $entry) { ?>
        <?php
                if($entry["is_deleted"] && (!User :: rankMatches(UserRank :: JANITOR) && User :: getUserID() != $entry["user"])) continue;
                
                $classes = [];
                if($entry["is_deleted"]) $classes[] = "deleted";
                if($entry["is_private"]) $classes[] = "private";
            ?>
            <tr <?php if(count($classes) > 0) echo "class=\"" . implode(" ", $classes) . "\""; ?>>
                <td class="home-projects-title"><a href="/projects/<?php outprint($entry["meta"]); ?>"><?php outprint($entry["name"]); ?></a></td>
                <td class="home-projects-descr" title="<?php outprint(formatProjectText($entry)); ?>"><?php outprint($entry["desc"]); ?></td>
            </tr>
        <?php } ?>
        </table>
    </div>
    <div class="home-changes">
        <section-header>Changes: <?php echo $changesData["total"]; ?></section-header>
        <table>
        <?php foreach($changesData["data"] as $entry) { ?>
            <tr>
                <td class="home-changes-title"><a href="/projects/<?php outprint($entry["meta"]); ?>"><?php outprint($entry["name"]); ?></a></td>
                <td class="home-changes-count"><?php echo $entry["changes"]; ?></td>
            </tr>
        <?php } ?>
        </table>
    </div>
</section>


<section id="comment-list">
    <section-header>Project Comments</section-header>
    <?php
    $norespond = true;
    foreach($commentData["data"] as $comment) {
    ?>
        <a href="/projects/<?php echo $comment["meta"]; ?>" class="comment-project-link"><?php echo $comment["name"]; ?></a>
    <?php
        include ROOT . "/public/util_common/comment.php";
    }
    ?>
</section>

<?php

return [ "title" => $userData["data"]["username"] . " - TagMe!" ];

function formatProjectText($project) {
    return $project["name"] . " (" . $project["meta"] . ")\n"
        . $project["desc"] . "\n"
        . "[" . implode(" ", $project["tags"]) . "]";
}

?>
