<?php

const ERROR_CODES = [
    400 => [
        "text" => "Bad Request",
        "desc" => "Malformed request",
    ],
    401 => [
        "text" => "Unauthorized",
        "desc" => "Authentication Failed",
    ],
    403 => [
        "text" => "Forbidden",
        "desc" => "Access Denied",
    ],
    404 => [
        "text" => "Not Found",
        "desc" => "The requested resource was not found",
    ],
    408 => [
        "text" => "Request Timeout",
        "desc" => "Request has timed out",
    ],

    500 => [
        "text" => "Internal Server Error",
        "desc" => "An error occurred while processing the request",
    ],
    
    1403 => [
        "text" => "Account Suspended",
        "desc" => "Your account has been suspended",
    ]
];

function getErrorResponse($format = "html", $code = 500) {
    if(!isset(ERROR_CODES[$code])) $code = 500;

    if($format == "json") {
        return [
            "success" => false,
            "error" => $code . ": " . ERROR_CODES[$code]["text"],
        ];
    } else {
        return "<section class=\"text-center\">"
            . "<section-header>Error " . $code . "</section-header>"
            . ERROR_CODES[$code]["desc"]
            . "</section>";
    }
}

?>
