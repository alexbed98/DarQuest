<?php

class Database
{
    public static function getConnexion(array $dbConfig): PDO {
        
        try {
<<<<<<< Choix-Quete
         return new PDO("mysql:host=".$dbConfig["dbHost"].";port=3307;dbname=".$dbConfig["dbName"], $dbConfig["dbUser"], $dbConfig["dbPass"], $dbConfig["dbParams"]);
//            return new PDO("mysql:host=".$dbConfig["dbHost"].";port=3306;dbname=".$dbConfig["dbName"], $dbConfig["dbUser"], $dbConfig["dbPass"], $dbConfig["dbParams"]);
=======
        
            // pour version en ligne 
            return new PDO("mysql:host=".$dbConfig["dbHost"].";port=3306;dbname=".$dbConfig["dbName"], $dbConfig["dbUser"], $dbConfig["dbPass"], $dbConfig["dbParams"]);
            
            // pour version en local
           //return new PDO("mysql:host=".$dbConfig["dbHost"].";port=3307;dbname=".$dbConfig["dbName"], $dbConfig["dbUser"], $dbConfig["dbPass"], $dbConfig["dbParams"]);
>>>>>>> main
        
        } catch(PDOException $e) {

            throw new PDOException($e->getMessage(), $e->getCode());

        }        

    }

}