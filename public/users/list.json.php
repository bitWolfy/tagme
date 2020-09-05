<?php

require_once ROOT . "/public/users/_data.php";

header ("Content-Type: application/json");
echo json_encode (getUserList($_GET), JSON_PRETTY_PRINT);

?>
