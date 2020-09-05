<?php

require_once ROOT . "/public/comments/_data.php";

header ("Content-Type: application/json");
echo json_encode(
    getCommentByID(\TagMe\PageRouter :: getVars("comment_id")),
    JSON_PRETTY_PRINT
);

?>
