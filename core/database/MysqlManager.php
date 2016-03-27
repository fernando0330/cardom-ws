<?php
require("core/database/DatabaseManager.php");
require_once("core/CustomLogger.php");

class MysqlManager extends DatabaseManager{
	
	private $result;
	
	public function getResult(){
		return $this->result;
	}
	
	public function __construct($host = null,$username = null,$password = null,$dbName = null){;
		parent::__construct($host,$username,$password,$dbName);
	}
	
	public function connectDb(){
		parent::$link = new mysqli($this->host,$this->username,$this->password,$this->dbName);
		
		if (parent::$link->connect_errno){
			$logger = new \Core\CustomLogger();
			$logger->error("Connect failed: " . parent::$link->connect_error . "\n");
			return false;
		}
		return true;
	}	
		
	/**
	 * Method to make query in database
	 */	
	public function query($query){
		if (!self::$link && !$this->connectDb()) return null;
		$result = parent::$link->prepare($query);
		if (!$result){
			//TODO: put a log
			$logger = new Core\CustomLogger();
			$logger->error("Error executing the query: details: " . parent::$link->error . ", query: " . $query);
			return false;
		}
		$this->result = $result;
		return $this->result;
	}
	
	/**
	 * Method to execute the sql
	 */
	public function executeSql(&$resultSql,$storeResult = true){
		if ($resultSql === null) return false;
		$resultSql->execute();
		
		if (!$resultSql){
			$logger = new \Core\CustomLogger();
			$logger->error(DatabaseManager::$link->error);
			return false;
		}
		
		if ($this->getResult()->errno){
			$logger = new \Core\CustomLogger();
			$logger->error($this->getResult()->error);
			return false;
		}
		if ($storeResult) $resultSql->store_result();
		return true;
	}
	
	/**
	 *
	 */	
	public function __destroy(){
		if (!parent::$link){
			parent::$link->close();
			parent::$link = null;
		}
	}
	
}
