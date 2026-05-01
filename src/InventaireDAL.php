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

        $sql = "SELECT i.idItem, i.nom, i.prix, i.photo, i.typeItem, inv.quantiteInventaire, p.effet AS effetPotion, s.typeSort AS typeSortSpell
            FROM Inventaires inv
            JOIN Items i ON i.idItem = inv.idItem
            LEFT JOIN Potions p ON p.idItem = i.idItem
            LEFT JOIN Sorts s ON s.idItem = i.idItem
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
                "INSERT INTO Inventaires (idJoueur, idItem, quantiteInventaire)
                 VALUES (:idJoueur, :idItem, :qte)
                 ON DUPLICATE KEY UPDATE quantiteInventaire = quantiteInventaire + :qte2"
            );
            $updStock = $pdo->prepare(
                "UPDATE Items SET quantiteStock = quantiteStock - :qte WHERE idItem = :idItem AND quantiteStock >= :qte2"
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
        * Retourne le gain en or, ou false si la vente est impossible.
     */
        public static function vendre(PDO $pdo, int $idJoueur, int $idItem, int $quantite): int|false
    {
        if ($quantite < 1) {
            return false;
        }

        // Vérifier quantité inventaire + récupérer type/prix (et rareté si sort)
        $check = $pdo->prepare(
            "SELECT inv.quantiteInventaire, i.prix, i.typeItem, s.rarete
             FROM Inventaires inv
             JOIN Items i ON i.idItem = inv.idItem
             LEFT JOIN Sorts s ON s.idItem = i.idItem
             WHERE inv.idJoueur = :idJoueur AND inv.idItem = :idItem"
        );
        $check->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $check->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if ($row === false || (int) $row['quantiteInventaire'] < $quantite) {
            return false;
        }

        $prixUnitaire = (float) ($row['prix'] ?? 0);
        $typeItem = (string) ($row['typeItem'] ?? '');
        $rarete = (int) ($row['rarete'] ?? 0);

        $ratioVente = 0.60;
        if ($typeItem === 'S') {
            $ratioVente = match ($rarete) {
                1 => 1.00,
                2 => 0.95,
                3 => 0.90,
                default => 0.60,
            };
        }

        $gain = (int) round($prixUnitaire * $ratioVente * $quantite);
        $nouvelleQte = (int) $row['quantiteInventaire'] - $quantite;

        $pdo->beginTransaction();
        try {
            if ($nouvelleQte === 0) {
                $del = $pdo->prepare(
                    "DELETE FROM Inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $del->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $del->execute();
            } else {
                $upd = $pdo->prepare(
                    "UPDATE Inventaires SET quantiteInventaire = :qte WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $upd->bindValue(':qte', $nouvelleQte, PDO::PARAM_INT);
                $upd->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $upd->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $upd->execute();
            }

            $addGold = $pdo->prepare(
                "UPDATE Joueurs SET gold = gold + :gain WHERE idJoueur = :idJoueur"
            );
            $addGold->bindValue(':gain', $gain, PDO::PARAM_INT);
            $addGold->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $addGold->execute();

            // L'item vendu retourne au stock du magasin
            $restoreStock = $pdo->prepare(
                "UPDATE Items SET quantiteStock = quantiteStock + :qte WHERE idItem = :idItem"
            );
            $restoreStock->bindValue(':qte', $quantite, PDO::PARAM_INT);
            $restoreStock->bindValue(':idItem', $idItem, PDO::PARAM_INT);
            $restoreStock->execute();

            $pdo->commit();
            return $gain;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Consomme 1 potion de vie (effet contenant "vie") et rend des points de vie au joueur.
     */
    public static function consommerPotionVie(PDO $pdo, int $idJoueur, int $idItem, int $gainPv = 10): bool
    {
        $check = $pdo->prepare(
            "SELECT inv.quantiteInventaire, i.typeItem, p.effet
             FROM Inventaires inv
             JOIN Items i ON i.idItem = inv.idItem
             LEFT JOIN Potions p ON p.idItem = i.idItem
             WHERE inv.idJoueur = :idJoueur AND inv.idItem = :idItem"
        );
        $check->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $check->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if ($row === false || (int) ($row['quantiteInventaire'] ?? 0) < 1) {
            return false;
        }

        $isPotionVie = (($row['typeItem'] ?? '') === 'P')
            && stripos((string) ($row['effet'] ?? ''), 'vie') !== false;

        if (!$isPotionVie) {
            return false;
        }

        $pdo->beginTransaction();
        try {
            if ((int) $row['quantiteInventaire'] === 1) {
                $del = $pdo->prepare(
                    "DELETE FROM Inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $del->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $del->execute();
            } else {
                $updInv = $pdo->prepare(
                    "UPDATE Inventaires
                     SET quantiteInventaire = quantiteInventaire - 1
                     WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $updInv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $updInv->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $updInv->execute();
            }

            $updPv = $pdo->prepare(
                "UPDATE Joueurs SET pointVie = pointVie + :gainPv WHERE idJoueur = :idJoueur"
            );
            $updPv->bindValue(':gainPv', $gainPv, PDO::PARAM_INT);
            $updPv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $updPv->execute();

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Consomme 1 potion et applique l'effet approprié:
     * - Si c'est une potion de "vie", ajoute +10 PV
     * - Sinon, retire simplement la potion
     */
    public static function consommerPotion(PDO $pdo, int $idJoueur, int $idItem): bool
    {
        $check = $pdo->prepare(
            "SELECT inv.quantiteInventaire, i.typeItem, p.effet AS effetPotion
             FROM Inventaires inv
             JOIN Items i ON i.idItem = inv.idItem
             LEFT JOIN Potions p ON p.idItem = i.idItem
             WHERE inv.idJoueur = :idJoueur AND inv.idItem = :idItem"
        );
        $check->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $check->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if ($row === false || (int) ($row['quantiteInventaire'] ?? 0) < 1) {
            return false;
        }

        $isPotion = (($row['typeItem'] ?? '') === 'P');

        if (!$isPotion) {
            return false;
        }

        $isPotionVie = stripos((string) ($row['effetPotion'] ?? ''), 'vie') !== false;

        $pdo->beginTransaction();
        try {
            if ((int) $row['quantiteInventaire'] === 1) {
                $del = $pdo->prepare(
                    "DELETE FROM Inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $del->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $del->execute();
            } else {
                $updInv = $pdo->prepare(
                    "UPDATE Inventaires
                     SET quantiteInventaire = quantiteInventaire - 1
                     WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $updInv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $updInv->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $updInv->execute();
            }

            if ($isPotionVie) {
                $updPv = $pdo->prepare(
                    "UPDATE Joueurs SET pointVie = pointVie + 10 WHERE idJoueur = :idJoueur"
                );
                $updPv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $updPv->execute();
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Lance 1 sort et applique l'effet approprié:
     * - Si c'est un sort de "vie" (ptVie > 0), ajoute les PV du type de sort
     * - Sinon, retire simplement le sort
     */
    public static function lancerSort(PDO $pdo, int $idJoueur, int $idItem): bool
    {
        $check = $pdo->prepare(
            "SELECT inv.quantiteInventaire, i.typeItem, i.nom, s.typeSort, ts.ptVie
             FROM Inventaires inv
             JOIN Items i ON i.idItem = inv.idItem
             LEFT JOIN Sorts s ON s.idItem = i.idItem
             LEFT JOIN TypeSorts ts ON LOWER(TRIM(ts.typeSort)) = LOWER(TRIM(s.typeSort))
             WHERE inv.idJoueur = :idJoueur AND inv.idItem = :idItem"
        );
        $check->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $check->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $check->execute();
        $row = $check->fetch();

        if ($row === false || (int) ($row['quantiteInventaire'] ?? 0) < 1) {
            return false;
        }

        $isSort = (($row['typeItem'] ?? '') === 'S');

        if (!$isSort) {
            return false;
        }

        $ptVieSort = (int) ($row['ptVie'] ?? 0);
        $nomItem = (string) ($row['nom'] ?? '');
        $typeSort = (string) ($row['typeSort'] ?? '');
        $typeSortCode = strtoupper(trim($typeSort));
        $isCodeVitalite = ($typeSortCode === 'V');
        $isSortVieParNom = stripos($typeSort, 'vie') !== false
            || stripos($typeSort, 'vital') !== false
            || stripos($typeSort, 'soin') !== false
            || stripos($typeSort, 'heal') !== false
            || stripos($nomItem, 'vie') !== false
            || stripos($nomItem, 'vital') !== false
            || stripos($nomItem, 'soin') !== false
            || stripos($nomItem, 'heal') !== false;
        $gainPv = $ptVieSort > 0 ? $ptVieSort : (($isSortVieParNom || $isCodeVitalite) ? 10 : 0);

        $pdo->beginTransaction();
        try {
            if ((int) $row['quantiteInventaire'] === 1) {
                $del = $pdo->prepare(
                    "DELETE FROM Inventaires WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $del->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $del->execute();
            } else {
                $updInv = $pdo->prepare(
                    "UPDATE Inventaires
                     SET quantiteInventaire = quantiteInventaire - 1
                     WHERE idJoueur = :idJoueur AND idItem = :idItem"
                );
                $updInv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $updInv->bindValue(':idItem', $idItem, PDO::PARAM_INT);
                $updInv->execute();
            }

            if ($gainPv > 0) {
                $updPv = $pdo->prepare(
                    "UPDATE Joueurs SET pointVie = pointVie + :gainPv WHERE idJoueur = :idJoueur"
                );
                $updPv->bindValue(':gainPv', $gainPv, PDO::PARAM_INT);
                $updPv->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
                $updPv->execute();
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
