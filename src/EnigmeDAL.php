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

    //-------------------------------------------------------------------------------
    // Insere une nouvelle enigme, retourne le nouvel ID ou false si echec
    //-------------------------------------------------------------------------------
    public static function insertEnigme(PDO $connexion, string $enonce, ?string $idCategorie, string $difficulte, int $estPigee): int|false
    {
        $sql = "INSERT INTO Enigmes (enonce, idCategorie, difficulte, estPigee)
                VALUES (:enonce, :idCategorie, :difficulte, :estPigee)";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':enonce', $enonce, PDO::PARAM_STR);
        $statement->bindValue(':idCategorie', $idCategorie, $idCategorie !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':difficulte', $difficulte, PDO::PARAM_STR);
        $statement->bindValue(':estPigee', $estPigee, PDO::PARAM_INT);

        if ($statement->execute()) {
            return (int) $connexion->lastInsertId();
        }
        return false;
    }

    //-------------------------------------------------------------------------------
    // Insere une reponse liee a une enigme
    //-------------------------------------------------------------------------------
    public static function insertReponse(PDO $connexion, string $reponse, int $estBonneReponse, int $idEnigme): bool
    {
        $sql = "INSERT INTO Reponses (reponse, estBonneReponse, idEnigme)
                VALUES (:reponse, :estBonneReponse, :idEnigme)";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':reponse', $reponse, PDO::PARAM_STR);
        $statement->bindValue(':estBonneReponse', $estBonneReponse, PDO::PARAM_INT);
        $statement->bindValue(':idEnigme', $idEnigme, PDO::PARAM_INT);

        return $statement->execute();
    }

}