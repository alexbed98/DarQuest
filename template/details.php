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
</script>


