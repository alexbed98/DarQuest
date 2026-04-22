<?php

class AccountDAL
{
    public static function selectByEmail(PDO $connexion, string $email): false|array
    {

        $sql = "SELECT idJoueur, alias, prenom, nom, motDePasse, gold, argent, bronze, estAdmin, estMage, avatar from Joueurs where courriel=:email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('email', $email, PDO::PARAM_STR);

        $statement->execute();

        // fetch retourne 'false' si aucune donnée
        return $statement->fetch();

    }

    public static function updateJoueur(PDO $connexion, int $idJoueur, string $username, string $prenom, string $nom, 
                            string $email, string $finalPassword, string $avatarFinal ): bool 
    {

        $sql = "UPDATE Joueurs 
            SET alias = :alias,
                prenom = :prenom,
                nom = :nom,
                courriel = :email,
                motDePasse = :finalPassword,
                avatar = :avatarFinal
            WHERE idJoueur = :idJoueur";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->bindValue(':alias', $username, PDO::PARAM_STR);
        $statement->bindValue(':prenom', $prenom, PDO::PARAM_STR);
        $statement->bindValue(':nom', $nom, PDO::PARAM_STR);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);
        $statement->bindValue(':finalPassword', $finalPassword, PDO::PARAM_STR);
        $statement->bindValue(':avatarFinal', $avatarFinal, PDO::PARAM_STR);        

        return $statement->execute();
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
    //-------------------------------------------------------------------------------
    //Select le nombre d'or d'un joueur selon son email
    //-------------------------------------------------------------------------------
    public static function selectGold(PDO $connexion, string $email): false|string
    {

        $sql = "SELECT gold from joueurs where courriel=:email";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('email', $email, PDO::PARAM_STR);

        $statement->execute();

        return $statement->fetchColumn();

    }
    //-------------------------------------------------------------------------------
    //Ajouter 100 pièces de bronze/argent/or selon la difficulter au joueur avec
    //l'email correspondant
    //Il retourn une phrase pour indiquer au joueur ce qu'il a gagné
    //-------------------------------------------------------------------------------
    public static function addReward(PDO $connexion, string $email, string $difficulte): string
    {
        $sql = null;
        $piece = '';
        switch ($difficulte) {
            case 'F':
                $sql = "UPDATE Joueurs SET bronze = bronze + 100 WHERE courriel=:email";
                $piece = 'de bronze';
                break;
            case 'M':
                $sql = "UPDATE Joueurs SET argent = argent + 100 WHERE courriel=:email";
                $piece = 'd\'argent';
                break;
            case 'D':
                $sql = "UPDATE Joueurs SET gold = gold + 100 WHERE courriel=:email";
                $piece = 'd\'or';
                break;
            default:
                return 'Difficulter non reconnu';
        }

        if ($sql != null) {

            $statement = $connexion->prepare($sql);

            $statement->bindValue('email', $email, PDO::PARAM_STR);

            $statement->execute();
        }
        return 'Vous aviez gagné 100 pièces ' . $piece . "!";
    }
    //-------------------------------------------------------------------------------
    //Enleve l'hp du joueur avec l'email correspondant selon la difficulter 
    //Il retourn une phrase pour indiquer au joueur ce qu'il a perdu
    //-------------------------------------------------------------------------------
    public static function takeDamage(PDO $connexion, string $email, string $difficulte): string
    {
        $sql = null;
        $hp = '';
        switch ($difficulte) {
            case 'F':
                $sql = "UPDATE Joueurs SET pointVie = pointVie - 3 WHERE courriel=:email";
                $hp = '3';
                break;
            case 'M':
                $sql = "UPDATE Joueurs SET pointVie = pointVie - 6 WHERE courriel=:email";
                $hp = '6';
                break;
            case 'D':
                $sql = "UPDATE Joueurs SET pointVie = pointVie - 10 WHERE courriel=:email";
                $hp = '10';
                break;
            default:
                return 'Difficulter non reconnu';
        }

        if ($sql != null) {

            $statement = $connexion->prepare($sql);

            $statement->bindValue('email', $email, PDO::PARAM_STR);

            $statement->execute();
        }
        return 'Vous aviez perdu  ' . $hp . 'HP!';
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





