<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/comments/_data.php";
require_once ROOT . "/lib/parsedown-1.7.4/Parsedown.php";

use TagMe\PageRouter;
use Markdown\Parsedown;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$projectID = PageRouter :: getVars("project_id");
$projectData = getProjectByID($projectID);
$commentData = getProjectComments($projectID);

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
$Parsedown = new Parsedown();
$Parsedown -> setSafeMode(true);
?>

<section
    class="page-title"
    data-id="<?php outprint($project["project_id"]); ?>"
    data-name="<?php outprint($project["name"]); ?>"
    data-meta="<?php outprint($project["meta"]); ?>"
    data-user="<?php outprint($project["user"]); ?>"
    data-desc="<?php outprint($project["desc"]); ?>"
    data-tags="<?php outprint(implode(" ", $project["tags"])); ?>"
    data-optmode="<?php outprint($project["optmode"]); ?>"
    data-deleted="<?php echo $project["is_deleted"] ? "true" : "false" ?>"
    data-changes="<?php outprint($project["changes"]); ?>"
>
    <section-header><?php outprint($project["name"]); ?></section-header>
    <div class="page-actions">
        <?php if(User :: rankMatches(UserRank :: MEMBER)) { ?>
            <a href="/projects/<?php outprint($projectID); ?>/resolve" class="action-resolve">Resolve</a>
        <?php } ?>

        <?php if(User :: rankMatches(UserRank :: JANITOR) || User :: getUserID() == $project["user"]) { ?>
            <a href="/projects/<?php outprint($projectID); ?>/edit" class="action-edit">Edit</a>
        <?php } ?>

        <?php if(User :: rankMatches(UserRank :: JANITOR)) { ?>
            <a href="/projects/<?php outprint($projectID); ?>/delete<?php echo $project["is_deleted"] ? "?restore=true" : ""; ?>" class="action-delete"><?php echo $project["is_deleted"] ? "Restore" : "Delete"; ?></a>
        <?php } ?>
    </div>
</section>
<section>
    <?php outprint($project["desc"]); ?>
</section>
<section id="project-info-tags">
    <?php
        $queryArr = [];
        foreach($project["tags"] as $tag) $queryArr[] = urlencode($tag);
    ?>
    <a href="https://e621.net/posts?tags=<?php outprint(implode("+", $queryArr)); ?>"><?php outprint(implode(" ", $project["tags"])); ?></a>
</section>
<section id="project-info-actions">
    <table>
        <tr>
            <th>Name</th>
            <th>Added Tags</th>
            <th>Removed Tags</th>
        </tr>
    <?php foreach($project["options"] as $option) { ?>
        <tr>
            <td><?php outprint($option["name"]); ?></td>
            <td><?php outprint(implode(" ", $option["tadd"])); ?></td>
            <td><?php outprint(implode(" ", $option["trem"])); ?></td>
        </tr>
    <?php } ?>
    </table>
</section>
<section class="markdown">
    <?php echo $Parsedown -> text($project["text"]); ?>
</section>

<?php if($commentData["count"] > 0) { ?>
<section id="comment-list">
    <section-header><?php echo $commentData["count"] . " " . ($commentData["count"] == 1 ? "Comment" : "Comments"); ?></section-header>
    <?php
    foreach($commentData["data"] as $comment) {
        include ROOT . "/public/util_common/comment.php";
    }
    ?>
</section>
<?php } ?>

<?php if(User :: isLoggedIn()) { ?>
<section id="comment-new">
    <section-header>New Comment</section-header>
    <form id="comment-new-form" data-project="<?php echo $project["project_id"]; ?>">
        <textarea name="content" id="comment-new-content" required pattern="^.{3,10000}$"></textarea>
        <button type="submit">Submit</button>
        <span class="comment-error"></span>
        <span class="comment-help"><a href="https://www.markdownguide.org/basic-syntax/">Markdown syntax</a> is supported.</span>
    </form>
</section>
<?php } ?>

<?php return [
    "title" => "Project: " . outescape($project["name"]) . " - TagMe!",
    "descr" => outescape($project["desc"]) . " [" . outescape(implode(", ", $project["tags"]) . "]"),
];
?>
