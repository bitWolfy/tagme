<?php
require_once("_data.php");
header ("Content-Type: application/json");
echo json_encode(
    getErrorResponse("json", 403),
    JSON_PRETTY_PRINT
);
?>
