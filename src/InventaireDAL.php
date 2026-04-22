<?php

class InventaireDAL
{
    /**
     * Retourne les items de l'inventaire d'un joueur avec tri et filtres optionnels.
     * $orderBy : 'prix_asc', 'prix_desc', 'type' — défaut : 'nom'
     * $filtres  : tableau de typeItem ('A', 'R', 'P', 'S')
     */
    public static function getInventaire(PDO $pdo, int $idJoueur, string $orderBy = 'nom', array $filtres = []): array
    {
        $allowedOrders = [
            'prix_asc'  => 'i.prix ASC',
            'prix_desc' => 'i.prix DESC',
            'type'      => 'i.typeItem ASC',
            'nom'       => 'i.nom ASC',
        ];
        $order = $allowedOrders[$orderBy] ?? 'i.nom ASC';

        $where = 'WHERE inv.idJoueur = :idJoueur';

        $allowedTypes = ['A', 'R', 'P', 'S'];
        $filtres = array_values(array_intersect($filtres, $allowedTypes));

        if (!empty($filtres)) {
            $placeholders = implode(', ', array_map(fn($i) => ":type$i", array_keys($filtres)));
            $where .= " AND i.typeItem IN ($placeholders)";
        }

        $sql = "SELECT i.idItem, i.nom, i.prix, i.photo, i.typeItem, inv.quantiteInventaire
                FROM inventaires inv
                JOIN items i ON i.idItem = inv.idItem
                $where
                ORDER BY $order";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);

        foreach ($filtres as $i => $type) {
            $stmt->bindValue(":type$i", $type, PDO::PARAM_STR);
        }

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
        $stmt = $pdo->prepare("SELECT gold FROM Joueurs WHERE idJoueur = :id");
        $stmt->bindValue(':id', $idJoueur, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row === false || (int) $row['gold'] < $total) {
            return false;
        }

        // Déduire l'or
        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare("UPDATE Joueurs SET gold = gold - :total WHERE idJoueur = :id");
            $upd->bindValue(':total', (int) $total, PDO::PARAM_INT);
            $upd->bindValue(':id', $idJoueur, PDO::PARAM_INT);
            $upd->execute();

            // Ajouter chaque item à l'inventaire et décrémenter le stock
            $ins = $pdo->prepare(
                "INSERT INTO inventaires (idJoueur, idItem, quantiteInventaire)
                 VALUES (:idJoueur, :idItem, :qte)
                 ON DUPLICATE KEY UPDATE quantiteInventaire = quantiteInventaire + :qte2"
            );
            $updStock = $pdo->prepare(
                "UPDATE items SET quantiteStock = quantiteStock - :qte WHERE idItem = :idItem AND quantiteStock >= :qte2"
            );

            foreach ($panier as $item) {
                $ins->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $ins->bindValue(':idItem', (int) $item['id'], PDO::PARAM_INT);
                $ins->bindValue(':qte', (int) $item['quantite'], PDO::PARAM_INT);
                $ins->bindValue(':qte2', (int) $item['quantite'], PDO::PARAM_INT);
                $ins->execute();

                $updStock->bindValue(':qte', (int) $item['quantite'], PDO::PARAM_INT);
                $updStock->bindValue(':qte2', (int) $item['quantite'], PDO::PARAM_INT);
                $updStock->bindValue(':idItem', (int) $item['id'], PDO::PARAM_INT);
                $updStock->execute();

                if ($updStock->rowCount() === 0) {
                    // Stock insuffisant pour cet item — annuler toute la transaction
                    $pdo->rollBack();
                    return false;
                }
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
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

        // Récupérer le prix de l'item, ajouter l'or au joueur et remettre le stock
        $prix = $pdo->prepare("SELECT prix FROM items WHERE idItem = :idItem");
        $prix->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $prix->execute();
        $itemRow = $prix->fetch();

        if ($itemRow !== false) {
            $gain = (int) $itemRow['prix'] * $quantite;
            $addGold = $pdo->prepare(
                "UPDATE Joueurs SET gold = gold + :gain WHERE idJoueur = :idJoueur"
            );
            $addGold->bindValue(':gain', $gain, PDO::PARAM_INT);
            $addGold->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $addGold->execute();

            $restoreStock = $pdo->prepare(
                "UPDATE items SET quantiteStock = quantiteStock + :qte WHERE idItem = :idItem"
            );
            $restoreStock->bindValue(':qte', $quantite, PDO::PARAM_INT);
            $restoreStock->bindValue(':idItem', $idItem, PDO::PARAM_INT);
            $restoreStock->execute();
        }

        return true;
    }
}
