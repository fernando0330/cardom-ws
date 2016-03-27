<?php
require_once ("models/model.php");

use Model\Model;

class Condition extends Model{
    const QUERY_FIND = "SELECT C.id, C.name FROM car_condition C";
    /**
     * @var int $id
    */
    private $id;

    /**
     * @var String $name
    */
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param String $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Method to find
    */
    public static function find($id = null,$name = null){
        $conditions=  [];
        if (!self::connectDB()) return null;
        $query = self::QUERY_FIND;

        $where = "";
        $dinParams = [];

        if ($id){
            $where.= $where ? " AND " : "";
            $where = "C.id=?";
            $dinParams[] = self::getBindParam("i",$id);
        }


        if ($name){
            $where.= $where ? " AND " : "";
            $where = "C.name LIKE ?";
            $dinParams[] = self::getBindParam("s","%" . $name . "%");
        }

        if ($where) $query.= " WHERE $where";


        //ORDER BY
        $query.= " ORDER BY C.name DESC";

        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return $conditions;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $conditions;

        $conditions = self::mappingFromDBResult($result);
        return $conditions;
    }

    public static function findById($id){
        $conditions = self::find($id);
        $condition = null;
        if ($conditions && count($conditions) == 1)
            $condition = $conditions[0];
        return $condition;
    }

    /**
     * @return array(Condition)
    */
    private static function mappingFromDBResult(&$result){
        $bindResult = [];
        $conditions = [];
        $result->bind_result($bindResult['id'],$bindResult['name']);
        while($result->fetch()){
            $condition = new Condition();
            $condition->setId($bindResult['id']);
            $condition->setName($bindResult['name']);
            $conditions[] = $condition;
        }
        return $conditions;
    }
}

