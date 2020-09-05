<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";
require_once ROOT . "/lib/parsedown-1.7.4/Parsedown.php";

use TagMe\PageRouter;
use TagMe\Auth\User;
use GuzzleHttp\Client;
use Markdown\Parsedown;

$projectID = PageRouter :: getVars("project_id");
$postID = PageRouter :: getVars("post_id");
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
    if(is_null($postID)) $query = implode("+", $project["tags"]) . "+order:random+-type:swf+-type:webm";
    else $query = "id:" . $postID . "+-type:swf+-type:webm";

    /*
    echo "<pre>";
    var_dump($project);
    echo "</pre>";
    */
    
$client = new Client([
    "base_uri" => "https://e621.net/",
    "timeout"  => 2.0,
]);

$Parsedown = new Parsedown();

?>


<section id="image-container" data-id="0" data-project="<?php echo $projectID; ?>" data-query="<?php outprint($query); ?>" class="loading">
    <img id="source-image" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="" />
</section>
<section id="image-data">
    <a href="https://e621.net/posts/0" id="source-link" target="_blank">#0</a> | 
    <span id="source-date">Loading</span> | 
    <a href="https://e621.net/post_versions?search[post_id]=0" id="source-history" target="_blank">history</a>
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
        <label for="action-<?php echo $actionIndex; ?>" data-hotkey="<?php echo $actionIndex == 9 ? 0 : $actionIndex + 1; ?>"><?php echo $action["name"]; ?></label>
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
    <button href="/projects/<?php echo $projectID; ?>/resolve/" class="loading-button" id="page-skip" data-hotkey="s">Skip</button>
    <button href="/projects/<?php echo $projectID; ?>/resolve/" class="loading-button" id="page-submit" data-hotkey="enter">Submit</button>
</section>

<section id="guidelines" class="markdown"><?php echo $Parsedown -> text($project["text"]); ?></section>

<section id="tags">
    <span class="tags-title">Old Tags</span>
    <span class="tags-title">New Tags</span>
    <textarea id="tags-old"></textarea>
    <textarea id="tags-new"></textarea>
</section>

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
    "descr" => outescape($project["desc"]) . " [" . outescape(implode(" ", $project["tags"]) . "]"),
];
?>
