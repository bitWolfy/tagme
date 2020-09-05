<?php

require_once ROOT . "/public/projects/_data.php";

use TagMe\Configuration;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$lookup = getProjectList($_GET);

// TODO Make sure the lookup is valid and has results

$count = $lookup["count"];
$result = $lookup["data"];


// Pagination
$currentPage = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : 1;
$maxPage = ceil($count / Configuration :: $page_length);

?>

<section>
    <section-header><?php echo isset($_GET["search"]) ? "Search" : "All Projects"; ?></section-header>
</section>
<section class="project-list">
<?php if(count($result) == 0) { ?>
    <span class="no-results">No Results Found</span>
<?php } ?>
<?php foreach($result as $entry) { ?>

    <div><a href="/projects/<?php echo $entry["meta"]; ?>"><?php echo $entry["name"]; ?></a></div>
    <div><?php echo $entry["desc"]; ?></div>
    <div>
        <?php if(User :: rankMatches(UserRank :: PRIVILEGED)) { ?>
        <a href="/projects/<?php echo $entry["meta"]; ?>/resolve">Resolve</a>
        <?php } ?>
    </div>

<?php } ?>

</section>
<section class="pagination">
<?php
for($i = 1; $i <= $maxPage; $i++) {
    $newGet = $_GET;
    $newGet["page"] = $i;
    if($i == $currentPage) {
        echo "<span>" . $i . "</span>";
    } else {
        echo "<a href=\"/projects?" . http_build_query($newGet) . "\">" . $i . "</a>";
    }
}
?>
</section>

<?php
return [ "title" => (isset($_GET["search"]) ? "Search" : "All Projects") . " - TagMe!" ]; ?>
