<h1 id="project-new-header">New Project</h1>
<?php
$edit = [
    "action" => "/projects/new.json",
];

include ROOT . "/public/util_common/edit.partial.php";

return [ "title" => "New Project - TagMe!" ];
?>
