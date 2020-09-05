<?php

use TagMe\Util;
use TagMe\Configuration;
use TagMe\Database;
use TagMe\Auth\User;
use TagMe\Auth\UserRank;

const COMMENT_SEARCH_PARAMS = ["id", "project_id", "meta", "user_id", "username", "added_on", "edited_on", "is_deleted"];

function getCommentList($query = []) {

    $response = [
        "success" => false,
        "count" => 0,
        "data" => [],
    ];
    
    // Establish default pagination and ordering
    $pageNum = (isset($query["page"]) && is_numeric($query["page"])) ? intval($query["page"]) : 1;
    unset($query["page"]);
    
    if(!isset($query["order"]) || !is_string($query["order"])) $query["order"] = "id";

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

    if(!in_array($order["by"], COMMENT_SEARCH_PARAMS)) {
        $response["error"] = "input.order";
        return $response;
    }
    
    $pageParams = [
        "LIMIT" => [($pageNum - 1) * Configuration :: $page_length, Configuration :: $page_length],
        "ORDER" => [ $order["by"] => $order["how"] ]
    ];
    
    
    // Determine lookup parameters
    // If a parameter is invalid, abort
    $searchParams = [];
    
    if(isset($query["search"])) {
        $searchVal = preg_replace("/[^\p{L}\p{N}\s]/u", "", $query["search"]);
        if(!Util :: validate_search_param($searchVal)) {
            $response["error"] = "input.search";
            return $response;
        }

        $searchParams["OR"] = [
            "username[~]" => $searchVal,
            "content[~]" => $searchVal,
        ];
        unset($query["search"]);
    }
    
    $dynSearchParams = Util :: validate_query_array($query, COMMENT_SEARCH_PARAMS);
    if(is_null($dynSearchParams)) {
        $response["error"] = "input.search";
        return $response;
    }
    $searchParams = array_merge($searchParams, $dynSearchParams);

    
    // Proceed with the search
    $db = Database :: connect();
    try {
        $response["count"] = $db -> count(
            "comment",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            "*",
            $searchParams
        );
        $lookup = $db -> select(
            "comment",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            [
                "id[Int]",
                "project_id[Int]",
                "project.meta",
                "user_id[Int]",
                "user.username",
                "added_on",
                "edited_on",
                "content",
                "is_hidden[Bool]",
            ],
            array_merge($searchParams, $pageParams)
        );

        if($lookup == false) $lookup = [];
        $response["data"] = [];
        foreach($lookup as $entry) {
            if($entry["is_hidden"] && !User :: idMatches($entry["user_id"]) && !User :: rankMatches(UserRank :: JANITOR)) continue;
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

function getCommentByID($id) {
    $data = getCommentList([ "id" => $id ]);
    if($data["count"] > 0) $data["data"] = $data["data"][0];
    else $data["data"] = null;
    return $data;
}

function getProjectComments($project_id) {
    return getCommentList([ "meta" => $project_id, "order" => "project_id_asc" ]);
}

function getUserComments($user_id) {
    return getCommentList([ "user_id" => $user_id ]);
}

function getUserCommentCount($id_list) {

    $response = [
        "success" => false,
        "count" => count($id_list),
        "data" => [],
    ];

    $db = Database :: connect();
    foreach($id_list as $user_id) {
        $response["data"][$user_id] = $db -> count("comment", [ "user_id" => $user_id ]);
        if($response["data"][$user_id] === false) $response["data"][$user_id] = 0;
    }

    $response["success"] = true;
    return $response;
}

?>
