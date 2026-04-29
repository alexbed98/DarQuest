<?php

// les require et les include
// a decommenter quand on les utilisent

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';
include_once 'core/Database.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';

$connexion = Database::getConnexion($dbConfig);

// Ajouter un item au panier (doit etre traite avant tout output HTML)
if (IS_POST && isset($_POST['add_item_id'])) {

    $itemId = filter_input(INPUT_POST, 'add_item_id', FILTER_VALIDATE_INT);
    $qty = filter_input(INPUT_POST, 'update_qty', FILTER_VALIDATE_INT);
    $qty = ($qty && $qty > 0) ? $qty : 1;

    if ($itemId) {
        $item = ItemDAL::selectById($connexion, $itemId);

        if ($item !== false) {
            if ((int) ($item['quantiteStock'] ?? 0) <= 0) {
                $_SESSION['cart_notice'] = 'Item en rupture de stock.';
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }

            if (!empty($_SESSION['id'])) {
                $cartSessionKey = 'panier_user_' . (int) $_SESSION['id'];
            } elseif (!empty($_SESSION['email'])) {
                $cartSessionKey = 'panier_user_' . md5(strtolower((string) $_SESSION['email']));
            } else {
                $cartSessionKey = 'panier_guest';
            }

            if (!isset($_SESSION[$cartSessionKey]) || !is_array($_SESSION[$cartSessionKey])) {
                $_SESSION[$cartSessionKey] = [];
            }

            $found = false;
            foreach ($_SESSION[$cartSessionKey] as &$cartItem) {
                if ((int) ($cartItem['id'] ?? 0) === (int) $item['idItem']) {
                    $cartItem['quantite'] += $qty;
                    $_SESSION['cart_notice'] = 'Quantite mise a jour.';
                    $found = true;
                    break;
                }
            }
            unset($cartItem);

            if (!$found) {
                $photo = (string) $item['photo'];
                $image = ($photo !== '' && $photo[0] === '/')
                    ? $photo
                    : IMG . '/items/' . ltrim($photo, '/');

                $_SESSION[$cartSessionKey][] = [
                    'id' => (int) $item['idItem'],
                    'nom' => (string) $item['nom'],
                    'image' => $image,
                    'prix' => (float) $item['prix'],
                    'quantite' => $qty,
                ];

                $_SESSION['cart_notice'] = 'Item ajoute au panier.';
            }

            if (!empty($_SESSION['id'])) {
                CartDAL::saveCart($connexion, (int) $_SESSION['id'], $_SESSION[$cartSessionKey]);
            }
        }
    }

    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// identification de la page active
 const ACTIVE_PAGE = Page::Details;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/details.css',
           '/public/css/panier.css',
           ];

?>
<!DOCTYPE html>
<html lang="fr">

<!--Bloc entête document-Head block-->
<?php include_once TEMPLATE . '/head.php'; ?>
<!--Bloc entête document-Head block-->

<body>
    
    <!--Contenant principal pour largeur du contenu-Main container-->
    <div class="container">

        <!--Bloc entête-Header block-->
        <?php include_once TEMPLATE . '/header.php'; ?>
        <!--Bloc entête-Header block-->
        
        <main>

            <!--Bloc ?-->
            <?php include_once TEMPLATE . '/details.php'; ?>
            <!--Bloc ?-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->
   
</body>
</html>

