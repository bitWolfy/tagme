<?php

require_once ROOT . "/public/projects/_data.php";

header ("Content-Type: application/json");
echo json_encode(
    getProjectByID(\TagMe\PageRouter :: getVars("project_id")),
    JSON_PRETTY_PRINT
);

?>
