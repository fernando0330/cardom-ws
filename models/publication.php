<?php
require_once ("models/model.php");
require_once ("models/modelCar.php");
require_once ("models/condition.php");
require_once ("models/publicationImage.php");

use Model\Model;

class Publication extends Model{
    const QUERY_FIND = "SELECT P.id,P.model_id,M.name 'model_name',M.brand_id,B.name 'brand_name', P.year, P.condition_id,C.name 'condition_name', P.description, P.user, U.email 'user_email', U.name 'user_name', P.date_created FROM publication P INNER JOIN model M on P.model_id=M.id INNER JOIN brand b on M.brand_id=B.id INNER JOIN car_condition C on P.condition_id=C.id INNER JOIN user U on P.user=U.id";

    /**
     * @var int $id
    */
    private $id;

    /**
     * @var ModelCar $model
     */
    private $model;

    /**
     * @var int $year
     */
    private $year;

    /**
     * @var Condition $condition
     */
    private $condition;

    /**
     * @var String $description
     */
    private $description;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var String $dateCreated
     */
    private $dateCreated;


    /**
     * @var array(PublicationImage) $images
    */
    private $images;


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
     * @return ModelCar
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param ModelCar $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return Condition
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param Condition $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param String $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return String
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param String $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param array $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * Method to find by id
     */
    public static function findById($id){
        $publications = self::find($id);
        $publication = null;
        if ($publications && count($publications) == 1) $publication = $publications[0];
        return $publication;
    }

    /**
     * @return array(Publication)
    */
    public static function find($id = null,$brand = null, $model = null,$year = null,$user = null){
        $users=  [];
        if (!self::connectDB()) return null;
        $query = self::QUERY_FIND;

        $where = "";
        $dinParams = [];

        if ($id){
            $where.= $where ? " AND " : "";
            $where = "P.id=?";
            $dinParams[] = self::getBindParam("i",$id);
        }

        if ($brand){
            $where.= $where ? " AND " : "";
            $where = "M.brand_id=?";
            $dinParams[] = self::getBindParam("i",$brand);
        }

        if ($model){
            $where.= $where ? " AND " : "";
            $where = "P.model_id=?";
            $dinParams[] = self::getBindParam("i",$model);
        }

        if ($year){
            $where.= $where ? " AND " : "";
            $where = "P.year=?";
            $dinParams[] = self::getBindParam("i",$year);
        }

        if ($user){
            $where.= $where ? " AND " : "";
            $where = "P.user=?";
            $dinParams[] = self::getBindParam("i",$user);
        }

        if ($where) $query.= " WHERE $where";
        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return $users;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $users;

        $users = self::mappingFromDBResult($result);

        return $users;

    }

    public function toArray(){
        $result = [];

        $result['id'] = $this->getId();

        $result['model'] = [];
        $result['model']['id'] = $this->getModel()->getId();
        $result['model']['name'] = $this->getModel()->getName();

        $result['model']['brand'] = [];
        $result['model']['brand']['id'] = $this->getModel()->getBrand()->getId();
        $result['model']['brand']['name'] = $this->getModel()->getBrand()->getName();

        $result['year'] = $this->getYear();

        $result['condition'] = [];
        $result['condition']['id'] = $this->getCondition()->getId();
        $result['condition']['name'] = $this->getCondition()->getName();

        $result['description'] = $this->getDescription();

        $result['user'] = [];
        $result['user']['id'] = $this->getUser()->getId();
        $result['user']['name'] = $this->getUser()->getName();
        $result['user']['email'] = $this->getUser()->getEmail();


        $result['images'] = [];
        foreach($this->getImages() as $image){
            $result['images'][] = "http://" . $_SERVER['SERVER_NAME'] . "/" . \Config\Config::DIR_RES_IMG_PUBLICATIONS . $image->getFilename();
        }
        $result['date'] = date(\Config\Config::$formatDate,strtotime($this->getDateCreated()));
        return $result;
    }


    /**
     * @return array(Publication)
     */
    private static function mappingFromDBResult(&$result){
        $bindResult = [];
        $publications = [];
        $result->bind_result(
            $bindResult['id'],
            $bindResult['model_id'],$bindResult['model_name'],
            $bindResult['brand_id'],$bindResult['brand_name'],
            $bindResult['year'],
            $bindResult['condition_id'],$bindResult['condition_name'],
            $bindResult['description'],
            $bindResult['user_id'],$bindResult['user_email'],$bindResult['user_name'],
            $bindResult['date_created']
            );
        while($result->fetch()){
            $publication = new Publication();
            $publication->setId($bindResult['id']);

            $model = new ModelCar();
            $model->setId($bindResult['model_id']);
            $model->setName($bindResult['model_name']);

            $brand = new Brand();
            $brand->setId($bindResult['brand_id']);
            $brand->setName($bindResult['brand_name']);
            $model->setBrand($brand);

            $publication->setModel($model);
            $publication->setYear($bindResult['year']);

            $condition = new Condition();
            $condition->setId($bindResult['condition_id']);
            $condition->setName($bindResult['condition_name']);
            $publication->setCondition($condition);

            $publication->setDescription($bindResult['description']);

            $user = new User();
            $user->setId($bindResult['user_id']);
            $user->setName($bindResult['user_name']);
            $user->setEmail($bindResult['user_email']);
            $publication->setUser($user);

            $publication->setDateCreated($bindResult['date_created']);

            $publication->retrieveImages();

            $publications[] = $publication;
        }

        return $publications;
    }


    /**
     * @return array(PublicationImage)
    */
    public function retrieveImages(){
        $this->images = PublicationImage::find($this->getId());
        return $this->images;
    }

    /**
     * Method to add publication
     *
    */
    public function add(){
        if (!self::connectDB()) return null;

        //dinamic parameters for query
        $dinParams = [];

        DatabaseManager::$link->autocommit(FALSE);

        $query = "INSERT INTO publication(model_id, year, condition_id, description, user) VALUES(?,?,?,?,?)";

        $dinParams = [];
        $dinParams[] = self::getBindParam("i",$this->getModel()->getId());
        $dinParams[] = self::getBindParam("i",$this->getYear());
        $dinParams[] = self::getBindParam("i",$this->getCondition());
        $dinParams[] = self::getBindParam("s",$this->getDescription());
        $dinParams[] = self::getBindParam("i",$this->getUser()->getId());

        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return null;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return null;

        $ret = false;
        $error = false;
        if ($result->affected_rows > 0){
            $this->id = $result->insert_id;
            if (!$this->persistImages())                 $error = true;
            $ret = !$error;
        }

        if ($ret) DatabaseManager::$link->commit();
        else DatabaseManager::$link->rollback();
        return $ret;
    }

    /**
     * Method to persist images
     * @return boolean
     */
    private function persistImages(){
        if (!is_array($this->getImages()) || count($this->getImages()) == 0)  return true;


        //add the images
        foreach($this->getImages() as $image){
            if (!$image->copyImage()) continue;
            $query = "INSERT INTO publication_image(publication_id, filename) VALUES (?,?)";
            $dinParams = [];
            $dinParams[] = self::getBindParam("i",$this->getId());
            $dinParams[] = self::getBindParam("s",$image->getFilename());
            $query = self::formatQuery($query);
            if (!$result = self::$dbManager->query($query))
                return false;
            self::bindDinParam($result,$dinParams);

            if (!self::$dbManager->executeSql($result))
                return false;

        }
        return true;
    }
}