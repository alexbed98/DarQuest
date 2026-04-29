<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';

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


?>

<div class="mainContainer">
<!-- Côté gauche de la page -->
<div class="conteneur, espacementGauche">
    <h2><?= htmlspecialchars($item['nom']) ?></h2>

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


    <div class="conteneurFlex">
        <div class="flexRow">
            <p><strong>Quantité en stock: &nbsp;</strong></p>
            <p><?= $item['quantiteStock'] ?></p>
        </div>

        <div class="flexRow">
            <p style="margin-left: 2em;"><strong>Prix unitaire: &nbsp;</strong></p>
            <p id="prixItem"><?= $item['prix'] ?></p>
            <p>🪙</p>
        </div>
    </div>
    

    
    <div>
        <form method="POST" style="display: flex; margin-top: 1em;">
            <input type="hidden" name="add_item_id" value="<?= $item['idItem'] ?>">
            <input type="number" name="update_qty" class="panier-qty-input auto-submit-input" value="1" min="1" style="margin-right: 0.2em;">
            <button type="submit" class="boutonAjouter">Ajouter au panier</button>
            <div class="flexRow">
                <p style="margin-left: 0.8em;"><strong>Total: </strong></p>
                <p id="prixTotal"></p>
            </div>
        </form>
    </div>
</div>

<!-- Côté droit de la page -->
<div class="conteneur">
    <div class="conteneur" style="display: flex; justify-content: center; align-items: center;">
        <img src="<?= IMG ?>/items/<?= htmlspecialchars($item['photo']) ?>" 
            alt="image item" 
            style="height: 300px; width: 500px;">
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
</script>


