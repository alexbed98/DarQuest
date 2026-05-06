<?php
class CategoryDAL
{
    //-------------------------------------------------------------------------------
    //Select tout dans la table Categories
    //-------------------------------------------------------------------------------
    public static function select(PDO $connexion): array
    {
        $sql = "SELECT idCategorie, nomCategorie FROM Categories";

        $statement = $connexion->prepare($sql);

        $statement->execute();

        return $statement->fetchAll();

    }
    //-------------------------------------------------------------------------------
    //Trouve l'id du categorie de magie
    //-------------------------------------------------------------------------------
    public static function selectMagicId(PDO $connexion): string
    {
        $sql = "SELECT idCategorie FROM Categories WHERE nomCategorie LIKE '%Magie%' OR nomCategorie LIKE '%magie%'";

        $statement = $connexion->prepare($sql);

        $statement->execute();

        return $statement->fetchColumn();

    }
}

