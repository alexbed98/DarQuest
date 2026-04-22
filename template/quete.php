<?php
include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';

$enigme = EnigneDAL::selectRandomEnigme(Database::getConnexion($dbConfig));
$reponses = $enigme != null ? EnigneDAL::selectAllAnswers(Database::getConnexion($dbConfig), $enigme['idEnigme']) : [];
$message = '';

shuffle($reponses);

if (IS_POST) {
    $difficulte = $_POST['answer'] ?? null; //<- Si la réponse est bonne, elle retourn sa difficulter, sinon elle est null
    if ($difficulte != null) {
        AccountDAL::addReward(Database::getConnexion($dbConfig), $_SESSION['email'], $difficulte);//Ne pas remplacer $difficulte par $enigme['difficulte'] car elle donne la valeur du prochain enigme
        $message = 'Bonne réponse';
    } else {
        $message = 'Mauvaise réponse';
    }
}

?>

<div style="flex: 1; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
    <input type="button" value="Demander pour de l'argent" onclick="alert('Demander pour de l\'argent')">
    <h1>Enigma</h1>
    <div>
        Nombre de pièces d'or : <span
            id="gold"><?= number_format(AccountDAL::selectGold($connexion, $_SESSION['email'])) . '&nbsp;🪙'; ?></span>
    </div>
</div>
<form id="answerEnigme" method="POST" action="">

    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; margin: 50px;">
        <div style="width: 45%; justify-items: center; border: 1px solid black; margin: 20px; padding: 5px;">
            <?php if ($enigme != null): ?>
                <h3><?php echo $enigme['enonce']; ?></h3>
            <?php else: ?>
                <h3>Désoler, il n'y a pas de quête pour le moment</h3>
            <?php endif ?>
        </div>
        <?php if ($reponses != null || !empty($reponses)): ?>
            <?php foreach ($reponses as $index => $reponse): ?>
                <label for="reponse<?= $index + 1; ?>" style="margin: 5px; padding: 5px;">
                    <!-- Si la reponse soumise est bonne, retourn la difficulté pour que l'argent soit calculer en fonction, sinon donne rien -->
                    <input type="radio" id="reponse<?= $index + 1; ?>" name="answer"
                        value="<?= $reponse['estBonneReponse'] == 1 ? $enigme['difficulte'] : null ?>"
                        placeholder="Votre réponse">
                    <?= $reponse['reponse']; ?>
                </label>
            <?php endforeach; ?>
            <button type="submit" style="width: 30%; margin:20px;">Valider la réponse</button>

            <div><?php echo $message ?></div>
        <?php else: ?>
            <button type="submit" style="width: 30%; margin:20px;">Nouvelle quête</button>
        <?php endif ?>
    </div>
</form>