<?php

namespace TagMe;

class LibLoader {
	
	/**
	 * Parses the library configuration file, resolves dependencies, and returns a list of Javascript and CSS files in the correct order
	 * @return array[] List of libraries
	 */
	public static function load() {
		$libraryJSON = json_decode (file_get_contents (ROOT . "/config/libraries.conf.json"), true);
		
		$libraryList = [ ];
		foreach ( $libraryJSON as $libName => $libData ) {
			$libraryList [] = new CWKLibrary ($libName, $libData);
		}
		$libraryList = self :: sortDependencies ($libraryList);
		
		$libraryListProcessed = [ 
			"js" => [ ],
			"css" => [ ]
		];
		foreach ( $libraryList as $library ) {
			if ($library -> hasJS ())
				$libraryListProcessed ["js"] [] = $library -> getJS ();
			if ($library -> hasCSS ())
				$libraryListProcessed ["css"] [] = $library -> getCSS ();
		}
		return $libraryListProcessed;
	}
	
	/**
	 * Resolves dependencies by rearranging the provided library list
	 * @param CWKLibrary[] $libraryList Unsorted library list
	 * @return CWKLibrary[] List of libraries, with resolved dependencies
	 */
	private static function sortDependencies($libraryList) {
		$resolved = [ ];
		$doneList = [ ];
		
		// While not all items are resolved
		while ( count ($libraryList) > count ($resolved) ) {
			$doneSomething = false;
			$failedLibrary = "";
			
			foreach ( $libraryList as $library ) {
				if (isset ($doneList [$library -> getName ()]))
					continue;
				
				$depSolved = true;
				if ($library -> hasDependencies ()) {
					foreach ( $library -> getDependencies () as $dep ) {
						if (! isset ($doneList [$dep])) {
							// Dependency has not been met
							$depSolved = false;
							break;
						}
					}
				}
				
				// All dependencies met
				if ($depSolved) {
					$doneList [$library -> getName ()] = true;
					$resolved [] = $library;
					$doneSomething = true;
				} else {
					$failedLibrary = $library -> getName ();
				}
			}
			
			if (! $doneSomething) {
				Console :: xlog ($failedLibrary . " : Failed to resolve library dependencies");
				break;
			}
		}
		
		return $resolved;
	}
}

class CWKLibrary {
	private $name = "sample-lib";
	private $version = "0.0.0";
	private $depends = [ ];
	private $jsFile = "";
	private $cssFile = "";
	public function __construct($libName = "sample-lib", $libData = [ ]) {
		$this -> name = $libName;
		
		if (isset ($libData ["meta"])) {
			if (isset ($libData ["meta"] ["version"]))
				$this -> version = $libData ["meta"] ["version"];
			if (isset ($libData ["meta"] ["requires"]))
				$this -> depends = $libData ["meta"] ["depends"];
		}
		
		if (isset ($libData ["js"]))
			$this -> jsFile = $libData ["js"];
		if (isset ($libData ["css"]))
			$this -> cssFile = $libData ["css"];
	}
	public function getName() {
		return $this -> name;
	}
	public function hasDependencies() {
		return ! empty ($this -> depends);
	}
	public function getDependencies() {
		return $this -> depends;
	}
	public function hasJS() {
		return $this -> jsFile != "";
	}
	public function getJS() {
		return $this -> jsFile;
	}
	public function hasCSS() {
		return $this -> cssFile != "";
	}
	public function getCSS() {
		return $this -> cssFile;
	}
}

?>
