<?php

class EnigneDAL
{
    public static function selectRandomEnigme(PDO $connexion): array
    {

        $sql = "SELECT idEnigme, enonce, idCategorie, difficulte, estPigee 
                FROM Enigmes;";

        $statement = $connexion->prepare($sql);
        $statement->execute();

        $randomEnigme = $statement->fetchAll();
        if (empty($randomEnigme)) {
            return [];
        }
        return $randomEnigme[array_rand($randomEnigme)];
    }
    public static function selectAllAnswers(PDO $connexion, $enigmeId): array
    {

        $sql = "SELECT idReponse, estBonneReponse, reponse, idEnigme 
                FROM Reponses
                WHERE idEnigme = :enigmeId;";


        $statement = $connexion->prepare($sql);
        $statement->bindValue(':enigmeId', $enigmeId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

}