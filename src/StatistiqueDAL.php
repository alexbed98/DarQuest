<?php
class StatistiqueDAL
{
    //-------------------------------------------------------------------------------
    // Cree la table de statistiques si elle n'existe pas.
    //-------------------------------------------------------------------------------
    private static function ensureStatistiquesTable(PDO $connexion): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS Statistiques (
                    idJoueur INT NOT NULL,
                    idEnigme INT NOT NULL,
                    estReussie TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (idJoueur, idEnigme),
                    INDEX idx_stats_reussite (estReussie)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $connexion->exec($sql);
    }

    //-------------------------------------------------------------------------------
    //Procedure pour ajouter ou mettre a jour le stats d'un joueur 
    //-------------------------------------------------------------------------------
    public static function updateStatistique(PDO $connexion, string $email, int $idEnigme, int $estReussie): void
    {
        self::ensureStatistiquesTable($connexion);

        $idJoueur = (int) AccountDAL::selectByEmail($connexion, $email)['idJoueur'];

        $selectSql = "SELECT estReussie FROM Statistiques WHERE idJoueur = :idJoueur AND idEnigme = :idEnigme";
        $selectStmt = $connexion->prepare($selectSql);
        $selectStmt->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $selectStmt->bindValue('idEnigme', $idEnigme, PDO::PARAM_INT);
        $selectStmt->execute();

        $existing = $selectStmt->fetchColumn();

        if ($existing === false) {
            $insertSql = "INSERT INTO Statistiques (idJoueur, idEnigme, estReussie) VALUES (:idJoueur, :idEnigme, :estReussie)";
            $insertStmt = $connexion->prepare($insertSql);
            $insertStmt->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
            $insertStmt->bindValue('idEnigme', $idEnigme, PDO::PARAM_INT);
            $insertStmt->bindValue('estReussie', $estReussie, PDO::PARAM_INT);
            $insertStmt->execute();
            return;
        }

        // Une enigme reussie une fois reste marquee reussie.
        $finalStat = max((int) $existing, $estReussie);

        $updateSql = "UPDATE Statistiques SET estReussie = :estReussie WHERE idJoueur = :idJoueur AND idEnigme = :idEnigme";
        $updateStmt = $connexion->prepare($updateSql);
        $updateStmt->bindValue('estReussie', $finalStat, PDO::PARAM_INT);
        $updateStmt->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $updateStmt->bindValue('idEnigme', $idEnigme, PDO::PARAM_INT);
        $updateStmt->execute();
    }
    //-------------------------------------------------------------------------------
    //Fonction pour voir si le joueur a deja fait l'enigme
    //-------------------------------------------------------------------------------
    public static function alreadyInStats(PDO $connexion, string $email, string $idEnigme): bool
    {
        self::ensureStatistiquesTable($connexion);

        $sql = null;
        $idJoueur = AccountDAL::selectByEmail($connexion, $email)['idJoueur'];
        //$catEnigme = EnigneDAL::selectById($connexion, $idEnigme)['idCategorie'];

        $sql = "SELECT idJoueur,idEnigme FROM Statistiques WHERE idJoueur = :idJoueur AND idEnigme = :idEnigme;";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->bindValue('idEnigme', $idEnigme, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetchAll();

        if (!empty($result))
            return true;
        else
            return false;
    }
    //-------------------------------------------------------------------------------
    //Compte tout les enigmes de categorie magie qui ont ete reussi par le joueur
    //-------------------------------------------------------------------------------
    public static function selectAllSuccesfulMagicQuestions(PDO $connexion, string $email): string | bool
    {
        self::ensureStatistiquesTable($connexion);

        $sql = null;
        $idJoueur = AccountDAL::selectByEmail($connexion, $email)['idJoueur'];
        $idCategoryMagie = CategoryDAL::selectMagicId($connexion);

        $sql = "SELECT COUNT(*) FROM Statistiques s inner join Enigmes e on s.idEnigme = e.idEnigme WHERE idJoueur = :idJoueur AND idCategorie = :idCategorie AND estReussie = 1;";

        $statement = $connexion->prepare($sql);

        $statement->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->bindValue('idCategorie', $idCategoryMagie, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetchColumn();

        if (!empty($result))
            return $result;
        else
            return false;
    }

    //-------------------------------------------------------------------------------
    // Compte toutes les questions deja tentees par le joueur.
    //-------------------------------------------------------------------------------
    public static function countAnsweredQuestions(PDO $connexion, string $email): int
    {
        self::ensureStatistiquesTable($connexion);

        $idJoueur = (int) AccountDAL::selectByEmail($connexion, $email)['idJoueur'];
        $sql = "SELECT COUNT(*) FROM Statistiques WHERE idJoueur = :idJoueur";
        $statement = $connexion->prepare($sql);
        $statement->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    //-------------------------------------------------------------------------------
    // Compte toutes les questions reussies par le joueur.
    //-------------------------------------------------------------------------------
    public static function countSuccessfulQuestions(PDO $connexion, string $email): int
    {
        self::ensureStatistiquesTable($connexion);

        $idJoueur = (int) AccountDAL::selectByEmail($connexion, $email)['idJoueur'];
        $sql = "SELECT COUNT(*) FROM Statistiques WHERE idJoueur = :idJoueur AND estReussie = 1";
        $statement = $connexion->prepare($sql);
        $statement->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }
}