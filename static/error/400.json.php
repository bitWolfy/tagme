<?php
require_once("_data.php");
header ("Content-Type: application/json");
echo json_encode(
    getErrorResponse("json", 400),
    JSON_PRETTY_PRINT
);
?>
