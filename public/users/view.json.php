<?php

require_once ROOT . "/public/users/_data.php";

header ("Content-Type: application/json");
echo json_encode(
    getUserByID(\TagMe\PageRouter :: getVars("user_id"), true),
    JSON_PRETTY_PRINT
);

?>
