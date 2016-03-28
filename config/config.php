<?php
namespace Config;

class Config{
    const DIR_TEMP = "tmp/";

    const DIR_RES_IMG_PUBLICATIONS = "public/resources/images/publications/";

    public static $db_host = "localhost";
    public static $db_port = "3301";
    public static $db_name = "cardomdb";
    public static $db_prefTable = "";
    public static $db_user = "cardomuser";
    public static $db_passwd = "cardom2016";

    public static $formatDate = "d-m-Y";

    public static $projectName = "Cardom";

    public static $smtp_host  		    = "smtp.gmail.com";
    public static $smtp_username 	    = "fernandoperez0330@gmail.com";
    public static $smtp_fullname 	    = "Cardom";
    public static $smtp_passwd 		    = "FernGmail2014";
    public static $smtp_debug 		    = 0;
    public static $smtp_secure		    = "tls"; //ssl also is accepted
    public static $smtp_port		    = 587;


}
date_default_timezone_set("America/Santo_Domingo");