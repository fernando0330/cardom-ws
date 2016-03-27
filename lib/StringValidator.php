<?php
class StringValidator{
    /**
     * Method to validate the input is locale (Ex.: EN-US)
     */
    public static function isLocale($input){
        $pattern = "/^[A-Z]{2}\-[A-Z]{2}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is integer
     */
    public static function isInteger($input){
        $pattern = "/^[0-9]+$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is integer
     */
    public static function isDecimal($input){
        $pattern = "/^(\-)*[0-9]+(\.[0-9]+)*$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is id of the simcard (19 digits)
     */
    public static function isIccid($input,$prefix = "8957"){
        $lenPrefix = strlen($prefix);
        $totalValidate = 19;
        $lenValidate = $totalValidate - $lenPrefix;

        if (self::isIccidWithoutPrefix($input,$prefix))
            $input = $prefix . $input;
        else if ($prefix == null){
            $prefix = substr($input,0,$lenPrefix);
        }

        $pattern = "/^{$prefix}[0-9]{{$lenValidate}}$/";
        $input = preg_match($pattern,$input) ? $input : null;
        return $input;
    }

    /**
     * Method to validate if the input is id of the simcard (19 digits)
     */
    public static function isIccidWithoutPrefix($input,$prefix = "8957"){
        $totalLen = 19;
        $count = strlen($prefix);
        $count = $totalLen - $count;
        $pattern = "/^[0-9]{{$count}}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is zipcode (33166)
     */
    public static function isZipCode($input){
        $pattern = "/^[0-9]{5}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is cvv code (032)
     */
    public static function isCvvCode($input){
        $pattern = "/^[0-9]{3,4}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is a date (MM-DD-YYYY)
     */
    public static function isDate($input){
        $pattern = "/^[0-9]{2}\-[0-9]{2}\-[0-9]{4}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to verify if the input is a alphanumeric
     */
    public static function isAlphanumeric($input){
        $pattern = "/^[A-Za-z0-9\_\-\ ]+$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is a country iso 2. EX.: US (United States)
     */
    public static function isCountryIso2($input){
        $pattern = "/^[A-Za-z]{2}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate the input is email
     */
    public static function isEmail($input = null){
        return filter_var($input, FILTER_VALIDATE_EMAIL);;
    }

    /**
     * Method to validate if the input is a base64 encoded
     */
    public static function isBase64($input = null){
        return base64_encode(base64_decode($input)) === $input;
    }

    /**
     * Method to validate if the input is month number, Ex.: 02
     */
    public static function isMonthNumber($input = null){
        $pattern = "/^[0-1]{1}[0-9]{1}$/";
        return preg_match($pattern,$input);
    }


    /**
     * Method to validate if the input is a card number
     */
    public static function isYear($input = null){
        $pattern = "/^[0-9]{4}$/";
        return preg_match($pattern,$input);
    }

    /**
     * Method to validate if the input is a card number
     */
    public static function isCardNumber($input = null){
        $pattern = "/^[0-9]{13,20}$/";
        return preg_match($pattern,$input);
    }


    /**
     * Method to validate if the input is a json
     */
    function isJson($input) {
        return is_string($input) && is_object(json_decode($input)) ? true : false;
    }

    /**
     * Method to validate the input is a valid password
     */
    public static function isPassword($input){
        $pattern = "/^.{6,}$/";
        return preg_match($pattern,$input);
    }


    public static function isDomain($input){
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $input) //valid chars check
            && preg_match("/^.{1,253}$/", $input) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $input)   ); //length of each label
    }
}