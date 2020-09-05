<?php

require_once ROOT . "/public/projects/_data.php";

header ("Content-Type: application/json");
echo json_encode (getProjectList($_GET), JSON_PRETTY_PRINT);

?>
