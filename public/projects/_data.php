<?php

require_once ROOT . "/public/changes/_data.php";

use TagMe\Util;
use TagMe\Configuration;
use Tagme\Database;

const PROJECT_SEARCH_PARAMS = ["project_id", "name", "meta", "user", "is_deleted", "changes", "is_private"];

function getProjectList($query = []) {

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

    if(!in_array($order["by"], PROJECT_SEARCH_PARAMS)) {
        $response["error"] = "input.order";
        return $response;
    }
    
    $pageParams = [
        "LIMIT" => [($pageNum - 1) * Configuration :: $page_length, Configuration :: $page_length],
        "ORDER" => [ $order["by"] => $order["how"] ],
        "GROUP" => "project_id",
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
            "name[~]" => $searchVal,
            "meta[~]" => $searchVal,
            "desc[~]" => $searchVal,
        ];
        unset($query["search"]);
    }
    
    $dynSearchParams = Util :: validate_query_array($query, PROJECT_SEARCH_PARAMS);
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
            "project",
            [
                "[>]project_changes" => "project_id",
            ],
            [
                "project_id[Int]",
                "name",
                "meta",
                "user[Int]",
                "desc",
                "text",
                "tags[JSON]",
                "optmode[Int]",
                "options[JSON]",
                "contags[JSON]",
                "is_deleted[Bool]",
                "changes[Int]" => Medoo\Medoo::raw("SUM(<project_changes.changes>)"),
                "is_private[Bool]",
            ],
            array_merge($searchParams, $pageParams)
        );

        if($lookup == false) $lookup = [];
        $response["data"] = [];
        foreach($lookup as $entry) {
            if(is_null($entry["changes"])) $entry["changes"] = 0;
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

function getProjectByID($id) {
    $data = getProjectList([ "meta" => $id ]);
    if($data["count"] > 0) $data["data"] = $data["data"][0];
    else $data["data"] = null;
    return $data;
}

function getUserProjectCount($id_list) {

    $response = [
        "success" => false,
        "count" => count($id_list),
        "data" => [],
    ];

    $db = Database :: connect();
    foreach($id_list as $user_id) {
        $response["data"][$user_id] = $db -> count("project", [ "user" => $user_id ]);
        if($response["data"][$user_id] === false) $response["data"][$user_id] = 0;
    }

    $response["success"] = true;
    return $response;
}

function deleteProject($project_id, $restore = false) {
    
    $db = Database :: connect();
    $db -> update(
        "project",
        [ "is_deleted" => $restore ? 0 : 1, ],
        [ "meta" => $project_id, ]
    );

    return $restore;
}

?>
