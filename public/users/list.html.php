<?php

require_once ROOT . "/public/users/_data.php";

use TagMe\Configuration;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

$lookup = getUserList(array_merge($_GET, [ "changes" => true ]));

// TODO Make sure the lookup is valid and has results

$count = $lookup["count"];
$result = $lookup["data"];


// Pagination
$currentPage = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : 1;
$maxPage = ceil($count / Configuration :: $page_length);

?>

<section>
    <section-header><?php echo isset($_GET["search"]) ? "Search" : "Users"; ?></section-header>
</section>
<section class="user-list">
    <table>
    <tr>
        <th>Username</th>
        <th>Rank</th>
        <th>Projects</th>
        <th>Changes</th>
    </tr>
<?php foreach($result as $entry) { ?>
    <tr>
        <td><a href="/users/<?php echo $entry["user_id"]; ?>"><?php echo $entry["username"]; ?></a></td>
        <td><?php echo $entry["rank_string"]; ?></td>
        <td><?php echo $entry["projects"]; ?></td>
        <td><?php echo $entry["changes"]; ?></td>
    </tr>
<?php } ?>
    </table>

</section>
<section class="pagination">
<?php
for($i = 1; $i <= $maxPage; $i++) {
    $newGet = $_GET;
    $newGet["page"] = $i;
    if($i == $currentPage) {
        echo "<span>" . $i . "</span>";
    } else {
        echo "<a href=\"/users?" . http_build_query($newGet) . "\">" . $i . "</a>";
    }
}
?>
</section>

<?php return [ "title" => "Users - TagMe!" ]; ?>
