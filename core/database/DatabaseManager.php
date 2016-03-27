<?php
require_once("core/CustomLogger.php");
class DatabaseManager{
	public $host;
	public $username;
	public $password;
	public $port;
	public $dbName;
	public static $link;
	
	public function __construct($host = null,$username = null,$password = null,$dbName = null){
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->dbName = $dbName;
	}	
}
?>
