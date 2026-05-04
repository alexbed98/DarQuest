<?php

use Dom\Document;
include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';
require_once 'src/StatistiqueDAL.php';
require_once 'src/CategoryDAL.php';

$connexion = Database::getConnexion($dbConfig);
$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0;
$enigme = [];
$reponses = [];
$message = '';
$errorMessage = '';


if (EnigneDAL::countAllEnigme(Database::getConnexion($dbConfig))) { //Verifie s'il y a des enigmes
    $enigme = EnigneDAL::selectRandomEnigme(Database::getConnexion($dbConfig));
    $reponses = $enigme != null ? EnigneDAL::selectAllAnswers(Database::getConnexion($dbConfig), $enigme['idEnigme']) : [];
if (EnigneDAL::countAllEnigme($connexion)) { //Verifie s'il y a des enigmes (Return true s'il y en a)
    $enigme = EnigneDAL::selectRandomEnigme($connexion);
    $reponses = $enigme != null ? EnigneDAL::selectAllAnswers($connexion, $enigme['idEnigme']) : [];

    if (!$reponses) //S'il l'egnime n'a pas de reponses, affiche un message d'erreur
        $errorMessage = "Cette quête manque ses réponses";
    else
        $errorMessage = "";

    shuffle($reponses);

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
if (IS_POST) {

    $bonOuPas = $_POST['answer'] ?? null; // => 1 si la reponse choisi est bon, sinon 0
    $difficulte = $_POST['difficulte'] ?? null;// => return la difficulte
    $idEnigme = $_POST['idEnigme'] ?? null;// => return l'id de l'enigme

    if ($bonOuPas != null && $difficulte != null)
        if ($bonOuPas == 1) {
            $message = AccountDAL::addReward($connexion, $_SESSION['email'], $idEnigme);//'Bonne réponse';
        } else {
            $message = AccountDAL::takeDamage($connexion, $_SESSION['email'], $idEnigme);//'Mauvaise réponse';
        }
}

    $playerGold = (int) (AccountDAL::selectGold($connexion, $_SESSION['email']) ?: 0);
    $playerHealth = (int) (AccountDAL::selectPointVie($connexion, $_SESSION['email']) ?: 0);

$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0; //Update son acces au jeu
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
<section class="enigme-page">
    <div class="enigme-header">
        <button type="button" class="enigme-help-btn" onclick="alert('Demander pour de l\'argent')">
            Demander de l'aide
        </button>
        <h1 class="enigme-title">Quete Enigme</h1>
        <div class="enigme-stats">
            <div class="enigme-gold">
                Pieces d'or: <span id="gold"><?= number_format($playerGold) . '&nbsp;🥇'; ?></span>
            </div>
            <div class="enigme-health">
                Points de vie: <span id="health"><?= number_format($playerHealth) . '&nbsp;❤'; ?></span>
            </div>
        </div>
    </div>

<form id="answerEnigme" method="POST" action="" class="enigme-form">
    <?php if ($peutJouer): ?>

        <?php if ($enigme): ?>
            <div class="enigme-question-card">
                <h3><?= htmlspecialchars($enigme['enonce']); ?></h3>
                <input type="hidden" name="difficulte" value="<?= htmlspecialchars((string) $enigme['difficulte']); ?>">
                <input type="hidden" name="idEnigme" value="<?= htmlspecialchars((string) $enigme['idEnigme']); ?>">
            </div>
        <?php endif; ?>

        <?php if ($enigme && $reponses): ?>
            <div class="enigme-answers">
                <?php foreach ($reponses as $index => $reponse): ?>
                    <label for="reponse<?= $index + 1; ?>" class="enigme-answer-option">
                        <input type="radio" id="reponse<?= $index + 1; ?>" name="answer"
                            value="<?= htmlspecialchars((string) $reponse['estBonneReponse']); ?>">
                        <span><?= htmlspecialchars($reponse['reponse']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="enigme-submit-btn">Valider la réponse</button>

        <?php else: ?>
            <?php if (EnigmeDAL::countAllEnigme($connexion)): ?>
                <button type="submit" class="enigme-submit-btn">Nouvelle quête</button>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="enigme-error"><?= htmlspecialchars((string) $errorMessage); ?></div>
            <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
        <div class="enigme-error">
            Vous n'avez pas assez de points de vie pour jouer.
        </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <div class="enigme-message"><?= htmlspecialchars((string) $message); ?></div>
    <?php endif; ?>
</form>
</section>