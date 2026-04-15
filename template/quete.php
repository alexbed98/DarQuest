<?php
include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';

$enigme = EnigneDAL::selectRandomEnigme(Database::getConnexion($dbConfig));
$reponses = EnigneDAL::selectAllAnswers(Database::getConnexion($dbConfig), $enigme['idEnigme']);



?>
<script>
function s(){
<?php
    $message = '';
    $a = $_POST['answer'] ?? null;
    if ($a != null) {
        $message = 'not null';
        if ($a == 1) {
            AccountDAL::addReward(Database::getConnexion($dbConfig), $_SESSION['email'], $enigme['difficulte']);
            $message = $a . '    ' . $enigme['difficulte'];
        } 
    }
    
?>
    document.getElementById('answerEnigme').submit();

}
</script>

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
            <h3><?php echo $enigme['enonce']; ?></h3>
        </div>

        <?php foreach ($reponses as $index => $reponse): ?>
            <label for="reponse<?= $index + 1; ?>" style="margin: 5px; padding: 5px;">
                <input type="radio" id="reponse<?= $index + 1; ?>" name="answer" value="<?= $reponse['estBonneReponse'] ?>" placeholder="Votre réponse">
                <?= $reponse['reponse']; ?>
            </label>
        <?php endforeach; ?>

        <button type="submit" style="width: 30%; margin:20px;" onclick="s()">Valider la réponse</button>
    </div>
</form>

<div><?php echo $message ?></div>