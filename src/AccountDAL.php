<?php

class AccountDAL
{
    public static function selectByEmail(PDO $connexion, string $email): false|array {

        $sql = "SELECT id, password, role, username from account where email=:email";

        $statement = $connexion->prepare($sql); 

        $statement->bindValue('email', $email, PDO::PARAM_STR);
             
        $statement->execute();

        // fetch retourne 'false' si aucune donnée
        return $statement->fetch();

    }

     public static function insertOne(PDO $connexion, string $email, string $password, string $username, string $prenom, string $nom): bool {

        $sql = "insert into account (email, password, username, prenom, nom) values(:email, :password, :username, :prenom, :nom)";

        $statement = $connexion->prepare($sql); 

        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->bindValue('password', $password, PDO::PARAM_STR);
        $statement->bindValue('username', $username, PDO::PARAM_STR);
        $statement->bindValue('prenom', $prenom, PDO::PARAM_STR);
        $statement->bindValue('nom', $nom, PDO::PARAM_STR);
             
        return $statement->execute();

    }
    
    
}





