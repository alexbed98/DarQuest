<?php

class AccountDAL
{
    public static function selectByEmail(PDO $connexion, string $email): false|array
    {

        $sql = "SELECT idJoueur, motDePasse, estAdmin from Joueurs where courriel=:email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('email', $email, PDO::PARAM_STR);

        $statement->execute();

        // fetch retourne 'false' si aucune donnée
        return $statement->fetch();

    }

    public static function insertOne(PDO $connexion, string $username, string $prenom, string $nom, string $courriel, string $motDePasse, int $estMage = 0, int $estAdmin = 0): bool
    {

        $sql = "insert into Joueurs (alias, prenom, nom, estMage, courriel, motDePasse, estAdmin) values(:username, :prenom, :nom, :estMage, :courriel, :motDePasse, :estAdmin)";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('username', $username, PDO::PARAM_STR);
        $statement->bindValue('prenom', $prenom, PDO::PARAM_STR);
        $statement->bindValue('nom', $nom, PDO::PARAM_STR);
        $statement->bindValue('courriel', $courriel, PDO::PARAM_STR);
        $statement->bindValue('motDePasse', $motDePasse, PDO::PARAM_STR);
        $statement->bindValue('estMage', $estMage, PDO::PARAM_INT);
        $statement->bindValue('estAdmin', $estAdmin, PDO::PARAM_INT);

        return $statement->execute();

    }

    public static function selectAlias(PDO $connexion, string $email): false|string
    {

        $sql = "SELECT alias from joueurs where courriel=:email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('email', $email, PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetchColumn();

    }
    public static function selectGold(PDO $connexion, string $email): false|string
    {

        $sql = "SELECT gold from joueurs where courriel=:email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('email', $email, PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetchColumn();

    }
    public static function addReward(PDO $connexion, string $email, string $difficulte): false|string
    {
        $sql = null;
        switch ($difficulte) {
            case 'F':
                $sql = "UPDATE Joueurs SET bronze = bronze + 100 WHERE courriel=:email";
                break;
            case 'M':
                $sql = "UPDATE Joueurs SET argent = argent + 100 WHERE courriel=:email";
                break;
            case 'D':
                $sql = "UPDATE Joueurs SET gold = gold + 100 WHERE courriel=:email";
                break;
        }

        if ($sql != null) {

            $statement = $connexion->prepare($sql);

            $statement->bindValue('email', $email, PDO::PARAM_STR);

            
            return $statement->execute();
        }
        return false;
    }

    public static function courrielExistant(PDO $connexion, string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM joueurs WHERE courriel = :email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':email', $email, PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetchColumn() > 0;
    }
}





