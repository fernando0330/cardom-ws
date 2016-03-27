<?php
require_once ("models/model.php");

use Model\Model;

class User extends Model{

    const QUERY_FIND = "SELECT id, email, password, name, date_created FROM user";

    /**
     * @var int $id
    */
    private $id;

    /**
     * @var String $email
    */
    private $email;

    /**
     * @var String $password
     */
    private $password;

    /**
     * @var String $name
     */
    private $name;

    /**
     * @var String $dateCreated
     */
    private $dateCreated;

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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param String $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return String
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param String $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
     * Method to find by id
     */
    public static function findById($id){
        if (!$id) return null;
        $users = self::find($id);
        $user = null;
        if ($users && count($users) == 1) $user = $users[0];
        return $user;
    }

    /**
     * Method to find by id
     * @return User
     */
    public static function findByEmail($email){
        if (!$email) return null;
        $users = self::find(null,$email);
        $user = null;
        if ($users && count($users) == 1) $user = $users[0];
        return $user;
    }

    /**
     * Method to find
     * @return array(User)
     */
    public static function find($id = null,$email = null){
        $users=  [];
        if (!self::connectDB()) return null;
        $query = self::QUERY_FIND;

        $where = "";
        $dinParams = [];

        if ($id){
            $where.= $where ? " AND " : "";
            $where = "ID=?";
            $dinParams[] = self::getBindParam("i",$id);
        }

        if ($email){
            $where.= $where ? " AND " : "";
            $where = "EMAIL=?";
            $dinParams[] = self::getBindParam("s",$email);
        }

        if ($where) $query.= " WHERE $where";
        $query = self::formatQuery($query);
        if (!$result = self::$dbManager->query($query)) return $users;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $users;

        $users = self::mappingFromDBResult($result);

        return $users;
    }

    /**
     * @return array(User)
     */
    private static function mappingFromDBResult(&$result){
        $bindResult = [];
        $users = [];
        //id, email, password, name, date_created
        $result->bind_result($bindResult['id'],$bindResult['email'],$bindResult['password'],$bindResult['name'],$bindResult['date_created']);
        while($result->fetch()){
            $user = new User();
            $user->setId($bindResult['id']);
            $user->setEmail($bindResult['email']);
            $user->setPassword($bindResult['password']);
            $user->setName($bindResult['name']);
            $user->setDateCreated($bindResult['date_created']);
            $users[] = $user;
        }
        return $users;
    }

    /**
     * Method to crypt
    */
    private function cryptPassword(){
        $passwd = $this->getPassword();
        if (!$passwd) {
            $this->setPassword("");
            return true;
        }
        $passwd = password_hash($passwd,PASSWORD_BCRYPT);
        $this->setPassword($passwd);
    }


    public function login($password){
        return password_verify($password,$this->getPassword());
    }

    /**
     * @return boolean
    */
    public function add(){
        if (!self::connectDB()) return null;

        //dinamic parameters for query
        $dinParams = [];

        DatabaseManager::$link->autocommit(FALSE);

        $query = "INSERT INTO user(email, password, name) VALUES (?,?,?)";

        $this->cryptPassword();

        $dinParams[] = self::getBindParam("s",$this->getEmail());
        $dinParams[] = self::getBindParam("s",$this->getPassword());
        $dinParams[] = self::getBindParam("s",$this->getName());

        $query = self::formatQuery($query);

        if (!$result = self::$dbManager->query($query)) return null;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return null;

        $ret = true;
        if ($ret) DatabaseManager::$link->commit();
        else DatabaseManager::$link->rollback();
        return $ret;
    }

    public function toArray(){
        $result = [];
        $result['id']           = $this->getId();
        $result['email']        = $this->getEmail();
        $result['name']         = $this->getName();
        $result['date_created'] = $this->getDateCreated();
        return $result;
    }




}