<?php

class AccountDAL
{
    public static function selectByEmail(PDO $connexion, string $email): false|array {

        $sql = "SELECT id, password, role from account where email=:email";

        $statement = $connexion->prepare($sql); 

        $statement->bindValue('email', $email, PDO::PARAM_STR);
             
        $statement->execute();

        // fetch retourne 'false' si aucune donnée
        return $statement->fetch();

    }

     public static function insertOne(PDO $connexion, string $email, string $password): bool {

        $sql = "insert into account (email, password) values(:email, :password)";

        $statement = $connexion->prepare($sql); 

        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->bindValue('password', $password, PDO::PARAM_STR);
             
        return $statement->execute();

    }
    
    
}





