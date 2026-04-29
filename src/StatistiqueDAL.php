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
}