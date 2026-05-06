<?php
require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'src/ItemDAL.php';
require_once 'src/CartDAL.php';
require_once 'core/Email.php';

// Chaque utilisateur connecté obtient une clé de panier dédiée en session.
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

$connexion = Database::getConnexion($dbConfig);

$isMagePlayer = false;
if (!empty($_SESSION['email'])) {
    $currentUser = AccountDAL::selectByEmail($connexion, (string) $_SESSION['email']);
    $isMagePlayer = $currentUser !== false && (int) ($currentUser['estMage'] ?? 0) === 1;
}

// Authentification rapide depuis le formulaire de connexion qui poste vers catalogue.php.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($email === false) {
        $_SESSION['error_email'] = 'Courriel invalide.';
        header('Location: login.php');
        exit;
    }
    $account = AccountDAL::selectByEmail($connexion, $email);

    if ($account === false) {
        $_SESSION['error_email'] = 'Ce compte n\'existe pas.';
        header('Location: login.php');
        exit;
    }

    $_SESSION['email'] = $email;
    $_SESSION['id'] = (int) $account['idJoueur'];

    // Charger le panier sauvegardé depuis la BD
    $cartKey = 'panier_user_' . $_SESSION['id'];
    $_SESSION[$cartKey] = CartDAL::loadCart($connexion, $_SESSION['id']);

    header('Location: index.php');
    exit;
}

// Ajout au panier: stocke/maj l'article dans le panier de l'utilisateur courant.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item_id'])) {
    if (!IS_AUTH) {
        $_SESSION['cart_notice'] = 'Connectez-vous pour ajouter des items au panier.';
        header('Location: ' . Page::Catalogue->url());
        exit;
    }

    $itemId = filter_input(INPUT_POST, 'add_item_id', FILTER_VALIDATE_INT);

    if ($itemId !== false && $itemId !== null) {
        $item = ItemDAL::selectById($connexion, $itemId);

        if ($item !== false) {
            $isSpell = (($item['typeItem'] ?? '') === 'S');
            if ($isSpell && !$isMagePlayer) {
                $_SESSION['cart_notice'] = 'Seuls les joueurs mages peuvent acheter des sorts.';
                header('Location: ' . Page::Catalogue->url());
                exit;
            }

            $cartSessionKey = getCartSessionKey();

            if (!isset($_SESSION[$cartSessionKey]) || !is_array($_SESSION[$cartSessionKey])) {
                $_SESSION[$cartSessionKey] = [];
            }

            $found = false;

            foreach ($_SESSION[$cartSessionKey] as &$cartItem) {
                if ((int) $cartItem['id'] === (int) $item['idItem']) {
                    $cartItem['quantite']++;
                    $_SESSION['cart_notice'] = 'Quantite mise a jour dans votre panier.';
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
                    'quantite' => 1,
                ];

                $_SESSION['cart_notice'] = 'Item ajoute au panier.';
            }

            // Synchroniser avec la BD si l'utilisateur est connecté
            if (!empty($_SESSION['id'])) {
                CartDAL::saveCart($connexion, (int) $_SESSION['id'], $_SESSION[$cartSessionKey]);
            }
        }
    }

    // PRG pattern: évite un double ajout si la page est rechargée.
    header('Location: ' . Page::Catalogue->url());
    exit;
}

$cartNotice = $_SESSION['cart_notice'] ?? '';
// Message flash: on le consomme une seule fois apres redirection.
unset($_SESSION['cart_notice']);

if (!defined('IS_MAGE_PLAYER')) {
    define('IS_MAGE_PLAYER', $isMagePlayer);
}

// identification de la page active
const ACTIVE_PAGE = Page::Catalogue;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css'];

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

            <?php if (!empty($cartNotice)): ?>
                <div id="cart-toast" class="cart-toast" role="status" aria-live="polite">
                    <span><?= htmlspecialchars($cartNotice) ?></span>
                    <button type="button" class="cart-toast-close" aria-label="Fermer">x</button>
                </div>
            <?php endif; ?>

            <!--Bloc ?-->
        <?php include_once TEMPLATE . '/listItems.php'; ?>
            
            <!--Bloc ?-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->

</body>

<script>
// Toast non bloquant: fermeture manuelle (x) ou automatique apres 2.6s.
document.addEventListener('DOMContentLoaded', function () {
    var toast = document.getElementById('cart-toast');
    if (!toast) {
        return;
    }

    var closeBtn = toast.querySelector('.cart-toast-close');
    var hideToast = function () {
        toast.classList.add('cart-toast-hide');
        setTimeout(function () {
            toast.remove();
        }, 250);
    };

    if (closeBtn) {
        closeBtn.addEventListener('click', hideToast);
    }

    setTimeout(hideToast, 2600);
});
</script>
</html>

