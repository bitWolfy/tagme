<?php

namespace TagMe;

class Version {

    private static $instance;

    private $version = "0.0.0";
    private $build = 0;
    
    private function __construct() {
        $versionJSON = json_decode(file_get_contents(ROOT . "/config/build.json"), true);
        
        if(isset($versionJSON["version"])) $this -> version = $versionJSON["version"];
        if(isset($versionJSON["build"])) $this -> build = intval($versionJSON["build"]);
    }

    private static function getInstance() {
        if(!isset(self :: $instance)) self :: $instance = new Version();
        return self :: $instance;
    }

    public static function getVersion() { return self :: getInstance() -> version; }
    public static function getBuild() { return self :: getInstance() -> build; }

}

?>
