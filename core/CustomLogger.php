<?php
namespace Core;

require("lib/log4j/Logger.php");

use Logger;

class CustomLogger{
	
	const FILE_SEPARATOR = "|";
	
	private $logger;
	
	public function __construct(){
		$this->logger = Logger::getLogger("main");
		Logger::configure('lib/log4j/xml/config.xml');
	}
	
	
	public function debug($msg){
		$this->logger->debug($msg);
	}
	
	public function httpLog(&$resource,&$method,&$requestHeader,&$request,&$responseHeader,&$response){
		$this->logger = Logger::getLogger("HTTP");
		$msg = $resource;
		$msg.= self::FILE_SEPARATOR;
		$msg.= $method;
		$msg.= self::FILE_SEPARATOR;
		$msg.= json_encode($requestHeader);
		$msg.= self::FILE_SEPARATOR;
		$msg.= json_encode($request);
		$msg.= self::FILE_SEPARATOR;
		$msg.= json_encode($responseHeader);
		$msg.= self::FILE_SEPARATOR;
		$msg.= json_encode($response);
		$this->logger->info($msg);
	}
	
	public function error($msg){
		$this->logger = Logger::getLogger("ERROR");
		$this->logger->error($msg);
	}
	
	public function info($msg){
		$this->logger->info($msg);
	}
	
	public function warn($msg){		
		$this->logger->warn($msg);
	}	
}

?>
