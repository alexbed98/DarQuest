<?php

class ItemDAL
{
    public static string $OrderBy = '';

    public static function selectAll(PDO $connexion): array {

        $sql = "SELECT idItem, nom, quantiteStock, prix, photo,typeItem from Items " . self::$OrderBy;

        $statement = $connexion->prepare($sql); 
             
        $statement->execute();

        // Retourne un tableau vide si aucune donnée
        return $statement->fetchAll();

    }
    public static function selectAllOrderByPrice(PDO $connexion): array {

        $sql = "SELECT idItem, nom, quantiteStock, prix, photo,typeItem from Items ORDER BY prix";

        $statement = $connexion->prepare($sql); 
             
        $statement->execute();

        // Retourne un tableau vide si aucune donnée
        return $statement->fetchAll();

    }
    public static function insertArme(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pDescription, string $pEfficacite, string $pGenreArme/*, int $idItem, string $nom, int $quantiteStock,int $prix,string $photo,string $typeItem*/): bool {

        $sql = "call ajouterArme(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pDescription,:pEfficacite,:pGenreArme);";

        $statement = $connexion->prepare($sql); 

         $statement->bindValue('pNom', $pNom, PDO::PARAM_STR);
         $statement->bindValue('pQuantite', $pQuantite, PDO::PARAM_INT);
         $statement->bindValue('pPrix', $pPrix, PDO::PARAM_INT);
         $statement->bindValue('pPhoto', $pPhoto, PDO::PARAM_STR);
         $statement->bindValue('pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
         $statement->bindValue('pDescription', $pDescription, PDO::PARAM_STR);
         $statement->bindValue('pEfficacite', $pEfficacite, PDO::PARAM_STR);
         $statement->bindValue('pGenreArme', $pGenreArme, PDO::PARAM_STR);
             
        return $statement->execute();

    }
    public static function insertSort(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, int $pInstantane, int $prarete, string $ptype/*, int $idItem, string $nom, int $quantiteStock,int $prix,string $photo,string $typeItem*/): bool {

        $sql = "call ajouterPotion(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pInstantane,:prarete,:ptype);";

        $statement = $connexion->prepare($sql); 

         $statement->bindValue('pNom', $pNom, PDO::PARAM_STR);
         $statement->bindValue('pQuantite', $pQuantite, PDO::PARAM_INT);
         $statement->bindValue('pPrix', $pPrix, PDO::PARAM_INT);
         $statement->bindValue('pPhoto', $pPhoto, PDO::PARAM_STR);
         $statement->bindValue('pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
         $statement->bindValue('pInstantane', $pInstantane, PDO::PARAM_INT);
         $statement->bindValue('prarete', $prarete, PDO::PARAM_INT);
         $statement->bindValue('ptype', $ptype, PDO::PARAM_STR_CHAR);
             
        return $statement->execute();

    }
   
    public static function insertArmure(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pMatiere, string $pTaille/*, int $idItem, string $nom, int $quantiteStock,int $prix,string $photo,string $typeItem*/): bool {

        $sql = "call ajouterArmure(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pMatiere,:pTaille);";

        $statement = $connexion->prepare($sql); 

         $statement->bindValue('pNom', $pNom, PDO::PARAM_STR);
         $statement->bindValue('pQuantite', $pQuantite, PDO::PARAM_INT);
         $statement->bindValue('pPrix', $pPrix, PDO::PARAM_INT);
         $statement->bindValue('pPhoto', $pPhoto, PDO::PARAM_STR);
         $statement->bindValue('pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
         $statement->bindValue('pMatiere', $pMatiere, PDO::PARAM_STR);
         $statement->bindValue('pTaille', $pTaille, PDO::PARAM_STR);
             
        return $statement->execute();

    }
     public static function insertPotion(PDO $connexion, string $pNom,int $pQuantite,int $pPrix, string $pPhoto, int $pEstDisponible, string $pEffet, int $pDuree/*, int $idItem, string $nom, int $quantiteStock,int $prix,string $photo,string $typeItem*/): bool {

        $sql = "call ajouterPotion(:pNom,:pQuantite,:pPrix,:pPhoto,:pEstDisponible,:pEffet,:pDuree);";

        $statement = $connexion->prepare($sql); 

         $statement->bindValue('pNom', $pNom, PDO::PARAM_STR);
         $statement->bindValue('pQuantite', $pQuantite, PDO::PARAM_INT);
         $statement->bindValue('pPrix', $pPrix, PDO::PARAM_INT);
         $statement->bindValue('pPhoto', $pPhoto, PDO::PARAM_STR);
         $statement->bindValue('pEstDisponible', $pEstDisponible, PDO::PARAM_INT);
         $statement->bindValue('pEffet', $pEffet, PDO::PARAM_STR);
         $statement->bindValue('pDuree', $pDuree, PDO::PARAM_INT);
             
        return $statement->execute();

    }
    public static function resetItems(PDO $connexion): bool {

        $sql = "TRUNCATE TABLE Armes;TRUNCATE TABLE Armures;TRUNCATE TABLE Sorts;TRUNCATE TABLE Potions; TRUNCATE TABLE Items;";

        $statement = $connexion->prepare($sql); 

        // $statement->bindValue('title', $title, PDO::PARAM_STR);
        // $statement->bindValue('description', $description, PDO::PARAM_STR);
        // $statement->bindValue('image', $image, PDO::PARAM_STR);
        // $statement->bindValue('alt', $alt, PDO::PARAM_STR);
             
        return $statement->execute();

    }
   

   

    
}





