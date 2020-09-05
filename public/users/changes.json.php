<?php

require_once ROOT . "/public/changes/_data.php";

header ("Content-Type: application/json");
echo json_encode(
    getUserChanges(\TagMe\PageRouter :: getVars("user_id")),
    JSON_PRETTY_PRINT
);

?>
