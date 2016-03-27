<?php
namespace Model;

require_once("models/model.php");

class Session extends Model{
	
	const SESSION_DATE_EXPIRE = 30; //minutes

    const QUERY_FIND = "SELECT SESION_ID, OPCION_ADICIONAL, FECHA_EXPIRACION, FECHA_CREACION, USUARIO_ID FROM sesion";
	
	//INSERT INTO `sesion`(`SESION_ID`, `OPCION_ADICIONAL`, `FECHA_EXPIRACION`, `FECHA_CREACION`, `USUARIO_ID`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5])
	
	public $id;
	public $opcionAdicional;
	public $fechaExpiracion;
	public $fechaCreacion;

    //@var User
	public $user;


    public function __construct($sessionId= null,$user = null)
    {
        $this->id = $sessionId;
        $this->user = $user;
    }

    /**
	 * Method to generate session
	 */
	private static function generateSessionId(){
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

    /**
     * Method to verify the session
     * @return The session found, null if doesn't find any session
     */
    public static function verifySession($sessionId){
        if (!self::connectDB()) return null;

        $query = self::QUERY_FIND;
        $query.= " WHERE SESION_ID=? AND FECHA_EXPIRACION >= NOW()";

        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return null;

        $result->bind_param("s",$sessionId);
        if (!self::$dbManager->executeSql($result)) return null;

        $bindResult = [];
        $result->bind_result($bindResult['session_id'],$bindResult['opcion_adicional'],$bindResult['fecha_expiracion'],$bindResult['fecha_creacion'],$bindResult['usuario_id']);

        $session = $user = null;
        while($result->fetch()){
            $user = User::findById($bindResult['usuario_id']);
            $session = new Session($bindResult['session_id'],$user);
            $session->fechaExpiracion = $bindResult['fecha_expiracion'];
            $session->fechaCreacion = $bindResult['fecha_creacion'];
            $session->opcionAdicional = $bindResult['opcion_adicional'];
            break;
        }

        //update expiration date
        if ($session !== null){
            $query = Session::formatQuery("UPDATE sesion SET FECHA_EXPIRACION= NOW() + INTERVAL " . self::SESSION_DATE_EXPIRE . " MINUTE WHERE SESION_ID=?");
            if (!$result = self::$dbManager->query($query)) return null;
            $result->bind_param("s",$sessionId);
            if (!self::$dbManager->executeSql($result)) return null;
        }
        //end: update expiration date

        return $session;
    }
	
	/**
     * Method to generate session
     */
    public static function addSession(&$user){
        if ($user == null || $user->id == null) return null;
        if (!self::connectDB()) return null;

        $query = "INSERT INTO sesion(SESION_ID, OPCION_ADICIONAL, FECHA_EXPIRACION, FECHA_CREACION, USUARIO_ID) VALUES (?,?,NOW() + INTERVAL " . self::SESSION_DATE_EXPIRE . " MINUTE,NOW(),?)";
        $query = Session::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return null;

        $session = new Session();
        $session->id = Session::generateSessionId();
        //$session->opcionAdicional = $aditionalInfo;
        $session->opcionAdicional = $session->opcionAdicional ? json_encode($session->opcionAdicional) : null;

        $result->bind_param("ssi",$session->id,$session->opcionAdicional,$user->id);
        if (!self::$dbManager->executeSql($result)) return null;
        if ($result->affected_rows <= 0) $session = null;
        return $session;
    }


    /**
     * Method to logout session
     */
    public static function logoutSession($sessionId){
        if ($sessionId == null) return null;
        if (!self::connectDB()) return null;
        $query = "DELETE FROM sesion WHERE SESION_ID=?;";
        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return null;

        $result->bind_param("s",$sessionId);

        if (!self::$dbManager->executeSql($result)) return null;
        return $result->affected_rows > 0;
    }

}

