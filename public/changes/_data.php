<?php

use TagMe\Util;
use TagMe\Configuration;
use Tagme\Database;

const CHANGES_SEARCH_PARAMS = ["id", "project_id", "meta", "name", "user_id", "username", "changes"];

function getChangesList($query = []) {

    $response = [
        "success" => false,
        "count" => 0,
        "total" => 0,
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

    if(!in_array($order["by"], CHANGES_SEARCH_PARAMS)) {
        $response["error"] = "input.order";
        return $response;
    }
    
    $pageParams = [
        "LIMIT" => [($pageNum - 1) * Configuration :: $page_length, Configuration :: $page_length],
        "ORDER" => [ $order["by"] => $order["how"] ],
    ];
    
    
    // Determine lookup parameters
    // If a parameter is invalid, abort
    $searchParams = [];
    
    if(isset($query["search"])) {
        $searchVal = $query["search"];
        if(Util :: validate_query_param($searchVal)) {
            $response["error"] = "input.search";
            return $response;
        }

        $searchParams["OR"] = [
            "project_meta[~]" => $searchVal,
            "project_name[~]" => $searchVal,
            "user_name[~]" => $searchVal,
        ];
        unset($query["search"]);
    }
    
    $dynSearchParams = Util :: validate_query_array($query, CHANGES_SEARCH_PARAMS);
    if(is_null($dynSearchParams)) {
        $response["error"] = "input.search";
        return $response;
    }
    $searchParams = array_merge($searchParams, $dynSearchParams);

    
    // Proceed with the search
    $db = Database :: connect();
    try {
        $response["count"] = $db -> count(
            "project_changes",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            "*",
            $searchParams
        );

        $response["total"] = $db -> sum(
            "project_changes",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            "changes",
            $searchParams
        );

        $lookup = $db -> select(
            "project_changes",
            [
                "[>]project" => "project_id",
                "[>]user" => "user_id",
            ],
            [
                "id[Int]",
                "project_id[Int]",
                "meta",
                "name",
                "user_id[Int]",
                "username",
                "changes[Int]",
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
    if(!$response["total"]) $response["total"] = 0;
    
    $response["success"] = true;
    return $response;
}

function getProjectChanges($id) {
    return getChangesList([ "meta" => $id ]);
}

function getUserChanges($id) {
    return getChangesList([ "user_id" => $id ]);
}

function getUserChangesCount($id_list) {

    $response = [
        "success" => false,
        "count" => count($id_list),
        "data" => [],
    ];
    
    $db = Database :: connect();
    foreach($id_list as $user_id) {

        $response["data"][$user_id] = $db -> sum(
            "project_changes",
            "changes",
            [ "user_id" => $user_id ]
        );

        if(is_null($response["data"][$user_id])) $response["data"][$user_id] = 0;
    }
    
    $response["success"] = true;
    return $response;
}

function getProjectChangesCount($id_list) {

    $response = [
        "success" => false,
        "count" => count($id_list),
        "data" => [],
    ];
    
    $db = Database :: connect();
    foreach($id_list as $project_id) {

        $response["data"][$project_id] = $db -> sum(
            "project_changes",
            "changes",
            [ "project_id" => $project_id ]
        );

        if(is_null($response["data"][$project_id])) $response["data"][$project_id] = 0;
    }
    
    $response["success"] = true;
    return $response;
}

function commitProjectChange($project_id, $user_id, $post_id) {

    $response = [
        "success" => false,
        "count" => 0,
        "data" => null,
    ];

    $db = Database :: connect();

    $lookup = $db -> select(
        "project_changes",
        [
            "id[Int]",
            "project_id[Int]",
            "user_id[Int]",
            "changes[Int]",
        ],
        [
            "project_id" => $project_id,
            "user_id" => $user_id,
        ]
    );

    if(isset($lookup[0])) {
        $db -> update(
            "project_changes",
            [ "changes[+]" => 1 ],
            [
                "project_id" => $project_id,
                "user_id" => $user_id,
            ]
        );
        $changes = $lookup[0]["changes"] + 1;
    } else {
        $db -> insert(
            "project_changes",
            [
                "project_id" => $project_id,
                "user_id" => $user_id,
                "changes" => 1,
            ]
        );
        $changes = 1;
    }

    $response["success"] = true;
    $response["data"] = $changes;
    return $response;
}

?>
