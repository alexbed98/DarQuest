<?php

class ItemDAL
{
    public static string $OrderBy = 'DESC';
    public static array $filtre = ['a'];

    public static function selectAll(PDO $connexion): array
    {

        $where = '';

        $sql = "SELECT idItem, nom, quantiteStock, prix, photo, typeItem 
                FROM Items 
                $where 
                ORDER BY prix " . self::$OrderBy;

        $statement = $connexion->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function selectAllOrderByPrice(PDO $connexion): array {

        $sql = "SELECT idItem, nom, quantiteStock, prix, photo, typeItem 
                FROM Items 
                ORDER BY prix";

        $statement = $connexion->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function selectById(PDO $connexion, int $idItem): false|array
    {
        $sql = "SELECT idItem, nom, quantiteStock, prix, photo, typeItem
                FROM Items
                WHERE idItem = :idItem";

        $statement = $connexion->prepare($sql);
        $statement->bindValue(':idItem', $idItem, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    public static function insertArme(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pDescription, string $pEfficacite, string $pGenreArme): bool {

        $sql = "CALL ajouterArme(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pDescription,:pEfficacite,:pGenreArme);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':pNom', $pNom, PDO::PARAM_STR);
        $statement->bindValue(':pQuantite', $pQuantite, PDO::PARAM_INT);
        $statement->bindValue(':pPrix', $pPrix, PDO::PARAM_INT);
        $statement->bindValue(':pPhoto', $pPhoto, PDO::PARAM_STR);
        $statement->bindValue(':pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
        $statement->bindValue(':pDescription', $pDescription, PDO::PARAM_STR);
        $statement->bindValue(':pEfficacite', $pEfficacite, PDO::PARAM_STR);
        $statement->bindValue(':pGenreArme', $pGenreArme, PDO::PARAM_STR);

        $result = $statement->execute();
        $statement->closeCursor();

        return $result;
    }

    public static function insertSort(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, int $pInstantane, int $prarete, string $ptype): bool {

        $sql = "CALL ajouterSort(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pInstantane,:prarete,:ptype);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':pNom', $pNom, PDO::PARAM_STR);
        $statement->bindValue(':pQuantite', $pQuantite, PDO::PARAM_INT);
        $statement->bindValue(':pPrix', $pPrix, PDO::PARAM_INT);
        $statement->bindValue(':pPhoto', $pPhoto, PDO::PARAM_STR);
        $statement->bindValue(':pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
        $statement->bindValue(':pInstantane', $pInstantane, PDO::PARAM_INT);
        $statement->bindValue(':prarete', $prarete, PDO::PARAM_INT);
        $statement->bindValue(':ptype', $ptype, PDO::PARAM_STR); // FIX

        $result = $statement->execute();
        $statement->closeCursor();

        return $result;
    }

    public static function insertArmure(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pMatiere, string $pTaille): bool {

        $sql = "CALL ajouterArmure(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pMatiere,:pTaille);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':pNom', $pNom, PDO::PARAM_STR);
        $statement->bindValue(':pQuantite', $pQuantite, PDO::PARAM_INT);
        $statement->bindValue(':pPrix', $pPrix, PDO::PARAM_INT);
        $statement->bindValue(':pPhoto', $pPhoto, PDO::PARAM_STR);
        $statement->bindValue(':pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
        $statement->bindValue(':pMatiere', $pMatiere, PDO::PARAM_STR);
        $statement->bindValue(':pTaille', $pTaille, PDO::PARAM_STR);

        $result = $statement->execute();
        $statement->closeCursor();

        return $result;
    }

    public static function insertPotion(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pEffet, int $pDuree): bool {

        $sql = "CALL ajouterPotion(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pEffet,:pDuree);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':pNom', $pNom, PDO::PARAM_STR);
        $statement->bindValue(':pQuantite', $pQuantite, PDO::PARAM_INT);
        $statement->bindValue(':pPrix', $pPrix, PDO::PARAM_INT);
        $statement->bindValue(':pPhoto', $pPhoto, PDO::PARAM_STR);
        $statement->bindValue(':pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
        $statement->bindValue(':pEffet', $pEffet, PDO::PARAM_STR);
        $statement->bindValue(':pDuree', $pDuree, PDO::PARAM_INT);

        $result = $statement->execute();
        $statement->closeCursor();

        return $result;
    }

    public static function insertSortType(PDO $connexion,string $typeSort,string $uneDescription,int $ptVie,int $ptDegat): bool {

        $sql = "INSERT INTO TypeSorts (typeSort, uneDescription, ptVie, ptDegat) 
                VALUES (:typeSort, :uneDescription, :ptVie, :ptDegat);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':typeSort', $typeSort, PDO::PARAM_STR);
        $statement->bindValue(':uneDescription', $uneDescription, PDO::PARAM_STR);
        $statement->bindValue(':ptVie', $ptVie, PDO::PARAM_INT);
        $statement->bindValue(':ptDegat', $ptDegat, PDO::PARAM_INT);

        return $statement->execute();
    }

    public static function resetItems(PDO $connexion): bool {

        try {
            $connexion->exec("TRUNCATE TABLE Armes");
            $connexion->exec("TRUNCATE TABLE Armures");
            $connexion->exec("TRUNCATE TABLE Sorts");
            $connexion->exec("TRUNCATE TABLE Potions");
            $connexion->exec("TRUNCATE TABLE Items");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}