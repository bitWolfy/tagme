<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";
require_once ROOT . "/lib/parsedown-1.7.4/Parsedown.php";

use TagMe\PageRouter;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;
use GuzzleHttp\Client;
use Markdown\Parsedown;

$projectID = PageRouter :: getVars("project_id");
$postID = PageRouter :: getVars("post_id");
$projectData = getProjectByID($projectID);

// ABORT - Invalid Project
if($projectData["count"] == 0 || $projectData["data"]["is_deleted"]) {
?>

<section class="project-error">
    <h2>Error - Project Not Found</h2>
    <p>This project does not exist. It could have been deleted, or has never existed in the first place.</p>
    <p>Either way, there is nothing more to be done here. <a href="/">Return to the home page</a>.</p>
</section>
    
<?php
    return [ "title" => "Invalid Project - TagMe!", ];
}

$project = $projectData["data"];
if(is_null($postID)) $query = implode(" ", $project["tags"]) . " order:random -type:swf";
else $query = "id:" . $postID . " -type:swf";


$canResolve = User :: rankMatches(UserRank :: MEMBER);

// ABORT - User Permissions
// This is done here to set proper metatags for the link previews
if(!$canResolve && is_null($postID)) {
?>

<section class="project-error">
    <h2>Error: Access Denied</h2>
    <p>Only logged in members with over 100 manual edits are permitted to access this page.</p>
</section>
    
<?php
return [
    "title" => (is_null($postID) ? "Project: " : ("#" . $postID . " - ")) . outescape($project["name"]) . " - TagMe!",
    "descr" => outescape($project["desc"]),
    "image" => is_null($postID) ? null : SITE . "/image/" . $postID . ".jpeg",
    "xtype" => is_null($postID) ? "website" : "photo",
    "xcard" => !is_null($postID),
];
}

$Parsedown = new Parsedown();

?>


<section
    id="image-container"
    data-id="0"
    data-project="<?php echo $projectID; ?>"
    data-project-id="<?php echo $project["project_id"]; ?>"
    data-project-name="<?php outprint($project["name"]); ?>"
    data-project-contags="<?php outprint(implode(" ", $project["contags"])); ?>"
    data-query="<?php outprint($query); ?>"
    data-static="<?php echo is_null($postID) ? "false" : "true"; ?>"
    class="loading"
>
    <img id="source-image" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />
    <video id="source-video" loop="loop" controls="controls" class="display-none"></video>
    <div id="blacklist-container" data-hotkey="`"></div>
</section>
<section id="image-data">
    <span><a href="https://e621.net/posts/0" id="source-link" target="_blank" data-hotkey="p|*">#0</a></span>
    <span id="source-date">Loading</span>
    <span id="dnp-notice" style="display: none;">On <a href="https://e621.net/wiki_pages/avoid_posting">DNP</a> list</span>
    <span><a href="https://e621.net/post_versions?search[post_id]=0" id="source-history" target="_blank" data-hotkey="q|/">history</a></span>
</section>
<section id="title"><a href="/projects/<?php echo $project["meta"]; ?>"><?php echo $project["name"]; ?></a></section>
<section id="description"><?php echo $project["desc"]; ?></section>
<section id="actions-hint">
<?php
$optmode = $project["optmode"];
if($optmode == 0) {
?>
( select one )
<?php } else if($optmode == 1) { ?>
( select all that apply )
<?php } else { ?>
???
<?php } ?>
</section>

<?php
// Authorized to resolve
if($canResolve) {
?>

<section id="actions">

<?php
$actionIndex = 0;
foreach( $project["options"] as $action ) {
?>
    <action data-added="<?php echo implode(" ", $action["tadd"]); ?>" data-removed="<?php echo implode(" ", $action["trem"]); ?>">
<?php if($optmode == 0) { ?>
        <input type="radio" id="action-<?php echo $actionIndex; ?>" name="action-select">
<?php } else if($optmode == 1) { ?>
        <input type="checkbox" id="action-<?php echo $actionIndex; ?>">
<?php } else { ?>
        <input type="text" id="action-<?php echo $actionIndex; ?>">
<?php } ?>
        <label for="action-<?php echo $actionIndex; ?>" data-hotkey="<?php echo $actionIndex == 9 ? 0 : $actionIndex + 1; ?>">
            <?php echo $actionIndex == 9 ? 0 : $actionIndex + 1; ?>.
            <?php echo $action["name"]; ?>
        </label>
        <span class="taglist">
<?php echo formatChangedTags($action["tadd"], $action["trem"]); ?>
        </span>
    </action>
<?php
    $actionIndex++;
}
?>

</section>

<section id="resolve-error" class="display-none">
    The API has returned an error while processing the request.<br />
    Either you have exceeded the hourly post edit limit, authentication has failed, or e621's data center has burned down.
</section>

<section id="proceed">
    <button href="/projects/<?php echo $projectID; ?>/resolve/" class="loading-button" id="page-skip" data-hotkey="tab|+">Skip</button>
    <button href="/projects/<?php echo $projectID; ?>/resolve/" class="loading-button" id="page-submit" data-hotkey="enter">Submit</button>
</section>

<?php
} else {
// Unauthorized
?>

<section id="proceed-unauthorized">
    <button href="/projects/<?php echo $projectID; ?>/resolve/" class="loading-button" id="page-skip" data-hotkey="tab">Skip</button>
</section>
<?php } ?>

<section id="guidelines" class="markdown"><?php echo $Parsedown -> text($project["text"]); ?></section>

<?php if($canResolve) { ?>
<section id="tags">
    <span class="tags-title">Old Tags</span>
    <span class="tags-title">New Tags</span>
    <textarea id="tags-old" readonly></textarea>
    <textarea id="tags-new"></textarea>
</section>
<?php } ?>

<?php

function formatChangedTags($addedTags, $removedTags) {
    $response = "";
    foreach($addedTags as $tag) $response .= "<a href=\"https://e621.net/wiki_pages/show_or_new?title=" . $tag . "\" target=\"_blank\">" . $tag . "</a><br />";
    foreach($removedTags as $tag) $response .= "-<a href=\"https://e621.net/wiki_pages/show_or_new?title=" . $tag . "\" target=\"_blank\">" . $tag . "</a><br />";
    return $response;
}

function getTagString($tags) {
    $allTags = array_merge($tags -> artist, $tags -> character, $tags -> copyright, $tags -> general, $tags -> invalid, $tags -> lore, $tags -> meta, $tags -> species);
    return implode(" ", $allTags) . " ";
}

return [
    "title" => (is_null($postID) ? "Project: " : ("#" . $postID . " - ")) . outescape($project["name"]) . " - TagMe!",
    "descr" => outescape($project["desc"]),
    "image" => is_null($postID) ? null : SITE . "/image/" . $postID . ".jpeg",
    "xtype" => is_null($postID) ? "website" : "photo",
    "xcard" => !is_null($postID),
];

?>
