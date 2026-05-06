<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';
require_once 'src/StatistiqueDAL.php';
require_once 'src/CategoryDAL.php';

$connexion = Database::getConnexion($dbConfig);
<<<<<<< Choix-Quete
$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0;
$filtreDifficulte = '';
$filtreCategorie = '';
$categories = CategoryDAL::select($connexion);
$enigme = [];
$reponses = [];
=======
>>>>>>> main
$message = '';
$errorMessage = '';
$enigme = [];
$reponses = [];

<<<<<<< Choix-Quete
// set le filtre pour la difficulte avec l'url
if (isset($_GET['filtreDifficulte'])) {
    $filtreDifficulte = $_GET['filtreDifficulte'];
    EnigmeDAL::setFiltreDifficulte($filtreDifficulte);
}
// set le filtre pour le categorie avec l'url
if (isset($_GET['filtreCategorie'])) {
    $filtreCategorie = $_GET['filtreCategorie'];
    EnigmeDAL::setFiltreCategorie($filtreCategorie);
}

if (EnigmeDAL::countAllEnigme($connexion)) { //Verifie s'il y a des enigmes (Return true s'il y en a)
    $enigme = EnigmeDAL::selectRandomEnigme($connexion);
    $reponses = $enigme != null ? EnigmeDAL::selectAllAnswers($connexion, $enigme['idEnigme']) : [];

    if (!$reponses) //S'il l'egnime n'a pas de reponses, affiche un message d'erreur
        $errorMessage = "Cette quête manque ses réponses";
    else
        $errorMessage = "";
=======
if (empty($_SESSION['email'])) {
    $errorMessage = 'Vous devez etre connecte pour acceder aux quetes.';
    $peutJouer = false;
    $playerGold = 0;
    $playerHealth = 0;
    $nbDemandes = 0;
} else {
    $joueur = AccountDAL::selectByEmail($connexion, $_SESSION['email']);
    $idJoueur = (int) $joueur['idJoueur'];
    $nbDemandes = (int) EnigmeDAL::countDemandesByJoueur($connexion, $idJoueur);

    if (IS_POST) {
        if (isset($_POST['demande_argent'])) {
            if ($nbDemandes < 3) {
                EnigmeDAL::insertDemande($connexion, $idJoueur);
                $nbDemandes = (int) EnigmeDAL::countDemandesByJoueur($connexion, $idJoueur);
                $message = "Demande envoyee a l'admin.";
            } else {
                $errorMessage = "Vous avez atteint la limite de 3 demandes.";
            }
        }
>>>>>>> main

        $bonOuPas = filter_input(INPUT_POST, 'answer', FILTER_VALIDATE_INT);
        $idEnigme = filter_input(INPUT_POST, 'idEnigme', FILTER_VALIDATE_INT);

        if ($bonOuPas !== null && $bonOuPas !== false && $idEnigme) {
            if ($bonOuPas === 1) {
                $message = AccountDAL::addReward($connexion, $_SESSION['email'], (string) $idEnigme);
            } else {
                $message = AccountDAL::takeDamage($connexion, $_SESSION['email'], (string) $idEnigme);
            }
        }
    }

    $playerGold = (int) (AccountDAL::selectGold($connexion, $_SESSION['email']) ?: 0);
    $playerHealth = (int) (AccountDAL::selectPointVie($connexion, $_SESSION['email']) ?: 0);
    $peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0;

    if (EnigmeDAL::countAllEnigme($connexion)) {
        $enigme = EnigmeDAL::selectRandomEnigmeNonReussie($connexion, $idJoueur);
        $reponses = $enigme ? EnigmeDAL::selectAllAnswers($connexion, $enigme['idEnigme']) : [];

        if (!$reponses) {
            $errorMessage = "Cette quete manque ses reponses.";
        } else {
            shuffle($reponses);
        }
    } elseif ($errorMessage === '') {
        $errorMessage = "Desole, il n'y a pas de quete pour le moment.";
    }
}
<<<<<<< Choix-Quete

$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0; //Update son acces au jeu
?>

<div style="flex: 1; display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
    <form id="changeFiltre" style="display: flex;flex-direction:column" method="GET" action="">
        <input type="button" value="Demander pour de l'argent" onclick="alert('Demander pour de l\'argent')">
        <label for="filtreDifficulte">Préférence de difficulté</label>
        <select id="filtreDifficulte" name="filtreDifficulte" onchange="submit()">
            <option value="None" <?php echo $filtreDifficulte == "" ? 'Selected' : '' ?>>Aucune Preference
            </option>
            <!-- HardCoder -->
            <option value="F" <?php echo $filtreDifficulte == "F" ? 'Selected' : '' ?>>Facile</option>
            <option value="M" <?php echo $filtreDifficulte == "M" ? 'Selected' : '' ?>>Moyen</option>
            <option value="D" <?php echo $filtreDifficulte == "D" ? 'Selected' : '' ?>>Difficile</option>
        </select>
        <label for="filtreCategorie">Préférence de Categorie</label>
        <select id="filtreCategorie" name="filtreCategorie" onchange="submit()">
            <option value="None" <?php echo $filtreCategorie == "" ? 'Selected' : '' ?>>Aucune Preference
            </option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['idCategorie']?>" <?php echo $filtreCategorie == $cat['idCategorie'] ? 'Selected' : '' ?>><?= $cat['nomCategorie'] ?></option>
            <?php endforeach ?>
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

=======
?>

<section class="enigme-page">
    <div class="enigme-header">
        <form method="POST" action="">
            <?php if ($nbDemandes < 3): ?>
                <button type="submit" name="demande_argent" value="1" class="enigme-help-btn">
                    Demander des pièces
                </button>
>>>>>>> main
            <?php else: ?>
                <button type="button" class="enigme-help-btn" disabled>
                    Limite de demande de pièces atteinte
                </button>
            <?php endif; ?>
        </form>

<<<<<<< Choix-Quete
                <?php if (EnigmeDAL::countAllEnigme(Database::getConnexion($dbConfig))): ?>
                    <!-- S'il n'y a pas de quete, n'affiche pas le button pour une nouvelle quete -->
                    <button type="submit" style="width: 30%; margin:20px;">Nouvelle quête</button>
                <?php endif ?>
=======
        <h1 class="enigme-title">Quete Enigme</h1>
>>>>>>> main

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
                    <h3><?= htmlspecialchars((string) $enigme['enonce']); ?></h3>
                    <input type="hidden" name="idEnigme" value="<?= htmlspecialchars((string) $enigme['idEnigme']); ?>">
                </div>
            <?php endif; ?>

            <?php if ($enigme && $reponses): ?>
                <div class="enigme-answers">
                    <?php foreach ($reponses as $index => $reponse): ?>
                        <label for="reponse<?= $index + 1; ?>" class="enigme-answer-option">
                            <input
                                type="radio"
                                id="reponse<?= $index + 1; ?>"
                                name="answer"
                                value="<?= htmlspecialchars((string) $reponse['estBonneReponse']); ?>"
                                required
                            >
                            <span><?= htmlspecialchars((string) $reponse['reponse']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" class="enigme-submit-btn">Valider la reponse</button>
            <?php elseif ($errorMessage !== ''): ?>
                <div class="enigme-error"><?= htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="enigme-error">
                <?= htmlspecialchars($errorMessage !== '' ? $errorMessage : "Vous n'avez pas assez de points de vie pour jouer."); ?>
            </div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="enigme-message"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </form>
</section>