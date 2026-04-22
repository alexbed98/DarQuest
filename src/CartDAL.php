<?php

class CartDAL
{
    /**
     * Charge le panier d'un joueur depuis la BD et retourne un tableau
     * au format session (id, nom, image, prix, quantite).
     */
    public static function loadCart(PDO $pdo, int $idJoueur): array
    {
        $sql = "SELECT p.idItem, p.quantitePanier, i.nom, i.prix, i.photo
            FROM Paniers p
            JOIN Items i ON i.idItem = p.idItem
                WHERE p.idJoueur = :idJoueur";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $cart = [];
        foreach ($rows as $row) {
            $photo = (string) $row['photo'];
            $image = ($photo !== '' && $photo[0] === '/')
                ? $photo
                : IMG . '/items/' . ltrim($photo, '/');

            $cart[] = [
                'id'       => (int) $row['idItem'],
                'nom'      => (string) $row['nom'],
                'image'    => $image,
                'prix'     => (float) $row['prix'],
                'quantite' => (int) $row['quantitePanier'],
            ];
        }

        return $cart;
    }

    /**
     * Sauvegarde le panier en session dans la BD (remplace les lignes existantes).
     */
    public static function saveCart(PDO $pdo, int $idJoueur, array $panier): void
    {
        $del = $pdo->prepare("DELETE FROM Paniers WHERE idJoueur = :idJoueur");
        $del->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
        $del->execute();

        if (empty($panier)) {
            return;
        }

        $ins = $pdo->prepare(
            "INSERT INTO Paniers (idJoueur, idItem, quantitePanier) VALUES (:idJoueur, :idItem, :qty)"
        );

        foreach ($panier as $item) {
            $ins->bindValue(':idJoueur', $idJoueur, PDO::PARAM_INT);
            $ins->bindValue(':idItem', (int) $item['id'], PDO::PARAM_INT);
            $ins->bindValue(':qty', (int) $item['quantite'], PDO::PARAM_INT);
            $ins->execute();
        }
    }
}
