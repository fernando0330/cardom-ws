<?php
namespace Core;

use Model\Session;
use Model\User;

class SessionManager{

    private static $sessionPattern = "/^[a-zA-Z0-9]{8}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{12}$/";

    private static $session;

    private static $user;


    /**
     * @return User
    */
    public static function getUser(){
        return self::$user;
    }

    public static function setUser($user){
        self::$user = $user;
    }

    /**
     * @return Session
    */
    public static function getSession(){
        return self::$session;
    }

    public function __construct($sessionId = null){
        $this->id = $sessionId;
    }
    /**
     * Method to regenerate a session id
     */
    public function generateSession($userId){
        self::$session = Session::addSession($userId);
        return self::$session;
    }

    /**
     * Method to verify whether a session is valid or not
     */
    public static function isValidSession($sessionId){
        return $sessionId != null && preg_match(self::$sessionPattern,$sessionId);
    }

    /**
     * Method to verify whether a session has expired or not. Also, set session property for session manager
     */
    public static function hasSessionExpired($sessionId){
        self::$session = Session::verifySession($sessionId);
        return self::$session !== null;
    }

}

