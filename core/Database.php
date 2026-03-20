<?php

class Database
{
    public static function getConnexion(array $dbConfig): PDO {
        
        try {
        
            return new PDO("mysql:host=".$dbConfig["dbHost"]."port=3307".";dbname=".$dbConfig["dbName"], $dbConfig["dbUser"], $dbConfig["dbPass"], $dbConfig["dbParams"]);
        
        } catch(PDOException $e) {

            throw new PDOException($e->getMessage(), $e->getCode());

        }        

    }

}