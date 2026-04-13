<?php

class InventaireDAL
{
    /**
     * Retourne tous les items de l'inventaire d'un joueur avec les infos de l'item.
     */
    public static function getInventaire(PDO $pdo, int $idJoueur): array
    {
        $sql = "SELECT i.idItem, i.nom, i.prix, i.photo, i.typeItem, inv.quantiteInventaire
                FROM inventaires inv
                JOIN items i ON i.idItem = inv.idItem
                WHERE inv.idJoueur = :idJoueur
                ORDER BY i.nom ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Traite la commande : déduit l'or, ajoute les items à l'inventaire.
     * Retourne true si succès, false si le joueur n'a pas assez d'or.
     */
    public static function commander(PDO $pdo, int $idJoueur, array $panier): bool
    {
        // Calculer le total
        $total = 0;
        foreach ($panier as $item) {
            $total += (float) $item['prix'] * (int) $item['quantite'];
        }

        // Vérifier que le joueur a assez d'or
        $stmt = $pdo->prepare("SELECT gold FROM joueurs WHERE idJoueur = :id");
        $stmt->bindValue(':id', $idJoueur, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row === false || (int) $row['gold'] < $total) {
            return false;
        }

        // Déduire l'or
        $upd = $pdo->prepare("UPDATE joueurs SET gold = gold - :total WHERE idJoueur = :id");
        $upd->bindValue(':total', (int) $total, PDO::PARAM_INT);
        $upd->bindValue(':id', $idJoueur, PDO::PARAM_INT);
        $upd->execute();

        // Ajouter chaque item à l'inventaire (INSERT ou UPDATE si déjà présent)
        $ins = $pdo->prepare(
            "INSERT INTO inventaires (idJoueur, idItem, quantiteInventaire)
             VALUES (:idJoueur, :idItem, :qte)
             ON DUPLICATE KEY UPDATE quantiteInventaire = quantiteInventaire + :qte2"
        );

        foreach ($panier as $item) {
            $ins->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $ins->bindValue(':idItem', (int) $item['id'], PDO::PARAM_INT);
            $ins->bindValue(':qte', (int) $item['quantite'], PDO::PARAM_INT);
            $ins->bindValue(':qte2', (int) $item['quantite'], PDO::PARAM_INT);
            $ins->execute();
        }

        return true;
    }

    /**
     * Vend une quantité d'un item : retire de l'inventaire et ajoute de l'or au joueur.
     * Retourne false si la quantité en inventaire est insuffisante.
     */
    public static function vendre(PDO $pdo, int $idJoueur, int $idItem, int $quantite): bool
    {
        // Vérifier la quantité disponible
        $check = $pdo->prepare(
            "SELECT quantiteInventaire FROM inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
        );
        $check->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $check->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if ($row === false || (int) $row['quantiteInventaire'] < $quantite) {
            return false;
        }

        $nouvelleQte = (int) $row['quantiteInventaire'] - $quantite;

        if ($nouvelleQte === 0) {
            $del = $pdo->prepare(
                "DELETE FROM inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
            );
            $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $del->bindValue(':idItem', $idItem, PDO::PARAM_INT);
            $del->execute();
        } else {
            $upd = $pdo->prepare(
                "UPDATE inventaires SET quantiteInventaire = :qte WHERE idJoueur = :idJoueur AND idItem = :idItem"
            );
            $upd->bindValue(':qte', $nouvelleQte, PDO::PARAM_INT);
            $upd->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $upd->bindValue(':idItem', $idItem, PDO::PARAM_INT);
            $upd->execute();
        }

        // Récupérer le prix de l'item et ajouter l'or au joueur
        $prix = $pdo->prepare("SELECT prix FROM items WHERE idItem = :idItem");
        $prix->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $prix->execute();
        $itemRow = $prix->fetch();

        if ($itemRow !== false) {
            $gain = (int) $itemRow['prix'] * $quantite;
            $addGold = $pdo->prepare(
                "UPDATE joueurs SET gold = gold + :gain WHERE idJoueur = :idJoueur"
            );
            $addGold->bindValue(':gain', $gain, PDO::PARAM_INT);
            $addGold->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $addGold->execute();
        }

        return true;
    }
}
