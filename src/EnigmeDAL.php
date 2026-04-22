<?php

class EnigneDAL
{
    //-------------------------------------------------------------------------------
    //Selectionne tout les enigmes et choisisez un aleatoirement
    //(return false s'il n'y a pas d'enigme choisi)
    //-------------------------------------------------------------------------------
    public static function selectRandomEnigme(PDO $connexion): array | false
    {

        $sql = "SELECT idEnigme, enonce, idCategorie, difficulte, estPigee 
                FROM Enigmes;";

        $statement = $connexion->prepare($sql);
        $statement->execute();

        $randomEnigme = $statement->fetchAll();
        if (empty($randomEnigme)) {
            return false;
        }
        $randIndex = array_rand($randomEnigme);

        return $randomEnigme[$randIndex];
    }
    //-------------------------------------------------------------------------------
    //Selectionne tout les reponses selon l'id de l'enigme
    //(return false s'il n'y a pas de reponses pour l'enigme)
    //-------------------------------------------------------------------------------
    public static function selectAllAnswers(PDO $connexion, $enigmeId): array | false
    {

        $sql = "SELECT idReponse, estBonneReponse, reponse, idEnigme 
                FROM Reponses
                WHERE idEnigme = :enigmeId;";


        $statement = $connexion->prepare($sql);
        $statement->bindValue(':enigmeId', $enigmeId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
    //-------------------------------------------------------------------------------
    //Compte tout les enigmes
    //(return false s'il n'y a pas d'enigmes)
    //-------------------------------------------------------------------------------
    public static function countAllEnigme(PDO $connexion): false | string
    {

        $sql = "SELECT COUNT(*) 
                FROM Enigmes;";

        $statement = $connexion->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }
}