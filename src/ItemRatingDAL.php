<?php

class ItemRatingDAL
{
    //-------------------------------------------------------------------------------
    // Cree la table de notes si elle n'existe pas.
    //-------------------------------------------------------------------------------
    public static function ensureTable(PDO $connexion): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS ItemEvaluations (
                    idEvaluation INT AUTO_INCREMENT PRIMARY KEY,
                    idItem INT NOT NULL,
                    idJoueur INT NOT NULL,
                    note TINYINT UNSIGNED NOT NULL,
                    dateEvaluation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_item_joueur (idItem, idJoueur),
                    INDEX idx_item (idItem),
                    INDEX idx_joueur (idJoueur)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $connexion->exec($sql);
    }

    //-------------------------------------------------------------------------------
    // Insere ou met a jour la note d'un joueur pour un item.
    //-------------------------------------------------------------------------------
    public static function upsertRating(PDO $connexion, int $idItem, int $idJoueur, int $note): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        self::ensureTable($connexion);

        $sql = "INSERT INTO ItemEvaluations (idItem, idJoueur, note)
                VALUES (:idItem, :idJoueur, :note)
                ON DUPLICATE KEY UPDATE note = VALUES(note), dateEvaluation = CURRENT_TIMESTAMP";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $statement->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->bindValue(':note', $note, PDO::PARAM_INT);

        return $statement->execute();
    }

    //-------------------------------------------------------------------------------
    // Retourne la moyenne et le nombre total d'evaluations d'un item.
    //-------------------------------------------------------------------------------
    public static function getItemAverage(PDO $connexion, int $idItem): array
    {
        self::ensureTable($connexion);

        $sql = "SELECT AVG(note) AS moyenne, COUNT(*) AS total
                FROM ItemEvaluations
                WHERE idItem = :idItem";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return [
            'moyenne' => isset($result['moyenne']) ? (float) $result['moyenne'] : 0.0,
            'total' => isset($result['total']) ? (int) $result['total'] : 0,
        ];
    }

    //-------------------------------------------------------------------------------
    // Retourne la note du joueur pour un item, ou null s'il n'a pas note.
    //-------------------------------------------------------------------------------
    public static function getUserRating(PDO $connexion, int $idItem, int $idJoueur): ?int
    {
        self::ensureTable($connexion);

        $sql = "SELECT note
                FROM ItemEvaluations
                WHERE idItem = :idItem AND idJoueur = :idJoueur";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $statement->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->execute();

        $note = $statement->fetchColumn();
        return $note !== false ? (int) $note : null;
    }
}
