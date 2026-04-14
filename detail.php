<?php


// les require et les include
// a decommenter quand on les utilisent

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';


// identification de la page active
 const ACTIVE_PAGE = Page::Details;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/details.css',
           '/public/css/panier.css',
           ];

// Ajout au panier: stocke/maj l'article dans le panier de l'utilisateur courant.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item_id'])) {
    $itemId = filter_input(INPUT_POST, 'add_item_id', FILTER_VALIDATE_INT);

    if ($itemId !== false && $itemId !== null) {
        $item = ItemDAL::selectById($connexion, $itemId);

        if ($item !== false) {
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
                    : '/public/img/' . ltrim($photo, '/');

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
    header('Location: ' . Page::Details->url());
    exit;
}



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

