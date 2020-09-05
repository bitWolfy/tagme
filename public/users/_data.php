<?php

require_once ROOT . "/lib/tagme-auth/user.php";
require_once ROOT . "/lib/tagme/util.php";

require_once ROOT . "/public/changes/_data.php";
require_once ROOT . "/public/projects/_data.php";

use TagMe\Util;
use TagMe\Configuration;
use TagMe\Database;
use TagMe\Auth\UserRank;

const USER_SEARCH_PARAMS = ["user_id", "username", "rank", "is_banned", "changes"];

function getUserList($query = []) {

    $includeChanges = isset($query["changes"]) && $query["changes"] == "true";
    unset($query["changes"]);

    $response = [
        "success" => false,
        "count" => 0,
        "data" => [],
    ];
    
    // Establish default pagination and ordering
    $pageNum = (isset($query["page"]) && is_numeric($query["page"])) ? intval($query["page"]) : 1;
    unset($query["page"]);
    
    if(!isset($query["order"]) || !is_string($query["order"])) $query["order"] = "user_id";

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

    if(!in_array($order["by"], USER_SEARCH_PARAMS)) {
        $response["error"] = "input.order";
        return $response;
    }
    
    $pageParams = [
        "LIMIT" => [($pageNum - 1) * Configuration :: $page_length, Configuration :: $page_length],
        "ORDER" => [ $order["by"] => $order["how"] ],
        "GROUP" => "user_id",
    ];
    
    
    // Determine lookup parameters
    // If a parameter is invalid, abort
    $searchParams = [];
    
    if(isset($query["search"])) {
        $searchVal = $query["search"];
        if(!Util :: validate_query_param($searchVal)) {
            $response["error"] = "input.search";
            return $response;
        }

        $searchParams["OR"] = [
            "user_id[~]" => $searchVal,
            "username[~]" => $searchVal,
        ];
        unset($query["search"]);
    }
    
    if(isset($query["rank_string"])) {
        $searchParams["rank"] = UserRank :: from_string($query["rank_string"]);
        unset($query["rank_string"]);
    }
    
    $dynSearchParams = Util :: validate_query_array($query, USER_SEARCH_PARAMS);
    if(is_null($dynSearchParams)) {
        $response["error"] = "input.params";
        return $response;
    }
    $searchParams = array_merge($searchParams, $dynSearchParams);

    
    // Proceed with the search
    $idList = [];
    $db = Database :: connect();
    try {
        $response["count"] = $db -> count("user", $searchParams);
        $lookup = $db -> select(
            "user",
            [
            //    "[>]project" => [ "user_id" => "user" ],
                "[>]project_changes" => "user_id",
            ],
            [
                "user_id",
                "username",
                "rank[Int]",
                "rank (rank_string)",
                "strikes[Int]",
                "is_banned[Bool]",
                // "projects[Int]" => Medoo\Medoo::raw("COUNT(<project.project_id>)"),
                "changes[Int]" => Medoo\Medoo::raw("SUM(<project_changes.changes>)"),
            ],
            array_merge($searchParams, $pageParams)
        );

        if($lookup == false) $lookup = [];
        $response["data"] = [];
        foreach($lookup as $entry) {
            $entry["rank_string"] = UserRank :: to_string($entry["rank"]);
            $idList[] = $entry["user_id"];
            $response["data"][] = $entry;
        }
    } catch (Error $e) {
        $response["error"] = "response.db";
        return $response;
    }

    // Append user counts
    if($includeChanges) {
        $projects = getUserProjectCount($idList);
        foreach($response["data"] as $key => $entry) {
            $response["data"][$key]["projects"] = $projects["data"][$entry["user_id"]];
        }
    }
    
    if(!$response["count"]) $response["count"] = 0;
    
    $response["success"] = true;
    return $response;
}

function getUserByID($id, $show_changes = false) {
    $data = getUserList([ "user_id" => $id ]);
    if($data["count"] > 0) $data["data"] = $data["data"][0];
    else $data["data"] = null;
    return $data;
}

?>
