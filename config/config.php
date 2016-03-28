<?php
namespace Config;

class Config{
    const DIR_TEMP = "tmp/";

    const DIR_RES_IMG_PUBLICATIONS = "public/resources/images/publications/";
    const DIR_RES_QR_PUBLICATIONS = "public/resources/qrcode/publications/";

    public static $db_host = "localhost";
    public static $db_port = "3301";
    public static $db_name = "cardomdb";
    public static $db_prefTable = "";
    public static $db_user = "cardomuser";
    public static $db_passwd = "cardom2016";

    public static $formatDate = "d-m-Y";

    public static $projectName = "Cardom";

    public static $smtp_host  		    = "smtp.gmail.com";
    public static $smtp_username 	    = "fperez@aiplatform.net";
    public static $smtp_fullname 	    = "Cardom";
    public static $smtp_passwd 		    = "Fernaipc2014";
    public static $smtp_debug 		    = 0;
    public static $smtp_secure		    = "tls"; //ssl also is accepted
    public static $smtp_port		    = 587;

    public static $email_templ_header   = "";
    public static $email_templ_footer   = "";


}
date_default_timezone_set("America/Santo_Domingo");