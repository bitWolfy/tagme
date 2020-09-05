<?php

require_once ROOT . "/lib/tagme-auth/user.php";

use TagMe\Auth\UserRank;

class Routes {

    public const PAGE_ROUTES = [

        "home" => [
            "view" => [
                "path" => "",
                "html" => "home/view.php"
            ]
        ],

        "auth" => [

            "login" => [
                "path" => "auth\\/login",
                "html" => "auth/login.html.php",
                "json" => "auth/login.json.php",
            ],

            "logout" => [
                "path" => "auth\\/logout",
                "html" => "auth/logout.html.php",
                "json" => "auth/logout.json.php",
            ],

            "captcha" => [
                "path" => "auth\\/captcha",
                "json" => "auth/captcha.json.php",
            ]

        ],

        "projects" => [
        
            "new" => [
                "path" => "projects\\/new",
                "html" => "projects/new.html.php",
                "json" => "projects/new.json.php",
                "perm" => UserRank :: PRIVILEGED,
            ],
            "resolve" => [
                "path" => "projects\\/{%project_id%}\\/resolve(?:\\/{%post_id%})?",
                "html" => "projects/resolve.html.php",
                "json" => "projects/resolve.json.php",
                "perm" => UserRank :: MEMBER,
            ],
            "edit" => [
                "path" => "projects\\/{%project_id%}\\/edit",
                "json" => "projects/edit.json.php",
                "html" => "projects/edit.html.php",
                "perm" => UserRank :: PRIVILEGED,
            ],
            "changes" => [
                "path" => "projects\\/{%project_id%}\\/changes",
                "json" => "projects/changes.json.php",
            ],
            "delete" => [
                "path" => "projects\\/{%project_id%}\\/delete",
                "json" => "projects/delete.json.php",
                "html" => "projects/delete.html.php",
                "perm" => UserRank :: JANITOR,
            ],
            "view" => [
                "path" => "projects\\/{%project_id%}",
                "html" => "projects/view.html.php",
                "json" => "projects/view.json.php",
            ],
            "list" => [
                "path" => "projects",
                "html" => "projects/list.html.php",
                "json" => "projects/list.json.php",
            ],
        ],

        "changes" => [
            "list" => [
                "path" => "changes",
                "json" => "changes/list.json.php",
            ],
            "commit" => [
                "path" => "changes\\/commit",
                "json" => "changes/commit.json.php",
                "perm" => UserRank :: MEMBER,
            ],
        ],

        "users" => [
            "changes" => [
                "path" => "users\\/{%user_id%}\\/changes",
                "json" => "users/changes.json.php",
            ],
            "view" => [
                "path" => "users\\/{%user_id%}",
                "json" => "users/view.json.php",
                "html" => "users/view.html.php",
            ],
            "list" => [
                "path" => "users",
                "json" => "users/list.json.php",
                "html" => "users/list.html.php",
            ],
            "ban" => [
                "path" => "users\\/{%user_id%}\\/ban",
                "json" => "users/ban.json.php",
                "perm" => UserRank :: JANITOR,
            ],
        ],

        "comments" => [
            "list" => [
                "path" => "comments",
                "json" => "comments/list.json.php",
                "html" => "comments/list.html.php",
            ],
            "new" => [
                "path" => "comments\\/new",
                "json" => "comments/new.json.php",
            ],
            "view" => [
                "path" => "comments\\/{%comment_id%}",
                "json" => "comments/view.json.php",
            ],
            "edit" => [
                "path" => "comments\\/{%comment_id%}\\/edit",
                "json" => "comments/edit.json.php",
            ],
            "hide" => [
                "path" => "comments\\/{%comment_id%}\\/hide",
                "json" => "comments/hide.json.php",
            ],
        ],

        "admin" => [
            "betelgeuse" => [
                "path" => "admin\\/betelgeuse",
                "json" => "admin/betelgeuse.php",
                "perm" => UserRank :: MEMBER,
            ],
        ],

        "redirect" => [
            "projects" => [
                "path" => "p\\/{%project_id%}",
                "json" => "redirect/projects.php",
                "html" => "redirect/projects.php",
            ],
            "users" => [
                "path" => "u\\/{%user_id%}",
                "json" => "redirect/users.php",
                "html" => "redirect/users.php",
            ],
        ],

    ];

}

?>
