<?php

require_once ROOT . "/public/betelgeuse/_data.php";

header ("Content-Type: application/json");
echo json_encode (getStrikeList($_GET), JSON_PRETTY_PRINT);

?>
