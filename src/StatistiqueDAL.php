<?php
class StatistiqueDAL
{
    //-------------------------------------------------------------------------------
    //Procedure pour ajouter ou mettre a jour le stats d'un joueur 
    //-------------------------------------------------------------------------------
    public static function updateStatistique(PDO $connexion, string $email, int $idEnigme, int $estReussie): void
    {
        $sql = null;
        $idJoueur = AccountDAL::selectByEmail($connexion, $email)['idJoueur'];
        //$catEnigme = EnigneDAL::selectById($connexion, $idEnigme)['idCategorie'];

        if(!StatistiqueDAL::alreadyInStats($connexion, $email,$idEnigme))
            $sql = "INSERT INTO Statistiques VALUES(:idJoueur,:idEnigme,:estReussie);";
        else
            $sql = "UPDATE Statistiques SET estReussie = :estReussie WHERE idJoueur = :idJoueur AND idEnigme = :idEnigme;";
            

        $statement = $connexion->prepare($sql);

        $statement->bindValue('idJoueur', $idJoueur, PDO::PARAM_INT);
        $statement->bindValue('idEnigme', $idEnigme, PDO::PARAM_INT);
        $statement->bindValue('estReussie', $estReussie, PDO::PARAM_INT);
        $statement->execute();
    }
    //-------------------------------------------------------------------------------
    //Fonction pour voir si le joueur a deja fait l'enigme
    //-------------------------------------------------------------------------------
    public static function alreadyInStats(PDO $connexion, string $email, string $idEnigme): bool
    {
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
}