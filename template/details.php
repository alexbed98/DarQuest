<?php

include_once 'core/Database.php';
include_once 'src/initialization.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';

$connexion = Database::getConnexion($dbConfig);

$id = isset($_GET['idItem']) ? intval($_GET['idItem']) : 0;

$stmt = $connexion->prepare("SELECT * FROM items WHERE idItem = ?");
$stmt->execute([$id]);

$item = $stmt->fetch();

if (!$item) {
    echo "Item introuvable";
    exit;
}

$details = null;

switch ($item['typeItem']) {

    case 'A':
        $stmt2 = $connexion->prepare("SELECT * FROM armes WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'R':
        $stmt2 = $connexion->prepare("SELECT * FROM armures WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'P':
        $stmt2 = $connexion->prepare("SELECT * FROM potions WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;

    case 'S':
        $stmt2 = $connexion->prepare("SELECT * FROM sorts WHERE idItem = ?");
        $stmt2->execute([$id]);
        $details = $stmt2->fetch(PDO::FETCH_ASSOC);
        break;
}


/*
Code ajouter panier
*/

function getCartSessionKey(): string
{
    if (!empty($_SESSION['id'])) {
        return 'panier_user_' . (int) $_SESSION['id'];
    }

    // Fallback si l'id n'est pas encore disponible en session.
    if (!empty($_SESSION['email'])) {
        return 'panier_user_' . md5(strtolower((string) $_SESSION['email']));
    }

    return 'panier_guest';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item_id'])) {

    $itemId = filter_input(INPUT_POST, 'add_item_id', FILTER_VALIDATE_INT);

    if ($itemId) {

        $item = ItemDAL::selectById($connexion, $itemId);

        if ($item !== false) {

            $cartSessionKey = getCartSessionKey();

            if (!isset($_SESSION[$cartSessionKey])) {
                $_SESSION[$cartSessionKey] = [];
            }

            $found = false;

            foreach ($_SESSION[$cartSessionKey] as &$cartItem) {
                if ($cartItem['id'] == $item['idItem']) {
                    $cartItem['quantite']++;
                    $_SESSION['cart_notice'] = 'Quantité mise à jour.';
                    $found = true;
                    break;
                }
            }
            unset($cartItem);

            if (!$found) {

                $photo = (string) $item['photo'];
                $image = ($photo !== '' && $photo[0] === '/')
                    ? $photo
                    : '/public/img/' . ltrim($photo, '/');

                $_SESSION[$cartSessionKey][] = [
                    'id' => (int) $item['idItem'],
                    'nom' => (string) $item['nom'],
                    'image' => $image,
                    'prix' => (float) $item['prix'],
                    'quantite' => 1,
                ];

                $_SESSION['cart_notice'] = 'Item ajouté au panier.';
            }

            if (!empty($_SESSION['id'])) {
                CartDAL::saveCart($connexion, (int) $_SESSION['id'], $_SESSION[$cartSessionKey]);
            }
        }
    }

    header('Location: details.php?idItem=' . $itemId);
    exit;
}


?>

<div class="mainContainer">
<!-- Côté gauche de la page -->
<div class="conteneur">
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
            <p><strong>Quantité en stock: </strong></p>
            <p><?= $item['quantiteStock'] ?></p>
        </div>

        <div class="flexRow">
            <p style="margin-left: 5em;"><strong>Prix: </strong></p>
            <p id="prixItem"><?= $item['prix'] ?></p>
            <p>🪙</p>
        </div>
    </div>
    
    <div class="conteneurFlex">
        <input type="number" name="update_qty" class="panier-qty-input auto-submit-input" value="1" min="1">
        <div class="flexRow">
            <p style="margin-left: 9em;"><strong>Total: </strong></p>
            <p id="prixTotal"></p>
        </div>
    </div>
    
    <div class="conteneurFlex" style="margin-top: 1em;">
        <form method="POST">
            <input type="hidden" name="add_item_id" value="<?= $item['idItem'] ?>">
            <button type="submit">Ajouter au panier</button>
        </form>

        <div id="nbPiece">
            <p style="margin-left: 3em;"><strong>Nombre de pièces: </strong></p>
            <p>500🪙</p>
        </div>
    </div>
</div>

<!-- Côté droit de la page -->
<div class="conteneur">

    <div class="conteneur" style="display: flex; justify-content: center; align-items: center;">
        <img src="/public/img/items/<?= htmlspecialchars($item['photo']) ?>" 
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


