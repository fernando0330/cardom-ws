<?php
require_once ("models/model.php");

use Model\Model;

class Brand extends Model{
    const QUERY_FIND = "SELECT ID,NAME FROM brand";
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


    public static function findById($id){
        $brands = self::find($id);
        $brand = null;
        if ($brands && count($brands) == 1)
            $brand = $brands[0];
        return $brand;
    }

    /**
     * Method to find
    */
    public static function find($id = null,$name = null){
        $brands=  [];
        if (!self::connectDB()) return null;
        $query = self::QUERY_FIND;

        $where = "";
        $dinParams = [];

        if ($id){
            $where.= $where ? " AND " : "";
            $where = "id=?";
            $dinParams[] = self::getBindParam("i",$id);
        }


        if ($name){
            $where.= $where ? " AND " : "";
            $where = "name LIKE ?";
            $dinParams[] = self::getBindParam("s","%" . $name . "%");
        }

        if ($where) $query.= " WHERE $where";



        //ORDER BY
        $query.= " ORDER BY name ASC";

        $query = self::formatQuery($query);



        if (!$result = self::$dbManager->query($query)) return $brands;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $brands;

        $brands = self::mappingFromDBResult($result);

        return $brands;
    }

    /**
     * @return array
    */
    public function toArray(){
        $result = [];
        $result['id'] = $this->getId();
        $result['name'] = $this->getName();
        return $result;
    }

    /**
     * @return array(Brand)
    */
    public static function mappingFromDBResult(&$result){
        $bindResult = [];
        $brands = [];
        $result->bind_result($bindResult['id'],$bindResult['name']);
        while($result->fetch()){
            $brand = new Brand();
            $brand->setId($bindResult['id']);
            $brand->setName($bindResult['name']);
            $brands[] = $brand;
        }
        return $brands;
    }
}

