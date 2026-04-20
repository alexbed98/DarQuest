<?php

class ItemDAL
{
    public static string $OrderByWhat = 'prix';
    public static string $OrderByDirection = 'DESC';
    public static array $filtre = [];

    //-------------------------------------------------------------------------------
    //Selectionne tout la liste d'items
    //(S'ajuste selon les filtres et l'ordre d'affichage)
    //-------------------------------------------------------------------------------
    public static function select(PDO $connexion): array
    {

        $where = ' WHERE estDisponible = true && quantiteStock > 0 ';

        if (count(self::$filtre) > 0) {
            $where .= " && (typeItem =  '" . self::$filtre[0] . "' ";
            if (count(self::$filtre) > 1) {

                for ($index = 1; $index < count(self::$filtre); $index++) {
                    $where .= " || typeItem = '" . self::$filtre[$index] . "'";
                }
            }
            $where .= ')';
        }

        $sql = "SELECT idItem, nom, quantiteStock, prix, photo, typeItem 
                FROM Items 
                $where
                ORDER BY " . self::$OrderByWhat . " " . self::$OrderByDirection;

        $statement = $connexion->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }
    //-------------------------------------------------------------------------------
    //Selectionne un item par son id
    //-------------------------------------------------------------------------------
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

    //----------------------------------------------------------------------------------------------------------------------
    //Change ce qui doit s'afficher sur la liste d'items selon le typeItem
    //(Verifie si le filtre n'est pas déjà appliqué, sinon il l'enleve) <= (Pour eviter les erreurs causé par les doublons)
    //----------------------------------------------------------------------------------------------------------------------
    public static function changeFilter(string $filter): void
    {
        if (in_array($filter, self::$filtre)) {
            self::$filtre = array_diff(self::$filtre, [$filter]);
        } else {
            array_push(self::$filtre, $filter);
        }
    }
    //-------------------------------------------------------------------------------
    //Change l'ordre d'affichage des items selon le prix ou le type
    //(Evite les erreurs causé par des colones non-existants)
    //-------------------------------------------------------------------------------
    public static function changeOrder(string $order): void
    {
        switch ($order) {
            case 'prix':
                self::$OrderByWhat = 'prix';
                break;
            case 'type' || 'typeItem':
                self::$OrderByWhat = 'typeItem';
                break;
        }
    }
    //-------------------------------------------------------------------------------
    //Insertion d'item arme en utilisant la procedure 'ajouterArme'
    //-------------------------------------------------------------------------------
    public static function insertArme(PDO $connexion, string $pNom, int $pQuantite, int $pPrix, string $pPhoto, int $pEstDisponible, string $pDescription, string $pEfficacite, string $pGenreArme): bool
    {

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
    //-------------------------------------------------------------------------------
    //Insertion d'item sort en utilisant la procedure 'ajouterSort'
    //-------------------------------------------------------------------------------
    public static function insertSort(PDO $connexion, string $pNom, int $pQuantite, int $pPrix, string $pPhoto, int $pEstDisponible, int $pInstantane, int $prarete, string $ptype): bool
    {

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
    //-------------------------------------------------------------------------------
    //Insertion d'item armure en utilisant la procedure 'ajouterArmure'
    //-------------------------------------------------------------------------------
    public static function insertArmure(PDO $connexion, string $pNom, int $pQuantite, int $pPrix, string $pPhoto, int $pEstDisponible, string $pMatiere, string $pTaille): bool
    {

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
    //-------------------------------------------------------------------------------
    //Insertion d'item potion en utilisant la procedure 'ajouterPotion'
    //-------------------------------------------------------------------------------
    public static function insertPotion(PDO $connexion, string $pNom, int $pQuantite, int $pPrix, string $pPhoto, int $pEstDisponible, string $pEffet, int $pDuree): bool
    {

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
    //-------------------------------------------------------------------------------
    //Insertion d'un type de sort a la table TypeSorts 
    //(A probablement besoin d'etre changer?)
    //-------------------------------------------------------------------------------
    public static function insertSortType(PDO $connexion, string $typeSort, string $uneDescription, int $ptVie, int $ptDegat): bool
    {

        $sql = "INSERT INTO TypeSorts (typeSort, uneDescription, ptVie, ptDegat) 
                VALUES (:typeSort, :uneDescription, :ptVie, :ptDegat);";

        $statement = $connexion->prepare($sql);

        $statement->bindValue(':typeSort', $typeSort, PDO::PARAM_STR);
        $statement->bindValue(':uneDescription', $uneDescription, PDO::PARAM_STR);
        $statement->bindValue(':ptVie', $ptVie, PDO::PARAM_INT);
        $statement->bindValue(':ptDegat', $ptDegat, PDO::PARAM_INT);

        return $statement->execute();
    }
    //-------------------------------------------------------------------------------
    //Enleve tout les items de les tables: Armes, Armures, Sorts, Potions et Items
    //(Ne pas utiliser pour ne pas surcompliquer des erreurs)
    //-------------------------------------------------------------------------------
    public static function resetItems(PDO $connexion): bool
    {

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