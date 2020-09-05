<?php
namespace TagMe\Auth;

class User {

    private static $logged_in = false;
    private static $user_id = null;
    private static $username = null;
    private static $api_key = null;

    private static $rank = UserRank :: VISITOR;
    private static $rank_string = "Visitor";
    private static $is_banned = false;
    private static $strikes = 0;

    private static $changes = 0;

    public static function init($userdata, $api_key) {
        User :: $logged_in = true;
        User :: $user_id = $userdata["user_id"];
        User :: $username = $userdata["username"];
        User :: $api_key = $api_key;

        User :: $rank = $userdata["rank"];
        User :: $rank_string = $userdata["rank_string"];
        User :: $is_banned = $userdata["is_banned"];
        User :: $strikes = $userdata["strikes"];
        
        User :: $changes = $userdata["changes"];
    }

    public static function reset() {
        User :: $logged_in = false;
        User :: $user_id = null;
        User :: $username = null;
        User :: $api_key = null;
        
        User :: $rank = UserRank :: VISITOR;
        User :: $rank_string = "Visitor";
        User :: $is_banned = false;
        User :: $strikes = 0;
        
        User :: $changes = 0;
    }
    
    public static function isLoggedIn() { return User :: $logged_in; }
    public static function getUserID() { return User :: $user_id; }
    public static function idMatches($user_id) { return User :: $user_id == $user_id; }
    public static function getUsername() { return User :: $username; }
    public static function getAPIKey() { return User :: $api_key; }
    
    public static function getRank() { return User :: $rank; }
    public static function getRankString() { return User :: $rank_string; }
    public static function rankMatches($rank) { return User :: $rank >= $rank; }
    public static function isBanned() { return User :: $is_banned; }
    public static function getStrikes() { return User :: $strikes; }

    public static function getChanges() { return User :: $changes; }

}

class UserRank {
    public const VISITOR = 0;
    public const MEMBER = 1;
    public const PRIVILEGED = 2;
    public const JANITOR = 3;
    public const ADMIN = 4;

    public static function to_string($rank = 0) {
        switch($rank) {
            case 4: return "Admin";
            case 3: return "Janitor";
            case 2: return "Privileged";
            case 1: return "Member";
            default: return "Visitor";
        }
    }

    public static function from_string($rank_string) {
        switch(strtolower($rank_string)) {
            case "admin": return 4;
            case "janitor": return 3;
            case "privileged": return 2;
            case "member": return 1;
            default: return 0;
        }
    }

}
?>
