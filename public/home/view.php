<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/users/_data.php";

$projects = getProjectList([ "is_deleted" => "false", ]);
$changes = getUserList([ "order" => "changes",]);

?>

<section class="home-blurb">
<section-header>E621 Tagging Project Assistant</section-header>
<p>TagMe! is a comprehensive utility, designed to make resolving large-scale tagging projects quick and easy.</p>
<p>To begin, <a href="/auth/login">log in</a> using your e621 username and API key, which you can find on your <a href="https://e621.net/users/home" target="_blank">user page</a>. Then, select one of the latest projects below, or look for the one you need via the search bar above. When resolving a project, follow the <a href="https://e621.net/help/tagging_checklist" target="_blank">tagging guidelines</a> to the best of your ability. If you are unsure of which option to pick, ask the question in <a href="https://e621.net/static/discord" target="_blank">Discord</a>, or simply skip it and move on to the next image.</p>
<p>Do not forget that any changes you make are done in your name, and are thus your responsibility. Vandalism will not be tolerated.</p>
</section>

<section class="home-display">

    <section-header>Latest Projects</section-header>
    <section-header>Top Contributors</section-header>

    <section class="home-project-list">
    <?php foreach($projects["data"] as $entry) { ?>

        <div class="home-project-name"><a href="/projects/<?php outprint($entry["meta"]); ?>"><?php outprint($entry["name"]); ?></a></div>
        <div class="home-project-desc" title="<?php outprint(formatProjectText($entry)); ?>"><?php outprint($entry["desc"]); ?></div>

    <?php } ?>
    </section>

    <section class="home-change-list">
    <?php foreach($changes["data"] as $entry) { ?>

        <div class="home-change-name"><a href="/users/<?php outprint($entry["user_id"]); ?>"><?php outprint($entry["username"]); ?></a></div>
        <div class="home-change-desc"><?php outprint($entry["changes"]); ?></div>

    <?php } ?>
    </section>

</section>

<?php

return [];

function formatProjectText($project) {
    return $project["name"] . " (" . $project["meta"] . ")\n"
        . $project["desc"] . "\n"
        . "[" . implode(" ", $project["tags"]) . "]";
}

?>
