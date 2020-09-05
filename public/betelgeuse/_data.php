<?php

use TagMe\Util;
use TagMe\Configuration;
use Tagme\Database;
use TagMe\Auth\User;

const STRIKE_SEARCH_PARAMS = ["id", "user_id", "username", "project_id", "meta", "post_id", "timestamp", "old_tags", "new_tags"];

function getStrikeList($query = []) {

    $response = [
        "success" => false,
        "count" => 0,
        "data" => [],
    ];
    
    // Establish default pagination and ordering
    $pageNum = (isset($query["page"]) && is_numeric($query["page"])) ? intval($query["page"]) : 1;
    unset($query["page"]);
    
    if(!isset($query["order"]) || !is_string($query["order"])) $query["order"] = "project_id";

    if(Util :: str_ends_with($query["order"], "_asc")) {
        $order = [
            "by" => substr($query["order"], 0, -4),
            "how" => "ASC",
        ];
    } else {
        $order = [
            "by" => $query["order"],
            "how" => "DESC",
        ];
    }
    unset($query["order"]);

    if(!in_array($order["by"], STRIKE_SEARCH_PARAMS)) {
        $response["error"] = "input.order";
        return $response;
    }
    
    $pageParams = [
        "LIMIT" => [($pageNum - 1) * Configuration :: $page_length, Configuration :: $page_length],
        "ORDER" => [ $order["by"] => $order["how"] ],
        "GROUP" => "id",
    ];
    
    
    // Determine lookup parameters
    // If a parameter is invalid, abort
    $searchParams = [];
    
    if(isset($query["search"])) {
        $searchVal = preg_replace("/[^\p{L}\p{N}\s]/u", "", $query["search"]);
        if(!Util :: validate_search_param($searchVal)) {
            $response["error"] = "input.lookup";
            return $response;
        }

        $searchParams["OR"] = [
            "username[~]" => $searchVal,
            "old_tags[~]" => $searchVal,
            "new_tags[~]" => $searchVal,
        ];
        unset($query["search"]);
    }
    
    $dynSearchParams = Util :: validate_query_array($query, STRIKE_SEARCH_PARAMS);
    if(is_null($dynSearchParams)) {
        $response["error"] = "input.search";
        return $response;
    }
    $searchParams = array_merge($searchParams, $dynSearchParams);

    
    // Proceed with the search
    $db = Database :: connect();
    try {
        $response["count"] = $db -> count(
            "project",
            $searchParams
        );
        $lookup = $db -> select(
            "betelgeuse",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            [
                "id[Int]",
                "user_id[Int]",
                "username",
                "project_id[Int]",
                "meta",
                "post_id[Int]",
                "timestamp[Int]",
                "old_tags",
                "new_tags",
            ],
            array_merge($searchParams, $pageParams)
        );

        if($lookup == false) $lookup = [];
        $response["data"] = [];
        foreach($lookup as $entry) {
            $response["data"][] = $entry;
        }
    } catch (Error $e) {
        $response["error"] = "response.db";
        return $response;
    }
    
    if(!$response["count"]) $response["count"] = 0;
    
    $response["success"] = true;
    return $response;
}

function getStrikeByID($id) {
    $data = getProjectList([ "id" => $id ]);
    if($data["count"] > 0) $data["data"] = $data["data"][0];
    else $data["data"] = null;
    return $data;
}

function summon($project_id, $post_id, $old_tags, $new_tags) {
    
    $db = Database :: connect();

    // Add to betelgeuse history
    $db -> insert(
        "betelgeuse",
        [
            "user_id" => User :: getUserID(),
            "project_id" => $project_id,
            "post_id" => $post_id,
            "timestamp" => time(),
            "old_tags" => $old_tags,
            "new_tags" => $new_tags,
        ],
    );

    // Set user strikes
    $set_banned = (User :: getStrikes() + 1) >= Configuration :: $user_max_strikes;
    $db -> update(
        "user",
        [
            "strikes[+]" => 1,
            "is_banned" => (User :: isBanned() || $set_banned) ? 1 : 0,
        ],
        [ "user_id" => User :: getUserID(), ]
    );

    return User :: getStrikes() + 1;
}

?>
