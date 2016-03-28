<?php
require 'Slim/Slim.php';
require_once("config/config.php");
require "core/Webservice.php";
require "models/brand.php";
require "models/modelCar.php";
require "models/publication.php";
require "models/user.php";
//require "vendor/endroid/qrcode/src/Exceptions/FreeTypeLibraryMissingException.php";
//require "vendor/endroid/qrcode/src/QrCode.php";

require 'vendor/autoload.php';

\Slim\Slim::registerAutoloader();

use Endroid\QrCode\QrCode;
use Dompdf\Dompdf;

$app = new \Slim\Slim();

$body = $app->request->getBody();
$param = json_decode($body,true);


$app->get("/",function() use($param,$app) {
    /*$qrCode = new QrCode();
    $img = $qrCode
        ->setText("http://google.com")
        ->setSize(300)
        ->setPadding(10)
        ->setErrorCorrection('high')
        ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
        ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
        ->setLabel('Visit Us')
        ->setLabelFontSize(16)
        ->getDataUri()
    ;*/
    // instantiate and use the dompdf class
    $dompdf = new Dompdf();
    $dompdf->loadHtml('<div style="text-align: center;"><h2>Honda Civic 2008</h2><img src=\"$img\" title=\"Visit Us\"/></div>');

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $dompdf->stream();
});


$app->get("/brand/get",function() use($param,$app) {
    $ws = new \Core\Webservice(false);
    $brands = Brand::find();
    $results = [];
    foreach($brands as $brand) $results[] = $brand->toArray();
    $ws->result = $results;
    echo $ws->output($app);
});


$app->get("/model/get",function() use($param,$app) {
    $ws = new \Core\Webservice(false);

    $param = $_GET;
    $brand = isset($param['brand']) ? $param['brand'] : null;

    $models = ModelCar::find(null,$brand);
    $results = [];
    foreach($models as $model) $results[] = $model->toArray();
    $ws->result = $results;
    echo $ws->output($app);
});

$app->get("/publication/get",function() use($param,$app) {
    $ws = new \Core\Webservice(false);

    $param = $_GET;
    $brand = isset($param['brand']) ? $param['brand'] : null;
    $model = isset($param['model']) ? $param['model'] : null;
    $year  = isset($param['year']) ? $param['year'] : null;
    $user  = isset($param['user']) ? $param['user'] : null;

    $publications = Publication::find(null,$brand,$model,$year,$user);
    $results = [];
    foreach($publications as $publication) $results[] = $publication->toArray();
    $ws->result = $results;
    echo $ws->output($app);
});

$app->post("/publication/add",function() use($param,$app) {
    $ws = new \Core\Webservice(false);

    $model          = isset($param['model']) ? $param['model'] : null;
    $year           = isset($param['year']) ? $param['year'] : null;
    $condition      = isset($param['condition']) ? $param['condition'] : null;
    $description    = isset($param['description']) ? $param['description'] : null;
    $images         = isset($param['images']) ? $param['images'] : [];
    $user           = isset($param['user']) ? $param['user'] : null;
    $price           = isset($param['price']) ? $param['price'] : null;

    if ($model === null || !$model) $ws->generate_error(01,"El modelo es requerido");
    else if (!StringValidator::isInteger($model)) $ws->generate_error(01,"El modelo es inv&aacute;lido");
    else if ($year === null || !$year) $ws->generate_error(01,"El a&ntilde;o es requerido");
    else if (!StringValidator::isInteger($year)) $ws->generate_error(01,"El año es inv&aacute;lido");
    else if ($condition === null || !$condition) $ws->generate_error(01,"La condici&oacute; es requerida");
    else if ($description === null || !$description) $ws->generate_error(01,"La descripci&oacute; es requerida");
    else if ($price === null || !$price) $ws->generate_error(01,"El precio es requerido");
    else if (!StringValidator::isDecimal($price)) $ws->generate_error(01,"El precio es invalido. Solo numero es permitido");
    else if (!$model = ModelCar::findById($model)) $ws->generate_error(01,"Modelo no encontrado");
    else if (!$condition = Condition::findById($condition)) $ws->generate_error(01,"Condici&oacute; no encontrada");
    else if (!$user = User::findById($user)) $ws->generate_error(01,"Usuario no encontrado");

    if ($ws->error){
        echo $ws->output($app);
        return;
    }

    if ($images){
        if (is_array($images)){
            $count = 0;
            foreach($images as $image){
                ++$count;
                if (!PublicationImage::getImageBlob($image)) {
                    $ws->generate_error(01,"La imagen con posicion $count es inv&aacute;lida");
                    break;
                }
            }
        }else $ws->generate_error(01,"La imagenes son inv&aacute;lidas");
    }


    if ($ws->error){
        echo $ws->output($app);
        return;
    }

    $publication = new Publication();
    $publication->setModel($model);
    $publication->setYear($year);
    $publication->setPrice($price);
    $publication->setCondition($condition);
    $publication->setDescription($description);
    $publication->setImages($images);
    $publication->setUser($user);

    $imageToAdd = [];
    foreach($images as $image){
        $pubImg = new PublicationImage($publication,null,$image);
        $imageToAdd[] = $pubImg;
    }
    $publication->setImages($imageToAdd);
    if (!$publication->add()) $ws->generate_error(00,"Error agregando la publicaci&oacute;n");

    echo $ws->output($app);
});

