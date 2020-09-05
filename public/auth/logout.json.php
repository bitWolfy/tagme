<?php

use TagMe\Auth\Session;
use TagMe\Auth\User;

header ('Content-Type: application/json');
$response = [ "success" => User :: isLoggedIn() ];

Session :: clearSessionCookies();
User :: reset();

echo json_encode ($response, JSON_PRETTY_PRINT);
?>
