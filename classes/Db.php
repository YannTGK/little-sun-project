<?php 
class Db{

    private static $conn;

    public static function getConnection(){

        //include_once(__DIR__ . "/../settings/settings.php");

        if(self::$conn === null){
            self::$conn = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');
            //self::$conn = new mysqli('localhost', 'root', 'root', 'littlesun');
            return self::$conn;
        }
            return self::$conn;
    }

}