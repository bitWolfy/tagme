<?php

require_once ROOT . "/public/changes/_data.php";

header ("Content-Type: application/json");
echo json_encode (getChangesList($_GET), JSON_PRETTY_PRINT);

?>
