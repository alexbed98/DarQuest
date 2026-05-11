<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/AccountDAL.php';
require_once 'src/EnigmeDAL.php';
require_once 'src/StatistiqueDAL.php';
require_once 'src/CategoryDAL.php';

$connexion = Database::getConnexion($dbConfig);
$message = '';
$errorMessage = '';
$enigme = [];
$reponses = [];
$categories = CategoryDAL::selectAll($connexion);

$allowedDifficultes = ['F', 'M', 'D'];
$selectedCategorie = isset($_GET['categorie']) ? strtoupper(trim((string) $_GET['categorie'])) : '';
$selectedDifficulte = isset($_GET['difficulte']) ? strtoupper(trim((string) $_GET['difficulte'])) : '';
$shouldLoadQuestion = isset($_GET['tirer_question']) || $selectedCategorie !== '' || $selectedDifficulte !== '';
$isRandomRequest = isset($_GET['tirer_question']);

$allowedCategories = array_map(
    static fn(array $cat): string => strtoupper((string) ($cat['idCategorie'] ?? '')),
    $categories
);

if ($selectedCategorie !== '' && !in_array($selectedCategorie, $allowedCategories, true)) {
    $selectedCategorie = '';
}

if ($selectedDifficulte !== '' && !in_array($selectedDifficulte, $allowedDifficultes, true)) {
    $selectedDifficulte = '';
}

if ($isRandomRequest) {
    // Le mode aleatoire ignore volontairement les filtres choisis.
    $selectedCategorie = '';
    $selectedDifficulte = '';
}

if (IS_POST) {
    $postedCategorie = isset($_POST['categorie']) ? strtoupper(trim((string) $_POST['categorie'])) : '';
    $postedDifficulte = isset($_POST['difficulte']) ? strtoupper(trim((string) $_POST['difficulte'])) : '';

    if ($postedCategorie !== '' && in_array($postedCategorie, $allowedCategories, true)) {
        $selectedCategorie = $postedCategorie;
    }

    if ($postedDifficulte !== '' && in_array($postedDifficulte, $allowedDifficultes, true)) {
        $selectedDifficulte = $postedDifficulte;
    }
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
            $shouldLoadQuestion = true;
        }
    }

    $playerGold = (int) (AccountDAL::selectGold($connexion, $_SESSION['email']) ?: 0);
    $playerHealth = (int) (AccountDAL::selectPointVie($connexion, $_SESSION['email']) ?: 0);
    $peutJouer = AccountDAL::selectHp($connexion, $_SESSION['email']) > 0;

    if (EnigmeDAL::countAllEnigme($connexion)) {
        if ($shouldLoadQuestion) {
            $enigme = EnigmeDAL::selectRandomEnigmeNonReussieFiltree(
                $connexion,
                $idJoueur,
                $selectedCategorie !== '' ? $selectedCategorie : null,
                $selectedDifficulte !== '' ? $selectedDifficulte : null
            );
            $reponses = $enigme ? EnigmeDAL::selectAllAnswers($connexion, $enigme['idEnigme']) : [];

            if ($enigme && !$reponses) {
                $errorMessage = "Cette quete manque ses reponses.";
            } elseif ($enigme) {
                shuffle($reponses);
            } else {
                $errorMessage = 'Aucune quete disponible pour ces filtres. Essayez une autre categorie ou difficulte.';
            }
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

    <?php if (!empty($_SESSION['email'])): ?>
        <form method="GET" action="" class="enigme-filters">
            <div class="enigme-filter-group">
                <label for="enigme-categorie">Categorie</label>
                <select id="enigme-categorie" name="categorie">
                    <option value="">Toutes les categories</option>
                    <?php foreach ($categories as $categorie): ?>
                        <?php $idCategorie = strtoupper((string) ($categorie['idCategorie'] ?? '')); ?>
                        <option value="<?= htmlspecialchars($idCategorie) ?>" <?= $selectedCategorie === $idCategorie ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($categorie['nomCategorie'] ?? $idCategorie)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="enigme-filter-group">
                <label for="enigme-difficulte">Difficulte</label>
                <select id="enigme-difficulte" name="difficulte">
                    <option value="">Toutes les difficultes</option>
                    <option value="F" <?= $selectedDifficulte === 'F' ? 'selected' : '' ?>>Facile</option>
                    <option value="M" <?= $selectedDifficulte === 'M' ? 'selected' : '' ?>>Moyen</option>
                    <option value="D" <?= $selectedDifficulte === 'D' ? 'selected' : '' ?>>Difficile</option>
                </select>
            </div>

            <div class="enigme-filter-actions">
                <button type="submit" class="enigme-random-btn">Appliquer les criteres</button>
                <button type="submit" name="tirer_question" value="1" class="enigme-random-btn">Question aleatoire</button>
            </div>
        </form>
    <?php endif; ?>

    <form id="answerEnigme" method="POST" action="" class="enigme-form">
        <?php if ($peutJouer): ?>
            <?php if ($enigme): ?>
                <div class="enigme-question-card">
                    <h3><?= htmlspecialchars((string) $enigme['enonce']); ?></h3>
                    <input type="hidden" name="idEnigme" value="<?= htmlspecialchars((string) $enigme['idEnigme']); ?>">
                    <input type="hidden" name="categorie" value="<?= htmlspecialchars($selectedCategorie); ?>">
                    <input type="hidden" name="difficulte" value="<?= htmlspecialchars($selectedDifficulte); ?>">
                    <input type="hidden" name="tirer_question" value="1">
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