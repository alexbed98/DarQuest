<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';
require_once 'src/StatistiqueDAL.php';
require_once 'src/CategoryDAL.php';

$connexion = Database::getConnexion($dbConfig);
$peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0;
$categories = CategoryDAL::select($connexion);
$filtreDifficulte = '';
$filtreCategorie = '';
$message = '';
$errorMessage = '';
$enigme = [];
$reponses = [];

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
?>

<section class="enigme-page">
    <div class="enigme-header">
        <form method="POST" action="">
            <?php if ($nbDemandes < 3): ?>
                <button type="submit" name="demande_argent" value="1" class="enigme-help-btn">
                    Demander des pièces
                </button>
            <?php else: ?>
                <button type="button" class="enigme-help-btn" disabled>
                    Limite de demande de pièces atteinte
                </button>
            <?php endif; ?>
        </form>

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
    
    <form id="changeFiltre" method="get" action="" style="align-self: center;">
        <select id="filtreDifficulte" name="filtreDifficulte" onchange="submit()">
            <option value="None" <?= htmlspecialchars($filtreDifficulte == '' ? 'Selected' : ''); ?>>Aucune préférence de
                difficulté</option>
            <option value="F" <?= htmlspecialchars($filtreDifficulte == 'F' ? 'Selected' : ''); ?>>Facile</option>
            <option value="M" <?= htmlspecialchars($filtreDifficulte == 'M' ? 'Selected' : ''); ?>>Moyen</option>
            <option value="D" <?= htmlspecialchars($filtreDifficulte == 'D' ? 'Selected' : ''); ?>>Difficile</option>
        </select>
        <select id="filtreCategorie" name="filtreCategorie" onchange="submit()">
            <option value="None" <?= htmlspecialchars($filtreCategorie == '' ? 'Selected' : ''); ?>>Aucune préférence de
                catégorie</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars((string) $cat['idCategorie']); ?>" <?= htmlspecialchars($filtreCategorie == $cat['idCategorie'] ? 'Selected' : ''); ?>><?= htmlspecialchars((string) $cat['nomCategorie']); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
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
                            <input type="radio" id="reponse<?= $index + 1; ?>" name="answer"
                                value="<?= htmlspecialchars((string) $reponse['estBonneReponse']); ?>" required>
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