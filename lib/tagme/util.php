<?php

namespace TagMe;

class Util {

    const VALID_QUERY_MATCH = "/^[a-zA-Z0-9_,]+$/";
    const VALID_SEARCH_MATCH = "/^[a-zA-Z0-9_, ]+$/";

    /** Returns true if the query parameter is valid */
    static function validate_query_param($param) {
        return preg_match(self :: VALID_QUERY_MATCH, $param);
    }

    /** Identical to `validate_query_param`, but allows for spaces */
    static function validate_search_param($param) {
        return preg_match(self :: VALID_SEARCH_MATCH, $param);
    }

    /**
     * Provided with an array of parameters, validates and returns them in a format that can be plugged into Medoo
     * @param $query Array to validate
     * @param $validKeys Any key not on this whitelist are ignored
     */
    static function validate_query_array($query, $validKeys) {
        $response = [];

        foreach($query as $key => $value) {
            if(!in_array($key, $validKeys) || !self :: validate_query_param($value)) return null;

            // Account for a potential comma-separated list instead of a single value
            $index = 0;
            $result = [];
            foreach(explode(",", $value) as $partKey => $partValue) {
                if(strtolower($partValue) == "true") $result[$partKey] = true;
                else if(strtolower($partValue) == "false") $result[$partKey] = false;
                else if(is_numeric($partValue)) $result[$partKey] = floatval($partValue);
                else $result[$partKey] = $partValue;

                // Maximum of 100 entries per request
                $index++;
                if($index >= 100) break;
            }

            // Only display the first parameter if it's the only one
            // This does not make a difference, but it makes database queries neater
            $count = count($result);
            if($count > 1) $response[$key] = $result;
            else if($count == 1) $response[$key] = $result[0];

            unset($query[$key]);
        }

        return $response;
    }

    /** Returns true if the $string ends with $test. Exact matches only. */
    static function str_ends_with($string, $test) {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) return false;
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }

    /** Converts a timestamp into `x minutes ago` string */
    static function to_time_ago($time) {
        $difference = time() - $time;
        if($difference < 1) return "seconds ago";
        
        $time_rule = array (
            12 * 30 * 24 * 60 * 60 => "year",
            30 * 24 * 60 * 60 => "month",
            24 * 60 * 60 => "day",
            60 * 60 => "hour",
            60 => "minute",
            1 => "second"
        );
        
        foreach( $time_rule as $sec => $my_str ) {
            $res = $difference / $sec;
            if( $res >= 1 ) {
                $t = round( $res );
                return $t . ' ' . $my_str . ( $t > 1 ? 's' : '' ) . ' ago';
            }
        }
    }

}

?>
