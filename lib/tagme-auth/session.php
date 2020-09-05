<?php

namespace TagMe\Auth;

use TagMe\Configuration;
use TagMe\Database;

require_once ROOT . "/public/users/_data.php";

class Session {

    /** Create a new session as a result of a login attempt */
    public static function create($username, $api_key, $remember) {

        if(!isset($remember) || !is_bool($remember)) $remember = false;

        $response = [
            "success" => false,
            "data" => null,
        ];
        
        // Validate the username-key pair through E621's API
        $esix = ESix :: authenticate($username, $api_key);
        if($esix["success"] == false) return $response;
        
        $rank = 0;
        if($esix["updates"] >= Configuration :: $user_create) $rank = 2;
        else if($esix["updates"] >= Configuration :: $user_resolve) $rank = 1;

        // Check for user ID
        $db = Database :: connect();
        $lookup = \getUserByID($esix["user_id"]);

        // If user record exists, update it. Otherwise, create it.
        if($lookup["count"] == 0) {
            $db -> insert("user", [
                "user_id" => $esix["user_id"],
                "username" => $username,
                "rank" => $rank,
            ]);
        } else {
            $db -> update("user", [
                "username" => $username,
                "rank" => ($rank > $lookup["data"]["rank"] ? $rank : $lookup["data"]["rank"]),
            ], [ "user_id" => $esix["user_id"] ]);
        }

        // Look up the updated data
        $lookup = getUserByID($esix["user_id"], true);
        $user_id = $lookup["data"]["user_id"];

        // Create the response
        $response["data"] = $lookup["data"];
        $response["success"] = true;

        // Create a user session
        User :: init($lookup["data"], $api_key);
        unset($lookup);

        // Create a token
        // If a token already exists, delete it
        $db -> delete("user_tokens", [ "user_id" => $user_id ]);
        do {
            $user_token = bin2hex(random_bytes(16));
            $lookup = $db -> select("user_tokens", "*", [ "token" => $user_token ]);
        } while(isset($lookup[0]));
        unset($lookup);
    
        $db -> insert("user_tokens", [
            "user_id" => $user_id,
            "token" => $user_token,
        ]);

        // Set the appropriate cookies
        self :: setSessionCookies(
            $user_token,
            $api_key,
            $remember,
            time() + (\TagMe\Configuration :: $user_refresh * 60 * 60),
        );

        return $response;
        
    }

    /** Attempt to restore a user session from the data stored in cookies */
    public static function restore() {

        // Check if appropriate cookies are set
        if(!isset($_COOKIE["api_key"]) || !$_COOKIE["user_token"]) return false;

        $user_token = $_COOKIE["user_token"];
        $api_key = $_COOKIE["api_key"];
        $next_update = isset($_COOKIE["next_update"]) ? $_COOKIE["next_update"] : 0;

        // Check if token exists
        $db = Database :: connect();
        $lookup = $db -> select("user_tokens", "*", [ "token" => $user_token ]);
        if(!isset($lookup[0])) {
            self :: clearSessionCookies();
            return false;
        }
    
        // Has userdata that matches token
        $userID = $lookup[0]["user_id"];
        $lookup = \getUserByID($userID, true);
        if(is_null($lookup)) {
            self :: clearSessionCookies();
            $db -> delete("tokens", [ "token" => $user_token ]);
            return false;
        }

        // Update the user's ranking if necessary
        if($next_update < time()) {
            $username = $lookup["data"]["username"];
            $esix = ESix :: authenticate($username, $api_key);
            if($esix["success"] == false) return false;

            $rank = 0;
            if($esix["updates"] >= Configuration :: $user_create) $rank = 2;
            else if($esix["updates"] >= Configuration :: $user_resolve) $rank = 1;
            
            $db -> update("user", [
                "username" => $username,
                "rank" => ($rank > $lookup["data"]["rank"] ? $rank : $lookup["data"]["rank"]),
            ], [ "user_id" => $esix["user_id"] ]);

            $next_update = time() + (Configuration :: $user_refresh * 60 * 60);
        }

        // Set the cookies, again
        self :: setSessionCookies(
            $user_token,
            $api_key,
            (isset($_COOKIE["remember_me"]) && $_COOKIE["remember_me"] == true),
            $next_update,
        );

        // Restore the user session
        User :: init($lookup["data"], $api_key);
    
        return $lookup["data"];
    }

    private static function setSessionCookies($user_token, $api_key, $remember, $next_update) {
        if($remember) {
            setcookie("user_token", $user_token, time() + (30 * 24 * 60 * 60), "/");
            setcookie("api_key", $api_key, time() + (30 * 24 * 60 * 60), "/");
            setcookie("remember_me", true, time() + (30 * 24 * 60 * 60), "/");
            setcookie("next_update", $next_update, time() + (Configuration :: $user_refresh * 60 * 60), "/");
        } else {
            setcookie("user_token", $user_token, 0, "/");
            setcookie("api_key", $api_key, 0, "/");
            setcookie("remember_me", false, 0, "/");
            setcookie("next_update", $next_update, 0, "/");
        }
    }

    /** Clears all session-related cookies */
    public static function clearSessionCookies() {
        if (isset($_COOKIE["user_token"])) {
            unset($_COOKIE["user_token"]);
            setcookie("user_token", null, -1, '/');
        }
        if(isset($_COOKIE["api_key"])) {
            unset($_COOKIE["api_key"]);
            setcookie("api_key", null, -1, '/');
        }
        if(isset($_COOKIE["remember_me"])) {
            unset($_COOKIE["remember_me"]);
            setcookie("remember_me", null, -1, '/');
        }
    }
}

?>
