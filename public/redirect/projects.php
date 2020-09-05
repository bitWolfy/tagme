<?php

use TagMe\PageRouter;

require_once ROOT . "/public/projects/_data.php";

$lookup = getProjectList([ "project_id" => PageRouter :: getVars("project_id") ]);
if($lookup["count"] == 0)
    header("Location: /404" . ((PageRouter :: getOutputFormat() == "json") ? ".json" : ""));
else header("Location: /projects/" . $lookup["data"][0]["meta"] . ((PageRouter :: getOutputFormat() == "json") ? ".json" : ""));

?>
