<?php

namespace TagMe;

class Configuration {

    // Database connection
    public static $db_name = "tagme";
    public static $db_user = "root";
    public static $db_pass = "";

    // User-Agent definitions
    // userfixr : used for low-frequency actions - authentication, user info fetching, etc
    // resolver : used for high-frequency actions - primarily resolving projects
    public static $agent_userfixr = "sample.tagme/userfixr/0.1";
    public static $agent_resolver = "sample.tagme/resolver/0.1";

    // Google ReCaptcha configuration
    public static $recaptcha_key = "";
    public static $recaptcha_secret = "";

    // Site Customization
    public static $commit_signature = "Tagging Sample Edit";
    public static $site_root = "sample.com";

    public static $url_github = "";
    public static $url_forum = "";

    // User limits
    public static $user_resolve = 100;      // changes needed for Member rank
    public static $user_create = 1000;      // changes needed for Privileged rank
    public static $user_refresh = 24;       // how often user data gets synced from e621
    public static $user_max_strikes = 3;    // strikes to summon beetlejuice
    public static $project_lock = 100;      // changes before the project can no longer be edited

    // Pagination
    public static $page_length = 100;

    // Misc
    public static $edit_threshold = 5;      // delay (in minutes) for comment ninja-edits

}

?>