$app->post("/register",function() use($param,$app) {
    $ws = new \Core\Webservice(false);

    $name       = isset($param['name']) ? $param['name'] : null;
    $email      = isset($param['email']) ? $param['email'] : null;
    $password   = isset($param['passwd']) ? $param['passwd'] : null;


    if ($email === null || !$email) $ws->generate_error(01,"El email es requerido");
    else if (!StringValidator::isEmail($email)) $ws->generate_error(01,"El email es inv&aacute;lido");
    else if ($name === null || !$name) $ws->generate_error(01,"El nombre es requerido");
    else if ($password === null || !$password) $ws->generate_error(01,"El password es requerido");
    else if (User::findByEmail($email)) $ws->generate_error(01,"Hay una cuenta creada con este email");

    if ($ws->error){
        echo $ws->output($app);
        return;
    }

    $user = new User();
    $user->setName($name);
    $user->setEmail($email);
    $user->setPassword($password);

    if (!$user->add()) $ws->generate_error(01,"No se pudo registrar el usuario");

    echo $ws->output($app);
});


$app->post("/login",function() use($param,$app) {
    $ws = new \Core\Webservice(false);

    $email      = isset($param['email']) ? $param['email'] : null;
    $password   = isset($param['passwd']) ? $param['passwd'] : null;
    $name     = isset($param['name']) ? $param['name'] : false;
    $openId     = isset($param['open_id']) ? $param['open_id'] : false;

    if ($email === null || !$email) $ws->generate_error(01,"El email es requerido");
    else if (!StringValidator::isEmail($email)) $ws->generate_error(01,"El email es inv&aacute;lido");
    else if (!$openId && ($password === null || !$password)) $ws->generate_error(01,"El password es inv&aacute;lido");
    else if (!$openId && !$user = User::findByEmail($email)) $ws->generate_error(01,"Usuario no encontrado");
    else if (!$openId && !$user->login($password)) $ws->generate_error(01,"Email o contrase&ntilde;a son incorrectos");
    else if ($openId && ($name === null || !$name)) $ws->generate_error(01,"El nombre es requerido");

    if ($ws->error){
        echo $ws->output($app);
        return;
    }

    if ($openId && !$user = User::findByEmail($email)){
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        if ($user->add())
            $user = User::findByEmail($email);
        else $ws->generate_error(01,"No se pudo registrar el usuario");
    }

    if (!$ws->error)
        $ws->result = $user->toArray();

    echo $ws->output($app);
});


$app->notFound(function () use($param,$app) {
    $ws = new Core\Webservice(false);
    $ws->generate_error(404,"Pagina no encontrada");
    echo $ws->output($app);
});

$app->error(function (\Exception $e) use($param,$app){
    $ws = new Core\Webservice(false);
    $ws->generate_error(500,"Error interno del servidor");
    echo $ws->output($app);
});


$app->run();
