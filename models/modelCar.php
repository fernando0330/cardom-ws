<?php
require_once ("models/model.php");
require_once ("models/brand.php");

use Model\Model;

class ModelCar extends Model{
    const QUERY_FIND = "SELECT M.id,M.brand_id,B.name 'brand_name',M.name FROM model M INNER JOIN brand B on M.brand_id=B.id";
    /**
     * @var int $id
    */
    private $id;

    /**
     * @var String $name
    */
    private $name;


    /**
     * @var Brand $brand
    */
    private $brand;

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
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }


    /**
     * Method to find
    */
    public static function find($id = null,$brandId = null,$name = null){
        $models = [];

        if (!self::connectDB()) return null;
        $query = self::QUERY_FIND;

        $where = "";
        $dinParams = [];

        if ($id){
            $where.= $where ? " AND " : "";
            $where = "M.id=?";
            $dinParams[] = self::getBindParam("i",$id);
        }

        if ($brandId){
            $where.= $where ? " AND " : "";
            $where = "M.brand_id =?";
            $dinParams[] = self::getBindParam("i",$brandId);
        }


        if ($name){
            $where.= $where ? " AND " : "";
            $where = "M.name LIKE ?";
            $dinParams[] = self::getBindParam("s","%" . $name . "%");
        }

        if ($where) $query.= " WHERE $where";

        //ORDER BY
        $query.= " ORDER BY M.name ASC";

        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return $models;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $models;

        $models = self::mappingFromDBResult($result);

        return $models;
    }

    /**
     * Method t
    */
    public function toArray(){
        $result = [];

        $result['id'] = $this->getId();
        $result['brand'] = [];
        $result['brand']['id'] = $this->getBrand()->getId();
        $result['brand']['name'] = $this->getBrand()->getName();
        $result['name'] = $this->getName();

        return $result;

    }

    public static function findById($id){
        $models = self::find($id);
        $model = null;
        if ($models && count($models) == 1)
            $model = $models[0];
        return $model;
    }

    /**
     *
    */
    public static function mappingFromDBResult(&$result){
        $bindResult = [];
        $models = [];
        $result->bind_result($bindResult['id'],
                        $bindResult['brand_id'],$bindResult['brand_name'],
                        $bindResult['name']);
        while($result->fetch()){
            $model = new ModelCar();
            $model->setId($bindResult['id']);
            $brand = new Brand();
            $brand->setId($bindResult['brand_id']);
            $brand->setName($bindResult['brand_name']);
            $model->setBrand($brand);
            $model->setName($bindResult['name']);
            $models[] = $model;
        }
        return $models;
    }
}

