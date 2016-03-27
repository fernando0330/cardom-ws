<?php
namespace Core;

use Model\Role;
use Model\User;

require "core/SessionManager.php";
    
class Webservice{
    
    const RESPONSE_MSG_OK = "OK";

	const METHOD_GET 	= "GET";
	const METHOD_POST 	= "POST";
	const METHOD_PUT 	= "PUT";

	const DEFAULT_CONTENT_TYPE = "application/json";

	const PARAM_HEADER_LOCALE = "xrqt-locale";
	const PARAM_LOCALE = "locale";
	const PARAM_SESSIONID = "session_id";

	const RESPONSE_PARAM_CODE = "code";
	const RESPONSE_PARAM_RESULT = "result";
	const RESPONSE_PARAM_ERROR = "error";
	const RESPONSE_PARAM_MSG = "msg";

	var $param;

	var $code;
	var $error;
	var $msg;
	var $result;
    
    
    /**
	 * Construct Method
	 */
	public function __construct($useSession = true,$session = null,$code = 0,$msg = self::RESPONSE_MSG_OK){
		$this->code = $code;
		$this->msg = $msg;
		$this->error = false;
		$this->result = array();
		$this->useSession = $useSession;

		$this->param = array();

		if ($this->useSession) $this->sessionManager = new SessionManager($session);
	}
    
    /**
	 * Method to prepare the request before execute
     * TODO: finish
	 */
    public function prepareRequest($method = "GET",$paramRequest = null,$app = null){
        $method = strtoupper($method);

        if ($method != self::METHOD_GET && $paramRequest === null) {
            $this->generate_error(3,"Estructura JSON inv&aacute;lida");
            echo $this->output($app);
            return false;
        }

        if (isset($paramRequest[self::PARAM_SESSIONID])) $this->param[self::PARAM_SESSIONID]  = $paramRequest[self::PARAM_SESSIONID];


        if ($this->useSession){
            $this->param[self::PARAM_SESSIONID] = isset($this->param[self::PARAM_SESSIONID]) ? $this->param[self::PARAM_SESSIONID] : null;

            if (!SessionManager::isValidSession($this->param[self::PARAM_SESSIONID])){
                $this->generate_error(5,"Sesi&oacute;n inv&aacute;lida");
                echo $this->output($app);
                return false;
            }


            if (!SessionManager::hasSessionExpired($this->param[self::PARAM_SESSIONID])){
                $this->generate_error(6,"Sesi&oacute;n expirada");
                echo $this->output($app);
                return false;
            }

            $user = SessionManager::getSession()->user;

            if (!$user){
                $this->generate_error(01,"Usuario no Encontrado");
                echo $this->output($app);
                return false;
            }

            //method to verify the privileges
            $resource = $app->request->getPathinfo();


            if (!Role::hasPermission($resource,$user->getRole()->getId())){
                $this->generate_error(02,"No tiene permiso suficiente para acceder a este m&oacute;dulo");
                echo $this->output($app);
                return false;
            }
            //end: method to verify the privileges


        }
        return true;
    }
    
    /**
	 * Method to make output to webservice
	 */
	public function output($app){
		$json = array();
		$json[Webservice::RESPONSE_PARAM_CODE] = $this->code;
		$json[Webservice::RESPONSE_PARAM_MSG] = utf8_encode($this->msg);
		$json[Webservice::RESPONSE_PARAM_RESULT] = $this->result;

		if ($app != null) {
			$app->response->headers->set('Content-Type', Webservice::DEFAULT_CONTENT_TYPE);
		}

		//DONT REMOVE THIS
		$this->generateLog($app,$json);
		return json_encode($json);
	}
    
    /**
	 * Method to generate http log
	 */
	private function generateLog($app,$response){
		//generate log
		$logger = new CustomLogger();

		$resourceUri = $app->request->getResourceUri();

		$method = $app->request->getMethod();
		$rqHeaders = $app->request->headers->all();

		switch($method){
			case \Slim\Http\Request::METHOD_GET:
				$reqParam = $app->request->get();
				break;
			default:
				$body = $app->request->getBody();
				$reqParam = json_decode($body,true);
				break;
		}
		$rsHeaders = $app->response->headers->all();
		$res = $response;

		$logger = $logger->httpLog($resourceUri,$method,$rqHeaders,$reqParam,$rsHeaders,$res);
	}
    
    /**
	 * Method to generate error in webservice to return
	 */
	public function generate_error($code,$msg){
		$this->error = true;
		$this->msg = $msg;
		$this->code = $code;
	}
}


