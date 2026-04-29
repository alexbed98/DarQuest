<?php

class EnigmeDAL
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
    //Selectionne une enigme selon son id
    //-------------------------------------------------------------------------------
    public static function selectById(PDO $connexion, $enigmeId): array | false
    {

        $sql = "SELECT idEnigme, enonce, idCategorie, difficulte, estPigee
                FROM Enigmes
                WHERE idEnigme = :enigmeId;";


        $statement = $connexion->prepare($sql);
        $statement->bindValue(':enigmeId', $enigmeId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
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
        if (!$statement->execute()) {
            return false;
        }

        $count = $statement->fetchColumn();
        return $count !== false ? (string) $count : false;
    }

    //-------------------------------------------------------------------------------
    // Insere une nouvelle enigme, retourne le nouvel ID ou false si echec
    //-------------------------------------------------------------------------------
    public static function insertEnigme(PDO $connexion, string $enonce, ?string $idCategorie, string $difficulte, int $estPigee): int|false
    {
        $idCategorie = $idCategorie !== null ? strtoupper(trim($idCategorie)) : null;
        if ($idCategorie === '') {
            $idCategorie = null;
        }

        // Evite la violation FK: la categorie doit exister si elle est fournie.
        if ($idCategorie !== null) {
            $catStmt = $connexion->prepare("SELECT COUNT(*) FROM Categories WHERE idCategorie = :idCategorie");
            $catStmt->bindValue(':idCategorie', $idCategorie, PDO::PARAM_STR);
            if (!$catStmt->execute() || (int) $catStmt->fetchColumn() === 0) {
                return false;
            }
        }

        // idEnigme is not auto-generated in this schema, compute the next id manually.
        $idStmt = $connexion->prepare("SELECT COALESCE(MAX(idEnigme), 0) + 1 FROM Enigmes");
        if (!$idStmt->execute()) {
            return false;
        }
        $nextId = (int) $idStmt->fetchColumn();

        $sql = "INSERT INTO Enigmes (idEnigme, enonce, idCategorie, difficulte, estPigee)
                VALUES (:idEnigme, :enonce, :idCategorie, :difficulte, :estPigee)";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':idEnigme', $nextId, PDO::PARAM_INT);
        $statement->bindValue(':enonce', $enonce, PDO::PARAM_STR);
        $statement->bindValue(':idCategorie', $idCategorie, $idCategorie !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $statement->bindValue(':difficulte', $difficulte, PDO::PARAM_STR);
        $statement->bindValue(':estPigee', $estPigee, PDO::PARAM_INT);

        try {
            if ($statement->execute()) {
                return $nextId;
            }
        } catch (PDOException $e) {
            return false;
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

// Backward compatibility with existing calls using the old typo'ed class name.
class EnigneDAL extends EnigmeDAL
{
}