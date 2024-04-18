<?php 
class Db{

    private static $conn;

    public static function getConnection(){

        include_once(__DIR__ . "/../settings/settings.php");

        if(self::$conn === null){
            self::$conn = new mysqli(SETTINGS['db']['host'], SETTINGS['db']['user'], SETTINGS['db']['password'], SETTINGS['db']['database']);
            //self::$conn = new mysqli('localhost', 'root', 'root', 'littlesun');
            return self::$conn;
        }
            return self::$conn;
    }

}