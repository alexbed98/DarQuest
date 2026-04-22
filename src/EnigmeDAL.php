<?php

class EnigneDAL
{
public static $currentDif = '';
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

        //self::$currentDif = $randomEnigme[$randIndex]['difficulte'];

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