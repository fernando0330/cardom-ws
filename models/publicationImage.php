<?php
require_once ("models/model.php");
require_once ("lib/StringValidator.php");

class PublicationImage extends \Model\Model{

    const QUERY_FIND = "SELECT publication_id, filename FROM publication_image";

    protected static $contentTypeImages = ["image/png"=>"png","image/jpg"=>"jpg","image/jpeg"=>"jpg","image/gif"=>"gif"];


    /**
     * @var Publication $publication;
    */
    private $publication;


    /**
     * @var String $filename
    */
    private $filename;

    /**
     * @var String $encodedFile
     */
    private $encodedFile;

    /**
     * PublicationImage constructor.
     * @param Publication $publication
     * @param String $filename
     */
    public function __construct(Publication $publication, $filename,$encodedFile)
    {
        $this->publication  = $publication;
        $this->filename     = $filename;
        $this->encodedFile  = $encodedFile;
    }

    /**
     * @return Publication
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * @param Publication $publication
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;
    }

    /**
     * @return String
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param String $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return String
     */
    public function getEncodedFile()
    {
        return $this->encodedFile;
    }

    /**
     * @param String $encodedFile
     */
    public function setEncodedFile($encodedFile)
    {
        $this->encodedFile = $encodedFile;
    }

    /**
     * @return boolean
    */
    public function copyImage(){
        $image = self::getImageInfo($this->encodedFile);
        if (!$image) return false;

        //proceed to copy the image
        $copied = false;

        $rand    = rand();
        $filename = $this->getPublication()->getId() . "-" . $rand;
        $destinationFile = \Config\Config::DIR_RES_IMG_PUBLICATIONS . $filename;
        $copied = copy($image['temp_file'],$destinationFile);
        if ($copied){
            $this->setFilename($filename);
        }

        //end: proceed to copy the image
        return $copied;
    }

    /**
     * Method to get the image info from base64encoded
     */
    public static function getImageInfo($imgEncoded){
        if (!StringValidator::isBase64($imgEncoded)) return null;

        $img     = null;
        $rand    = rand();
        $imgTemp = base64_decode($imgEncoded);
        $tempFilePath = \Config\Config::DIR_TEMP . $rand;
        $filesize = file_put_contents($tempFilePath, $imgTemp);
        $contentType = mime_content_type($tempFilePath);
        foreach (self::$contentTypeImages as $type => $format) {
            if ($contentType === $type) {
                $img = base64_encode($imgTemp);
                break;
            }
        }

        $result = [];
        if ($img){
            $result['temp_file'] = $tempFilePath;
            $result['type'] = $contentType;
            $result['img'] = $img;
        }
        return $result;
    }

    /**
     * Method to get the image encoded and validate the content type
     */
    public static function getImageBlob($imgEncoded)
    {
        $result = self::getImageInfo($imgEncoded);
        $img = isset($result['img']) ? $result['img'] : null;
        return $img;
    }

    /**
     * Method to find
     * @return array(PublicationImage)
    */
    public static function find($publicationId = null,$filename = null){
        if (!self::connectDB()) return null;

        //dinamic parameters for query
        $dinParams = [];
        $where = "";
        $query = self::QUERY_FIND;

        if ($publicationId){
            $where.= $where ? " AND " : "";
            $where = "publication_id=?";
            $dinParams[] = self::getBindParam("i",$publicationId);
        }

        if ($filename){
            $where.= $where ? " AND " : "";
            $where = "filename=?";
            $dinParams[] = self::getBindParam("s",$filename);
        }

        if ($where) $query.= " WHERE $where";
        $query = self::formatQuery($query);

        $images= [];

        if (!$result = self::$dbManager->query($query)) return $images;
        self::bindDinParam($result,$dinParams);
        if (!self::$dbManager->executeSql($result)) return $images;

        $images = self::mappingFromDBResult($result);
        return $images;
    }

    /**
     * @return array(Publication)
     */
    private static function mappingFromDBResult(&$result){
        $bindResult = [];
        $images = [];
        $result->bind_result($bindResult['publication_id'],$bindResult['filename']);
        while($result->fetch()){
            $publicacion = new Publication();
            $publicacion->setId($bindResult['publication_id']);
            $image = new PublicationImage($publicacion,$bindResult['filename'],null);
            $images[] = $image;
        }
        return $images;

    }
}