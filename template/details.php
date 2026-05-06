<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';
require_once 'src/ItemRatingDAL.php';

$connexion = Database::getConnexion($dbConfig);

$id = isset($_GET['idItem']) ? intval($_GET['idItem']) : 0;


$stmt = $connexion->prepare("SELECT * FROM Items WHERE idItem = ?");
$stmt->execute([$id]);

$item = $stmt->fetch();

if (!$item) {
    echo "Item introuvable";
    exit;
}

$details = null;

switch ($item['typeItem']) {

    case 'A':
        $stmt2 = $connexion->prepare("SELECT * FROM Armes WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'R':
        $stmt2 = $connexion->prepare("SELECT * FROM Armures WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'P':
        $stmt2 = $connexion->prepare("SELECT * FROM Potions WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'S':
        $stmt2 = $connexion->prepare("SELECT * FROM Sorts WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;
}

$ratingStats = ItemRatingDAL::getItemAverage($connexion, $id);
$ratingAverage = (float) ($ratingStats['moyenne'] ?? 0);
$ratingCount = (int) ($ratingStats['total'] ?? 0);
$roundedAverage = (int) round($ratingAverage);
$userRating = !empty($_SESSION['id'])
    ? ItemRatingDAL::getUserRating($connexion, $id, (int) $_SESSION['id'])
    : null;

$ratingNotice = $_SESSION['rating_notice'] ?? null;
unset($_SESSION['rating_notice']);


// Truc pour les commentaires

$stmtComments = $connexion->prepare("
    SELECT j.nom, e.leCommentaire, e.idJoueur
    FROM Evaluations e
    JOIN Joueurs j ON j.idJoueur = e.idJoueur
    WHERE e.idItem = ?
    AND e.leCommentaire IS NOT NULL
    AND e.leCommentaire != ''
");
$stmtComments->execute([$id]);

$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_item_id'])) {
    if (!empty($_SESSION['id'])) {

        $idItem = (int) $_POST['comment_item_id'];
        $idJoueur = (int) $_SESSION['id'];
        $commentaire = trim($_POST['comment_text']);

        if (!empty($commentaire)) {

            $stmtInsert = $connexion->prepare("
                INSERT INTO Evaluations (idJoueur, idItem, nbEtoiles, leCommentaire)
                VALUES (?, ?, 0, ?)
                ON DUPLICATE KEY UPDATE leCommentaire = VALUES(leCommentaire)
            ");

            $stmtInsert->execute([$idJoueur, $idItem, $commentaire]);

            header("Location: detail.php?idItem=" . $idItem);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item_id'])) {
    if (!empty($_SESSION['id'])) {

        $idItem = (int) $_POST['edit_item_id'];
        $idJoueur = (int) $_SESSION['id'];
        $commentaire = trim($_POST['edit_comment_text']);

        if (!empty($commentaire)) {

            $stmtUpdate = $connexion->prepare("
                UPDATE Evaluations
                SET leCommentaire = ?
                WHERE idJoueur = ? AND idItem = ?
            ");

            $stmtUpdate->execute([$commentaire, $idJoueur, $idItem]);

            header("Location: detail.php?idItem=" . $idItem);
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item_id'])) {
    if (!empty($_SESSION['id'])) {

        $idItem = (int) $_POST['delete_item_id'];
        $idJoueur = (int) $_SESSION['id'];

        $stmtDelete = $connexion->prepare("
            DELETE FROM Evaluations
            WHERE idJoueur = ? AND idItem = ?
        ");

        $stmtDelete->execute([$idJoueur, $idItem]);

        header("Location: detail.php?idItem=" . $idItem);
        exit;
    }
}

$userHasCommented = false;

if (!empty($_SESSION['id'])) {

    $stmtCheck = $connexion->prepare("
        SELECT 1
        FROM Evaluations
        WHERE idJoueur = ? AND idItem = ?
        LIMIT 1
    ");

    $stmtCheck->execute([(int)$_SESSION['id'], $id]);
    $userHasCommented = (bool) $stmtCheck->fetchColumn();
}
// fin des trucs pour les commentaires


?>

<div class="mainContainer">
<!-- Côté gauche de la page -->
<div class="conteneur espacementGauche details-panel">
    <div class="item-title-row">
        <h2 class="item-title"><?= htmlspecialchars($item['nom']) ?></h2>
        <div class="title-rating" aria-label="Moyenne de <?= number_format($ratingAverage, 1) ?> sur 5">
            <div class="rating-stars-readonly">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $roundedAverage ? 'star-full' : 'star-empty' ?>">&#9733;</span>
                <?php endfor; ?>
            </div>
            <span class="rating-meta"><?= number_format($ratingAverage, 1) ?>/5 (<?= $ratingCount ?>)</span>
        </div>
    </div>

    <?php if (!empty($_SESSION['id'])): ?>
        <form method="POST" class="rating-inline-form" id="rating-inline-form">
            <input type="hidden" name="rate_item_id" value="<?= (int) $item['idItem'] ?>">
            <div class="rating-stars-input" aria-label="Votre evaluation">
                <?php for ($star = 1; $star <= 5; $star++): ?>
                    <input
                        type="radio"
                        id="star-inline-<?= $star ?>"
                        name="item_rating"
                        value="<?= $star ?>"
                        <?= $userRating === $star ? 'checked' : '' ?>
                        required
                    >
                    <label for="star-inline-<?= $star ?>" title="<?= $star ?> etoile<?= $star > 1 ? 's' : '' ?>">&#9733;</label>
                <?php endfor; ?>
            </div>
        </form>
        <?php if ($ratingNotice): ?>
            <p class="rating-notice-inline"><?= htmlspecialchars($ratingNotice) ?></p>
        <?php endif; ?>
    <?php endif; ?>

    <div class="details-info-list">
        <?php if ($item['typeItem'] == 'A'): ?>
            <p><strong>Efficacité : </strong><?= htmlspecialchars($details['efficacite']) ?></p>
            <p><strong>Genre : </strong><?= htmlspecialchars($details['genre']) ?></p>
            <p><strong>Description : </strong><?= htmlspecialchars($details['uneDescription']) ?></p>
        <?php endif; ?>

        <?php if ($item['typeItem'] == 'R'): ?>
            <p><strong>Matière : </strong><?= htmlspecialchars($details['matiere']) ?></p>
            <p><strong>Taille : </strong><?= htmlspecialchars($details['taille']) ?></p>
        <?php endif; ?>

        <?php if ($item['typeItem'] == 'P'): ?>
            <p><strong>Effet : </strong><?= htmlspecialchars($details['effet']) ?></p>
            <p><strong>Durée : </strong><?= $details['duree'] ?> tours</p>
        <?php endif; ?>

        <?php if ($item['typeItem'] == 'S'): ?>
            <p><strong>Instantané : </strong><?= $details['estInstantane'] ? 'Oui' : 'Non' ?></p>
            <p><strong>Rareté : </strong><?= $details['rarete'] ?></p>
            <p><strong>Type : </strong><?= $details['typeSort'] ?></p>
        <?php endif; ?>
    </div>


    <div class="conteneurFlex details-meta-row">
        <div class="flexRow details-stat">
            <p><strong>Quantité en stock: &nbsp;</strong></p>
            <p class="details-value"><?= $item['quantiteStock'] ?></p>
        </div>

        <div class="flexRow details-stat details-price">
            <p><strong>Prix unitaire: &nbsp;</strong></p>
            <p id="prixItem" class="details-value"><?= $item['prix'] ?></p>
            <p class="details-coin">🪙</p>
        </div>
    </div>
    

    
    <div class="details-buy-zone">
        <?php $outOfStock = (int) ($item['quantiteStock'] ?? 0) <= 0; ?>
        <form method="POST" class="details-buy-form">
            <input type="hidden" name="add_item_id" value="<?= $item['idItem'] ?>">
            <input type="number" name="update_qty" class="panier-qty-input auto-submit-input details-qty-input" value="<?= $outOfStock ? 0 : 1 ?>" min="1" max="<?= (int) $item['quantiteStock'] ?>" <?= $outOfStock ? 'disabled' : '' ?>>
            <button type="submit" class="item-add-btn" <?= $outOfStock ? 'disabled' : '' ?>>Ajouter au panier</button>
            <div class="flexRow details-total-wrap">
                <p><strong>Total: </strong></p>
                <p id="prixTotal" class="details-value"></p>
            </div>
        </form>
    </div>

</div>

<!-- Côté droit de la page -->
<div class="conteneur details-image-panel">
    <div class="conteneur details-image-wrap">
        <img src="<?= IMG ?>/items/<?= htmlspecialchars($item['photo']) ?>" 
            alt="image item" 
            class="details-image">
    </div>
</div>

</div>


<div class="conteneur comments-section">
    <h3>Commentaires</h3>

    <?php if (empty($comments)): ?>
        <p>Aucun commentaire pour cet item.</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment-card">

    <div class="comment-header">
        <strong><?= htmlspecialchars($comment['nom']) ?></strong>

        <?php if (!empty($_SESSION['id']) && $_SESSION['id'] == $comment['idJoueur']): ?>
            <div class="comment-actions">
                <button class="edit-btn">🪶</button>

                <form method="POST" class="delete-form" onsubmit="return confirm('Supprimer ce commentaire ?');">
                    <input type="hidden" name="delete_item_id" value="<?= (int)$item['idItem'] ?>">
                    <button type="submit" class="delete-btn">🗑️</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Texte -->
    <p class="comment-text">
        <?= htmlspecialchars($comment['leCommentaire']) ?>
    </p>

    <!-- Formulaire caché -->
    <?php if (!empty($_SESSION['id']) && $_SESSION['id'] == $comment['idJoueur']): ?>
        <form method="POST" class="edit-form" style="display:none;">
            <input type="hidden" name="edit_item_id" value="<?= (int)$item['idItem'] ?>">

            <textarea name="edit_comment_text" class="edit-textarea"><?= htmlspecialchars($comment['leCommentaire']) ?></textarea>

            <div class="edit-actions">
                <button type="submit" class="save-btn">Enregistrer</button>
                <button type="button" class="cancel-edit">Annuler</button>
            </div>
        </form>
    <?php endif; ?>

</div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($_SESSION['id'])): ?>
    <div class="add-comment-section">
        <button 
            id="toggleCommentForm" 
            class="item-add-btn"
            <?= $userHasCommented ? 'disabled' : '' ?>
        >
            Ajouter un commentaire
        </button>

        <?php if ($userHasCommented): ?>
            <p class="comment-limit-text">Tu as déjà commenté cet item.</p>
        <?php endif; ?>

        <form method="POST" id="commentForm" class="comment-form" style="display: none;">
            <input type="hidden" name="comment_item_id" value="<?= (int)$item['idItem'] ?>">

            <textarea 
                name="comment_text" 
                maxlength="255"
                required
                placeholder="Écris ton commentaire..."
                class="comment-textarea"
                id="commentText">
            </textarea>

            <button type="submit" class="item-add-btn">
                Envoyer
            </button>
        </form>
    </div>
<?php endif; ?>


<script>
    const prixUnitaire = parseInt(document.querySelector('#prixItem').textContent);
    const inputQty = document.querySelector('.panier-qty-input');
    const prixTotalElement = document.querySelector('#prixTotal');

    function calculPrixTotal() {
        const quantite = parseInt(inputQty.value);
        const prixTotal = prixUnitaire * quantite;

        prixTotalElement.textContent = prixTotal + ' 🪙';
    }

    calculPrixTotal();

    inputQty.addEventListener('input', calculPrixTotal);

    const ratingInlineForm = document.querySelector('#rating-inline-form');
    if (ratingInlineForm) {
        const ratingInputs = ratingInlineForm.querySelectorAll('input[name="item_rating"]');
        const ratingLabels = ratingInlineForm.querySelectorAll('.rating-stars-input label');
        const ratingStarsWrap = ratingInlineForm.querySelector('.rating-stars-input');

        function setFilledStars(value) {
            ratingLabels.forEach((label) => {
                const targetValue = parseInt(label.getAttribute('for').replace('star-inline-', ''), 10);
                label.classList.toggle('is-filled', targetValue <= value);
            });
        }

        const checkedInput = ratingInlineForm.querySelector('input[name="item_rating"]:checked');
        setFilledStars(checkedInput ? parseInt(checkedInput.value, 10) : 0);

        ratingLabels.forEach((label) => {
            label.addEventListener('mouseenter', () => {
                const hoverValue = parseInt(label.getAttribute('for').replace('star-inline-', ''), 10);
                setFilledStars(hoverValue);
            });
        });

        ratingStarsWrap.addEventListener('mouseleave', () => {
            const selected = ratingInlineForm.querySelector('input[name="item_rating"]:checked');
            setFilledStars(selected ? parseInt(selected.value, 10) : 0);
        });

        ratingInputs.forEach((input) => {
            input.addEventListener('change', () => {
                setFilledStars(parseInt(input.value, 10));
                ratingInlineForm.submit();
            });
        });
    }


    const toggleBtn = document.querySelector('#toggleCommentForm');
    const commentForm = document.querySelector('#commentForm');

    if (toggleBtn && commentForm) {
        toggleBtn.addEventListener('click', () => {
            commentForm.style.display =
                commentForm.style.display === 'none' ? 'block' : 'none';
        });
    }

    const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.comment-card');
            const text = card.querySelector('.comment-text');
            const form = card.querySelector('.edit-form');

            text.style.display = 'none';
            form.style.display = 'block';
        });
    });

    document.querySelectorAll('.cancel-edit').forEach(btn => {
    btn.addEventListener('click', () => {
        const card = btn.closest('.comment-card');
        card.querySelector('.edit-form').style.display = 'none';
        card.querySelector('.comment-text').style.display = 'block';
    });
});

    const btn = document.querySelector('#toggleCommentForm');
    const textarea = document.querySelector('#commentText');

    if (btn && textarea) {
        btn.addEventListener('click', () => {
            setTimeout(() => {
                textarea.focus();
                textarea.value = "";
            }, 50);
        });
    }
</script>


