<?php

namespace TagMe;

require_once ROOT . "/lib/tagme-auth/user.php";

use TagMe\Auth\User;

class PageRouter {

	private const VAR_MATCH = "[a-z0-9_]+";		// pattern that the template variables must follow

	private static $address = "";           	// trimmed page address, ex. projects/nchar/resolve/12345
	private static $stack = array();        	// same as above, exploded, ex. [ "projects", "nchar", "resolve", "12345" ]

	private static $vars = [];
	private static $outputFormat = "html";
	private static $outputPage = "";

	public static function init($routes_list) {

		self :: $address = trim($_SERVER['REQUEST_URI'], " \t\n\r\0\x0B/");							// trim garbage data
		self :: $stack = explode("/", preg_replace('/\\.[^.\\s]{3,4}$/', '', self :: $address));	// remove extension

		$match = null;
		$varPattern = "/\{%(" . self :: VAR_MATCH . ")%\}/";
		foreach($routes_list as $group => $entries) {
			foreach($entries as $route_name => $route) {
				// echo "testing [" . $group . "." . $route_name . "]\n";

				// Replace template variables with alphanumeric regex
				$innerPattern = $route["path"];
				preg_match_all($varPattern, $route["path"], $varMatches);
				for($i = 0; $i < count($varMatches[1]); $i++) {
					$innerPattern = preg_replace("/\{%" . $varMatches[1][$i] . "%\}/", "(" . self :: VAR_MATCH . ")", $innerPattern);
				}

				// Convert the template string into a regex
				$innerPattern = "/^" . $innerPattern . "(?:\\/|\.json|\.jpeg)?(?:\?\S+)?$/";
				// var_dump($innerPattern);
				
				// If the regex matches, dump variables and return
				if(preg_match($innerPattern, self :: $address, $patternMatches)) {
					// var_dump($patternMatches);
					$match = $route;

					// Collect variable values
					for($i = 0; $i < count($varMatches[1]); $i++) {
						if(!isset($patternMatches[$i + 1])) self :: $vars[$varMatches[1][$i]] = null;
						else if($patternMatches[$i + 1] == "") self :: $vars[$varMatches[1][$i]] = null;
						else self :: $vars[$varMatches[1][$i]] = $patternMatches[$i + 1];
					}
					break;
				}
			}
			if(!is_null($match)) break;
		}

		// Determine output mode
		$strippedPath = preg_replace("/\?.+$/", "", self :: $address);
		if(preg_match("/\.json$/", $strippedPath) > 0) self :: $outputFormat = "json";
		else if(preg_match("/\.jpeg$/", $strippedPath) > 0) self :: $outputFormat = "jpeg";
		else self :: $outputFormat = "html";

		// Get the page
		if(isset($match[self :: $outputFormat])) self :: $outputPage = "public/" . $match[self :: $outputFormat];
		else self :: $outputPage = "static/error/404." . ((self :: $outputFormat == "json") ? "json" : "html") . ".php";

		// Check permissions
		if(isset($match["perm"]) && is_numeric($match["perm"]) && !User :: rankMatches(intval($match["perm"])))
			self :: $outputPage = "static/error/403." . ((self :: $outputFormat == "json") ? "json" : "html") . ".php";

		// Deny all pages to suspended accounts
		if(User :: isBanned())
			self :: $outputPage = "static/error/1403." . ((self :: $outputFormat == "json") ? "json" : "html") . ".php";

		// Debug output
		// var_dump($match, self :: $outputPage, self :: $outputFormat);

		return [
			"page" => self :: $outputPage,
			"json" => self :: $outputFormat == "json" || self :: $outputFormat == "jpeg",
		];
	}

	public static function getVars($param) {
		if(!isset($param)) return self :: $vars;
		return self :: $vars[$param];
	}
	public static function getOutputFormat() { return self :: $outputFormat; }
	public static function getPage() { return self :: $outputPage; }

}

?>
