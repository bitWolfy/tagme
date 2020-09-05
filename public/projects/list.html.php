<?php

require_once ROOT . "/public/projects/_data.php";

use TagMe\Configuration;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

if(!isset($_GET["is_deleted"])) $_GET["is_deleted"] = "false";

$lookup = getProjectList($_GET);

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
<?php } else { ?>
    <table>
        <thead><tr>
            <th>Name</th>
            <th>Description</th>
            <th>Changes</th>
        </tr></thead>
        <tbody>
        <?php foreach($result as $entry) { ?>
            <tr class="project-row" data-deleted="<?php echo $entry["is_deleted"] ? "true" : "false"; ?>">
                <td><a href="/projects/<?php echo $entry["meta"]; ?>"><?php echo $entry["name"]; ?></a></td>
                <td><?php echo $entry["desc"]; ?></td>
                <td><?php echo $entry["changes"]; ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
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
