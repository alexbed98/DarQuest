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
$filtre = '';
$enigme = [];
$reponses = [];
$message = '';
$errorMessage = '';

if (isset($_GET['filtreDifficulte'])) {
    $filtre = $_GET['filtreDifficulte'];
    EnigmeDAL::setFiltre($filtre);
}

if (EnigmeDAL::countAllEnigme($connexion)) { //Verifie s'il y a des enigmes (Return true s'il y en a)
    $enigme = EnigmeDAL::selectRandomEnigme($connexion);
    $reponses = $enigme != null ? EnigmeDAL::selectAllAnswers($connexion, $enigme['idEnigme']) : [];

    if (!$reponses) //S'il l'egnime n'a pas de reponses, affiche un message d'erreur
        $errorMessage = "Cette quête manque ses réponses";
    else
        $errorMessage = "";

    shuffle($reponses);

} else {
    $errorMessage = "Désoler, il n'y a pas de quête pour le moment";
}

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

$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0; //Update son acces au jeu
?>

<script>
    function DifficultyFiltreChange() {
        document.getElementById('changeDifficultyFiltre').submit();
    }

</script>

<div style="flex: 1; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
    <form id="changeDifficultyFiltre" style="display: flex;flex-direction:column" method="GET" action="">
        <input type="button" value="Demander pour de l'argent" onclick="alert('Demander pour de l\'argent')">
        <label for="filtreDifficulte">Préférence de difficulté</label>
        <select id="filtreDifficulte" name="filtreDifficulte" onchange="DifficultyFiltreChange()">
            <option value="None" <?php echo EnigmeDAL::$filtleDifficulte == "" ? 'Selected' : '' ?>>Aucune Preference
            </option>
            <!-- HardCoder -->
            <option value="F" <?php echo $filtre == "F" ? 'Selected' : '' ?>>Facile</option>
            <option value="M" <?php echo $filtre == "M" ? 'Selected' : '' ?>>Moyen</option>
            <option value="D" <?php echo $filtre == "D" ? 'Selected' : '' ?>>Difficile</option>
        </select>
    </form>
    <h1>Enigma</h1>
    <div>
        Nombre de pièces d'or : <span
            id="gold"><?= number_format(AccountDAL::selectGold($connexion, $_SESSION['email'])) . '&nbsp;🥇'; ?></span>
    </div>
</div>

<form id="answerEnigme" method="POST" action="">

    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; margin: 50px;">
        <?php if ($peutJouer): ?><!-- Si le joueur a suffisament d'hp, affiche le jeu -->
            <?php if ($enigme): ?> <!-- S'il y a une quete, affiche sa question -->
                <div style="width: 45%; justify-items: center; border: 1px solid black; margin: 20px; padding: 5px;">
                    <h3><?php echo $enigme['enonce']; ?></h3>
                    <input type="hidden" name="difficulte" value="<?= $enigme['difficulte'] ?>">
                    <input type="hidden" name="idEnigme" value="<?= $enigme['idEnigme'] ?>">
                </div>
            <?php endif ?>

            <?php if ($enigme && $reponses): ?><!-- S'il y a une quete ET cette quete a des reponses, affiche toute ses reponses associé -->
                <?php foreach ($reponses as $index => $reponse): ?>
                    <label for="reponse<?= $index + 1; ?>" style="margin: 5px; padding: 5px;">
                        <!-- Si la reponse soumise est bonne, retourn la difficulté pour que l'argent soit calculer en fonction, sinon donne rien -->
                        <input type="radio" id="reponse<?= $index + 1; ?>" name="answer" value="<?= $reponse['estBonneReponse'] ?>"
                            placeholder="Votre réponse">
                        <?= $reponse['reponse']; ?>
                    </label>
                <?php endforeach; ?>
                <button type="submit" style="width: 30%; margin:20px;">Valider la réponse</button>

            <?php else: ?>

                <?php if (EnigmeDAL::countAllEnigme(Database::getConnexion($dbConfig))): ?>
                    <!-- S'il n'y a pas de quete, n'affiche pas le button pour une nouvelle quete -->
                    <button type="submit" style="width: 30%; margin:20px;">Nouvelle quête</button>
                <?php endif ?>

            <?php endif ?>

        <?php else: ?><!-- Sinon, affiche un message pour indiquer au joueur qu'il n'a pas assé d'hp pour jouer -->
            <div style="width: 45%; justify-items: center; border: 1px solid black; margin: 20px; padding: 5px;">
                <h3>Vous n'avez pas assée hp</h3>
            </div>
        <?php endif ?>

        <div><?php echo $message ?></div>
        <div><?php echo $errorMessage ?></div>

    </div>
</form>