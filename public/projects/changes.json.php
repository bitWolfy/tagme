<?php

require_once ROOT . "/public/changes/_data.php";

header ("Content-Type: application/json");
echo json_encode(
    getProjectChanges(\TagMe\PageRouter :: getVars("project_id")),
    JSON_PRETTY_PRINT
);

?>
