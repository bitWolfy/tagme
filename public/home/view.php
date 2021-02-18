<?php

require_once ROOT . "/public/changes/_data.php";
require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/users/_data.php";

use TagMe\Configuration;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$changes = getChangesList([]);
$users = getUserList([ "order" => "changes", ]);

$projectOpt = User :: rankMatches(UserRank :: JANITOR)
    ? []
    : $projectOpt = [ "is_deleted" => "false", "is_private" => "false" ];
$projects = getProjectList($projectOpt);
$projectsMaxPage = ceil($projects["count"] / Configuration :: $page_length);

$popular = getProjectList([ "is_deleted" => "false", "is_private" => "false", "order" => "changes", ]);
if(count($popular["data"]) > 5) $popular["data"] = array_slice($popular["data"], 0, 5);

?>

<section class="home-subheader">
    <section-header>E621 Tagging Project Assistant</section-subheader>
</section>

<section class="home-counters">
    <div class="home-counters-changes">
        <span><span id="counter-changes" class="counter" count="<?php echo $changes["total"]; ?>">0</span> Post Changes</span>
    </div>
    <div class="home-counters-users">
        <span>by <span id="counter-users" class="counter" count="<?php echo $users["count"]; ?>">0</span> Users</span>
    </div>
    <div class="home-counters-projects">
        <span>via <span id="counter-projects" class="counter" count="<?php echo $projects["count"]; ?>">">0</span> Projects</span>
    </div>
</section>


<section class="home-blurb">
<p>TagMe! is a comprehensive utility designed to make resolving large-scale tagging projects quick and easy.</p>
<p>To begin, <a href="/auth/login">log in</a> using your e621 username and API key, which you can find on your <a href="https://e621.net/users/home" target="_blank">user page</a>. Then, select one of the latest projects below, or look for the one you need via the search bar above. When resolving a project, follow the <a href="https://e621.net/help/tagging_checklist" target="_blank">tagging guidelines</a> to the best of your ability. If you are unsure of which option to pick, ask the question in <a href="https://e621.net/static/discord" target="_blank">Discord</a>, or simply skip it and move on to the next image.</p>
<p>Do not forget that any changes you make are done in your name, and are thus your responsibility. Vandalism will not be tolerated.</p>
</section>


<section class="home-group">
    <div class="home-popular">
        <section-header>Popular Projects</section-header>
        <table>
        <?php foreach($popular["data"] as $entry) { ?>
            <?php
                $classes = [];
                if($entry["is_deleted"]) $classes[] = "deleted";
                if($entry["is_private"]) $classes[] = "private";
            ?>
            <tr <?php if(count($classes) > 0) echo "class=\"" . implode(" ", $classes) . "\""; ?>>
                <td class="home-projects-title"><a href="/projects/<?php outprint($entry["meta"]); ?>"><?php outprint($entry["name"]); ?></a></td>
                <td class="home-projects-descr" title="<?php outprint(formatProjectText($entry)); ?>"><?php outprint($entry["desc"]); ?></td>
                <td class="home-changes-count" title="<?php outprint(formatProjectText($entry)); ?>"><?php outprint($entry["changes"]); ?></td>
            </tr>
        <?php } ?>
        </table>
    </div>
    <div class="home-projects">
        <section-header>Latest Projects</section-header>
        <table>
        <?php foreach($projects["data"] as $entry) { ?>
            <?php
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

        <section class="pagination">
        <?php
        if($projectsMaxPage > 1) {
            for($i = 1; $i <= $projectsMaxPage; $i++) {
                if($i == 1) {
                    echo "<span>" . $i . "</span>";
                } else {
                    echo "<a href=\"/projects?page=" . $i . "\">" . $i . "</a>";
                }
            }
        }
        ?>
        </section>
    </div>
    <div class="home-changes">
        <section-header>Top Contributors</section-header>
        <table>
        <?php foreach($users["data"] as $entry) { ?>
            <tr>
                <td class="home-changes-title"><a href="/users/<?php outprint($entry["user_id"]); ?>"><?php outprint($entry["username"]); ?></a></td>
                <td class="home-changes-count"><?php outprint($entry["changes"]); ?></td>
            </tr>
        <?php } ?>
        </table>
    </div>
</section>


<?php

return [];

function formatProjectText($project) {
    return $project["name"] . " (" . $project["meta"] . ")\n"
        . $project["desc"] . "\n"
        . "[" . implode(" ", $project["tags"]) . "]";
}

?>
