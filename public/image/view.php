<?php

require_once ROOT . "/lib/guzzlehttp_7.0.1.0/index.php";

use TagMe\PageRouter;
use TagMe\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException as ClientException;

$imageID = PageRouter :: getVars("image_id");

try {
    $client = new Client([
        "base_uri" => "https://e621.net/",
        "timeout"  => 2.0,
    ]);
    $apiresponse = $client -> request("GET", "posts.json?tags=id:" . $imageID, [
        "headers" => [
            "User-Agent" => \TagMe\Configuration :: $agent_resolver,
            "Accept"     => "application/json",
        ],
    ]);
} catch (ClientException $exception) {
    return $response;
} catch(Exception $error) {
    // FAILURE - Probably failed authentication
    return $response;
}

$data = json_decode($apiresponse -> getBody() -> getContents()) -> posts;

if(count($data) == 0) {
    $url = "https://e621.net/images/deleted-preview.png";
} else {
    $url = $data[0] -> sample -> url;
    if(is_null($url)) $url = "https://e621.net/images/deleted-preview.png";
}

header ('Content-Type: image/jpeg');
echo file_get_contents($url);

?>
