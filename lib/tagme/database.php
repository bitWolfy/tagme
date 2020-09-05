<?php
namespace TagMe;

use Medoo\Medoo;

require_once ROOT . '/config/settings.conf.php';
require_once ROOT . '/lib/medoo-1.7.10/Medoo.php';

class Database {
	
	/**
	 * Wrapper for the Medoo database, used to simplify the initial configuration
	 * @param string $databaseName Database to connect to
	 * @param string $username Username
	 * @param string $password Password
	 * @return \Medoo\medoo Database connection
	 */
	public static function connect() {
		return new Medoo ([ 
			"database_type"	=> "mysql",
			"database_name" => Configuration :: $db_name,
			"server"		=> "localhost",
			"username"		=> Configuration :: $db_user,
			"password"		=> Configuration :: $db_pass,
			
			"logging"		=> false,
		]);
	}
}

?>
