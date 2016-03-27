<?php
namespace Model;

require_once("core/database/MysqlManager.php");

use Config\Config;
use DatabaseManager;

use MysqlManager;

class Model{
	public static $dbManager;
	
	protected static $prefTable;
	
	/**
	 * Method to connect the database
	 */
	public static function connectDB(){
		if (self::$prefTable == null)
			self::$prefTable = Config::$db_prefTable;
		self::$dbManager = new MysqlManager(Config::$db_host,Config::$db_user,Config::$db_passwd,Config::$db_name);
		return self::$dbManager->connectDb();
	}

    /**
     * Method to get the bind param array to prepare the statement
     * @param $typeChar The type available of the param. Types: s = string, i = integer, d = double,  b = blob
     * @param $valueString the value of the param
     */
    protected static function getBindParam($type,$value){
        $bindParam = [];
        $bindParam['type'] = $type;
        $bindParam['value'] = $value;
        return $bindParam;
    }

    /**
     * Method to bind dinamic parameters
     */
    protected static function bindDinParam(&$result,$bindParams){
        if (count($bindParams) == 0) return;

        $function_params = [];

        //extract the rows
        $param_type = "";
        $param_value = [];
        foreach($bindParams as $bindParam){
            if (isset($bindParam['type']))
                $param_type.= $bindParam['type'];
            $param_value[] = & $bindParam['value'];
        }

        /* with call_user_func_array, array params must be passed by reference */
        $function_params[] = & $param_type;

        $len = count($param_value);

        for($i=0;$i<$len;$i++){
            $function_params[] = & $param_value[$i];
        }

        //print_r($function_params);

        /* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
        call_user_func_array(array($result, 'bind_param'), $function_params);
    }


	/**
	 * Method to format query
	 */
	protected static function formatQuery($query){
		if (self::$prefTable == null) self::$prefTable = Config::$db_prefTable;
		$query = str_replace("{PREF_TABLE}",self::$prefTable,$query);
		return $query;
	}
	
}
?>
