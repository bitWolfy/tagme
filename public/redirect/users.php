<?php

use TagMe\PageRouter;

require_once ROOT . "/public/users/_data.php";

$lookup = getUserList([ "user_id" => PageRouter :: getVars("user_id") ]);
if($lookup["count"] == 0)
    header("Location: /404" . ((PageRouter :: getOutputFormat() == "json") ? ".json" : ""));
else header("Location: /users/" . $lookup["data"][0]["user_id"] . ((PageRouter :: getOutputFormat() == "json") ? ".json" : ""));

?>
