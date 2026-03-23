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

     public static function insertOne(PDO $connexion,string $username, string $prenom, string $nom, string $email, string $password, int $estMage = 0, int $estAdmin = 0): bool {

        $sql = "insert into Joueurs (alias, prenom, nom, estMage, courriel, motDePasse, estAdmin) values(:username, :prenom, :nom, :estMage, :email, :password, :estAdmin)";

        $statement = $connexion->prepare($sql); 

        $statement->bindValue('username', $username, PDO::PARAM_STR);
        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->bindValue('prenom', $prenom, PDO::PARAM_STR);
        $statement->bindValue('nom', $nom, PDO::PARAM_STR);
        $statement->bindValue('estMage', $estMage, PDO::PARAM_INT);
        $statement->bindValue('password', $password, PDO::PARAM_STR);
        $statement->bindValue('email', $email, PDO::PARAM_STR);
        $statement->bindValue('estAdmin', $estAdmin, PDO::PARAM_INT);
             
        return $statement->execute();

    }
    
    
}





