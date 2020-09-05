<?php

// Define common constants
define ("ROOT", $_SERVER['DOCUMENT_ROOT']);
define ("SITE", (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST']);

header('X-Frame-Options: Deny');
session_start();

// Import required files
require_once ROOT . "/config/settings.conf.php";
require_once ROOT . "/config/routes.conf.php";

require_once ROOT . "/lib/tagme/common.php";
require_once ROOT . "/lib/tagme/page_router.php";
require_once ROOT . "/lib/tagme/lib_loader.php";
require_once ROOT . "/lib/tagme/database.php";
require_once ROOT . "/lib/tagme/util.php";

require_once ROOT . "/lib/tagme-auth/session.php";
require_once ROOT . "/lib/tagme-auth/esix.php";
require_once ROOT . "/lib/tagme-auth/user.php";

use TagMe\PageRouter;
use TagMe\LibLoader;
use TagMe\Auth\Session;
use TagMe\Auth\User;

// Initialize
Session :: restore();

$output = PageRouter :: init(Routes :: PAGE_ROUTES);
if($output["json"]) return include ROOT . "/" . $output["page"];

$libraries = LibLoader :: load();

// Buffer output
ob_start();
?>
<html>

<head>
    <title><!-- %PAGETITLE% --></title>

    <link rel="icon" type="image/png" href="/static/images/favicon.ico"/>

<?php foreach ( $libraries ["css"] as $cssLibrary ) { ?>
    <link rel="stylesheet" href="<?php echo SITE . '/static/libraries/' . $cssLibrary; ?>" />
<?php } ?>

    <link rel="stylesheet" href="<?php echo SITE; ?>/static/assets/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Pacifico&display=swap" rel="stylesheet">

<?php foreach ( $libraries ["js"] as $jsLibrary ) { ?>
	<script src="<?php echo SITE . '/static/libraries/' . $jsLibrary; ?>"></script>
<?php } ?>

<?php if(User :: isLoggedIn()) { ?>
    <meta name="current-user-name" content="<?php echo User :: getUsername(); ?>">
    <meta name="current-user-id" content="<?php echo User :: getUserID(); ?>">
<?php } ?>
    <meta name="og:site_name" content="TagMe!">
    <meta name="og:title" content="<!-- %PAGETITLE% -->">
    <meta name="og:description" content="<!-- %PAGEDESCR% -->">
    <meta name="og:image" content="/static/images/sitelogo.png">

    <link rel="icon" type="image/x-icon" href="/static/images/favicon.ico"/>
    <link rel="icon" type="image/png" href="/static/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/static/images/favicon-16x16.png" sizes="16x16">

    <style type="text/css" id="background-style"></style>

</head>

<body>

<?php include_once ROOT . "/public/util_common/header.php"; ?>

<page-container>
<?php $pageData = include_once ROOT . "/" . $output["page"]; ?>
</page-container>

<?php include_once ROOT . "/public/util_common/footer.php"; ?>

<script src="/static/assets/script.js"></script>
</body>

</html>
<?php
$pageContents = ob_get_contents ();
ob_end_clean ();

// PageData not set
if(!isset($pageData) || $pageData == false || $pageData == 1) $pageData = [];
if(!isset($pageData["title"])) $pageData["title"] = "TagMe! - E621 Project Resolver";
if(!isset($pageData["descr"])) $pageData["descr"] = "Improve your tagging experience with TagMe! Resolve large tag projects with ease, at lightning-fast speed. Collaborate and compete while improving e621's image searchability.";

// Replace <!-- %PAGETITLE% --> with $pageTitle variable contents, and print the HTML
$replace = [
    "/<!-- %PAGETITLE% -->/" => $pageData["title"],
    "/<!-- %PAGEDESCR% -->/" => $pageData["descr"],
];
echo preg_replace (array_keys($replace), array_values($replace), $pageContents);

?>
