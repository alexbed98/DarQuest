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


if (IS_POST) {

    $bonOuPas = $_POST['answer'] ?? null;
    $difficulte = $_POST['difficulte'] ?? null;
    if ($bonOuPas != null && $difficulte != null)
        if ($bonOuPas == 1) {
            $message =  AccountDAL::addReward(Database::getConnexion($dbConfig), $_SESSION['email'], $difficulte);//'Bonne réponse';
        } else {
            $message = AccountDAL::takeDamage(Database::getConnexion($dbConfig), $_SESSION['email'], $difficulte);//'Mauvaise réponse';
        }
}

    $playerGold = (int) (AccountDAL::selectGold($connexion, $_SESSION['email']) ?: 0);
    $playerHealth = (int) (AccountDAL::selectPointVie($connexion, $_SESSION['email']) ?: 0);

?>

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
        <?php if ($enigme): ?>
            <div class="enigme-question-card">
                <h3><?= htmlspecialchars($enigme['enonce']); ?></h3>
                <input type="hidden" name="difficulte" value="<?= htmlspecialchars((string) $enigme['difficulte']); ?>">
            </div>
        <?php endif; ?>

        <?php if ($enigme && $reponses): ?>
            <div class="enigme-answers">
                <?php foreach ($reponses as $index => $reponse): ?>
                    <label for="reponse<?= $index + 1; ?>" class="enigme-answer-option">
                        <input type="radio" id="reponse<?= $index + 1; ?>" name="answer"
                            value="<?= $reponse['estBonneReponse']; ?>">
                        <span><?= htmlspecialchars($reponse['reponse']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="enigme-submit-btn">Valider la reponse</button>

            <?php if ($message !== ''): ?>
                <div class="enigme-message"><?= htmlspecialchars((string) $message); ?></div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (EnigneDAL::countAllEnigme(Database::getConnexion($dbConfig))): ?>
                <button type="submit" class="enigme-submit-btn">Nouvelle quete</button>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
                <div class="enigme-message"><?= htmlspecialchars((string) $message); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="enigme-error"><?= htmlspecialchars((string) $errorMessage); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </form>
</section>