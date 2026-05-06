<?php

class EnigmeDAL
{
    public static $currentDif = '';

    //-------------------------------------------------------------------------------
    // Cree la table de demandes si elle n'existe pas.
    //-------------------------------------------------------------------------------
    private static function ensureDemandesTable(PDO $connexion): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS Demandes (
                    idDemande INT AUTO_INCREMENT PRIMARY KEY,
                    idJoueur INT NOT NULL,
                    accepter TINYINT NOT NULL DEFAULT 0,
                    dateDemande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_demandes_joueur (idJoueur),
                    INDEX idx_demandes_statut (accepter)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $connexion->exec($sql);
    }
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


    //-------------------------------------------------------------------------------
    // Selectionne toutes les enigmes avec leur statut de disponibilite (pour l'admin)
    //-------------------------------------------------------------------------------
    public static function selectPourPublication(PDO $connexion): array
    {
        $sql = "SELECT idEnigme, enonce, difficulte, estDisponible
                FROM Enigmes
                ORDER BY idEnigme ASC";

        $statement = $connexion->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    //-------------------------------------------------------------------------------
    // Change le statut de disponibilite d'une enigme (sans suppression BD)
    //-------------------------------------------------------------------------------
    public static function setDisponibilite(PDO $connexion, int $idEnigme, bool $estDisponible): bool
    {
        // Ajoute la colonne si elle n'existe pas
        $connexion->exec("
            ALTER TABLE Enigmes
            ADD COLUMN IF NOT EXISTS estDisponible TINYINT(1) NOT NULL DEFAULT 1
        ");

        $sql = "UPDATE Enigmes SET estDisponible = :estDisponible WHERE idEnigme = :idEnigme";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':estDisponible', $estDisponible ? 1 : 0, PDO::PARAM_INT);
        $statement->bindValue(':idEnigme', $idEnigme, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public static function countDemandesByJoueur($connexion, $idJoueur) {
        self::ensureDemandesTable($connexion);

        $sql = "SELECT COUNT(*) as total FROM Demandes WHERE idJoueur = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->execute([$idJoueur]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public static function insertDemande($connexion, $idJoueur) {
        self::ensureDemandesTable($connexion);

        $sql = "INSERT INTO Demandes (idJoueur, accepter) VALUES (?, 0)";
        $stmt = $connexion->prepare($sql);
        $stmt->execute([$idJoueur]);
    }

    public static function accepterDemande(PDO $connexion, int $idDemande) {
        self::ensureDemandesTable($connexion);

        $sql = "SELECT idJoueur FROM Demandes WHERE idDemande = ?";
        $stmt = $connexion->prepare($sql);
        $stmt->execute([$idDemande]);
        $demande = $stmt->fetch();

        if (!$demande) return;

        $idJoueur = $demande['idJoueur'];

        $sql = "SELECT COUNT(*) FROM Demandes WHERE accepter = 1";
        $count = $connexion->query($sql)->fetchColumn();

        if ($count == 0) {
            $connexion->prepare("UPDATE Joueurs SET gold = gold + 100 WHERE idJoueur = ?")
                    ->execute([$idJoueur]);

        } elseif ($count == 1) {
            $connexion->prepare("UPDATE Joueurs SET argent = argent + 100 WHERE idJoueur = ?")
                    ->execute([$idJoueur]);

        } else {
            $connexion->prepare("UPDATE Joueurs SET bronze = bronze + 100 WHERE idJoueur = ?")
                    ->execute([$idJoueur]);
        }
        
        $connexion->prepare("UPDATE Demandes SET accepter = 1 WHERE idDemande = ?")
                ->execute([$idDemande]);
    }

    public static function selectDemandesEnAttente(PDO $connexion) {
        self::ensureDemandesTable($connexion);

        $sql = "
            SELECT d.idDemande, j.alias, j.idJoueur
            FROM Demandes d
            LEFT JOIN Joueurs j ON j.idJoueur = d.idJoueur
            WHERE d.accepter = 0
            ORDER BY d.idDemande DESC
        ";

        $stmt = $connexion->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public static function refuserDemande(PDO $connexion, int $idDemande) {
        self::ensureDemandesTable($connexion);

        $sql = "UPDATE Demandes SET accepter = -1 WHERE idDemande = ?";
        $connexion->prepare($sql)->execute([$idDemande]);
    }

}

// Backward compatibility with existing calls using the old typo'ed class name.
class EnigneDAL extends EnigmeDAL
{
}
