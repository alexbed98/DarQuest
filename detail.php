<?php

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Database.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';
require_once 'src/AccountDAL.php';

function getCartSessionKey(): string
{
    if (!empty($_SESSION['id'])) {
        return 'panier_user_' . (int) $_SESSION['id'];
    }
    if (!empty($_SESSION['email'])) {
        return 'panier_user_' . md5(strtolower((string) $_SESSION['email']));
    }
    return 'panier_guest';
}

$connexion = Database::getConnexion($dbConfig);

// Récupérer l'item depuis le paramètre GET
$itemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = ($itemId !== false && $itemId !== null) ? ItemDAL::selectById($connexion, $itemId) : false;

// Ajout au panier depuis la page détail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item_id'])) {
    $postId = filter_input(INPUT_POST, 'add_item_id', FILTER_VALIDATE_INT);
    $qty    = max(1, (int) filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT));

    if ($postId !== false && $postId !== null) {
        $cartItem = ItemDAL::selectById($connexion, $postId);

        if ($cartItem !== false) {
            $cartSessionKey = getCartSessionKey();

            if (!isset($_SESSION[$cartSessionKey]) || !is_array($_SESSION[$cartSessionKey])) {
                $_SESSION[$cartSessionKey] = [];
            }

            $found = false;
            foreach ($_SESSION[$cartSessionKey] as &$entry) {
                if ((int) $entry['id'] === (int) $cartItem['idItem']) {
                    $entry['quantite'] += $qty;
                    $_SESSION['cart_notice'] = 'Quantite mise a jour dans votre panier.';
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $photo = (string) $cartItem['photo'];
                $image = ($photo !== '' && $photo[0] === '/')
                    ? $photo
                    : '/public/img/' . ltrim($photo, '/');

                $_SESSION[$cartSessionKey][] = [
                    'id'       => (int) $cartItem['idItem'],
                    'nom'      => (string) $cartItem['nom'],
                    'image'    => $image,
                    'prix'     => (float) $cartItem['prix'],
                    'quantite' => $qty,
                ];
                $_SESSION['cart_notice'] = 'Item ajoute au panier.';
            }

            if (!empty($_SESSION['id'])) {
                CartDAL::saveCart($connexion, (int) $_SESSION['id'], $_SESSION[$cartSessionKey]);
            }
        }
    }

    header('Location: ' . Page::Details->url() . '?id=' . $postId);
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

