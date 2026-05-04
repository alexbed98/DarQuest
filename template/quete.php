<?php

use Dom\Document;
include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';
$enigme = [];
$reponses = [];
$message = '';
$errorMessage = '';


if (EnigneDAL::countAllEnigme(Database::getConnexion($dbConfig))) { //Verifie s'il y a des enigmes
    $enigme = EnigneDAL::selectRandomEnigme(Database::getConnexion($dbConfig));
    $reponses = $enigme != null ? EnigneDAL::selectAllAnswers(Database::getConnexion($dbConfig), $enigme['idEnigme']) : [];

    if (!$reponses) //S'il l'egnime n'a pas de reponses, affiche un message d'erreur
        $errorMessage = "Cette quête manque ses réponses";
    else
        $errorMessage = "";

} else {
    $errorMessage = "Désoler, il n'y a pas de quête pour le moment";
}

shuffle($reponses);


$connexion = Database::getConnexion($dbConfig);
$joueur = AccountDAL::selectByEmail($connexion, $_SESSION['email']);
$idJoueur = $joueur['idJoueur'];
$nbDemandes = EnigneDAL::countDemandesByJoueur($connexion, $idJoueur);

if (IS_POST) {

    // ===== DEMANDE D'ARGENT =====
    if (isset($_POST['demande_argent'])) {

        $connexion = Database::getConnexion($dbConfig);
        $idJoueur = $joueur['idJoueur'];

        // Compter les demandes existantes
        $nbDemandes = EnigneDAL::countDemandesByJoueur($connexion, $idJoueur);

        if ($nbDemandes < 3) {
            EnigneDAL::insertDemande($connexion, $idJoueur);
            $nbDemandes = EnigneDAL::countDemandesByJoueur($connexion, $idJoueur);
            $message = "Demande envoyée à l'admin.";
        } else {
            $errorMessage = "Vous avez atteint la limite de 3 demandes.";
        }
    }

    // ===== RÉPONSE À L'ÉNIGME =====
    $bonOuPas = $_POST['answer'] ?? null;
    $difficulte = $_POST['difficulte'] ?? null;
    if ($bonOuPas != null && $difficulte != null)
        if ($bonOuPas == 1) {
            $message =  AccountDAL::addReward(Database::getConnexion($dbConfig), $_SESSION['email'], $difficulte);//'Bonne réponse';
        } else {
            $message = AccountDAL::takeDamage(Database::getConnexion($dbConfig), $_SESSION['email'], $difficulte);//'Mauvaise réponse';
        }
}

?>

<div style="flex: 1; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
    <form method="POST" action="">
    <?php if ($nbDemandes < 3): ?>
        <button type="submit" name="demande_argent" value="1">
            Demander de l'argent
        </button>
    <?php else: ?>
        <button disabled>Limite de demande d'argent atteinte</button>
    <?php endif; ?>
</form>
    <h1>Enigma</h1>
    <div>
        Nombre de pièces d'or : <span
            id="gold"><?= number_format(AccountDAL::selectGold($connexion, $_SESSION['email'])) . '&nbsp;🥇'; ?></span>
    </div>
</div>

<form id="answerEnigme" method="POST" action="">

    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; margin: 50px;">
        <?php if ($enigme): ?> <!-- S'il y a une quete, affiche sa question -->
            <div style="width: 45%; justify-items: center; border: 1px solid black; margin: 20px; padding: 5px;">
                <h3><?php echo $enigme['enonce']; ?></h3>
                <input type="hidden" name="difficulte" value="<?=$enigme['difficulte']?>">
            </div>
        <?php endif ?>

        <?php if ($enigme && $reponses): ?><!-- S'il y a une quete ET cette quete a des reponses, affiche toute ses reponses associé -->
            <?php foreach ($reponses as $index => $reponse): ?>
                <label for="reponse<?= $index + 1; ?>" style="margin: 5px; padding: 5px;">
                    <!-- Si la reponse soumise est bonne, retourn la difficulté pour que l'argent soit calculer en fonction, sinon donne rien -->
                    <input type="radio" id="reponse<?= $index + 1; ?>" name="answer"
                        value="<?= $reponse['estBonneReponse']  ?>" placeholder="Votre réponse">
                    <?= $reponse['reponse']; ?>
                </label>
            <?php endforeach; ?>
            <button type="submit" style="width: 30%; margin:20px;">Valider la réponse</button>

            <div><?php echo $message ?></div>

        <?php else: ?>

            <?php if (EnigneDAL::countAllEnigme(Database::getConnexion($dbConfig))): ?> <!-- S'il n'y a pas de quete, n'affiche pas le button pour une nouvelle quete -->
                <button type="submit" style="width: 30%; margin:20px;">Nouvelle quête</button>
            <?php endif ?>

            <div><?php echo $message ?></div>
            <div><?php echo $errorMessage ?></div>

        <?php endif ?>

    </div>
</form>