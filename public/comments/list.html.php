<?php

require_once ROOT . "/public/projects/_data.php";
require_once ROOT . "/public/comments/_data.php";
require_once ROOT . "/lib/parsedown-1.7.4/Parsedown.php";

use TagMe\Configuration;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;
use Markdown\Parsedown;


$lookup = getCommentList($_GET);

$count = $lookup["count"];
$result = $lookup["data"];

$Parsedown = new Parsedown();
$Parsedown -> setSafeMode(true);


// Pagination
$currentPage = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? $_GET["page"] : 1;
$maxPage = ceil($count / Configuration :: $page_length);

?>

<section>
    <section-header><?php echo isset($_GET["search"]) ? "Search" : "All Comments"; ?></section-header>
</section>
<section id="comment-list">
<?php if(count($result) == 0) { ?>
    <span class="no-results">No Results Found</span>
<?php } else { ?>
    <?php
    $norespond = true;
    foreach($result as $comment) {
    ?>
        <a href="/projects/<?php echo $comment["meta"]; ?>" class="comment-project-link"><?php echo $comment["name"]; ?></a>
    <?php
        include ROOT . "/public/util_common/comment.php";
    }
    ?>
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
return [ "title" => (isset($_GET["search"]) ? "Comment Search" : "Comment Index") . " - TagMe!" ]; ?>
