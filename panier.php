<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'src/CartDAL.php';
require_once 'src/InventaireDAL.php';
require_once 'core/Email.php';

$connexion = Database::getConnexion($dbConfig);

// Traiter la commande
if (IS_POST && isset($_POST['passer_commande']) && !empty($_SESSION['id'])) {
    $idJoueur = (int) $_SESSION['id'];
    $cartKey  = 'panier_user_' . $idJoueur;
    $panier   = $_SESSION[$cartKey] ?? [];

    if (!empty($panier)) {
        $ok = InventaireDAL::commander($connexion, $idJoueur, $panier);

        if ($ok) {
            // Vider le panier en session et en BD
            $_SESSION[$cartKey] = [];
            CartDAL::saveCart($connexion, $idJoueur, []);
            $_SESSION['commande_notice'] = 'success';
        } else {
            $_SESSION['commande_notice'] = 'error';
        }
    }

    header('Location: ' . Page::Panier->url());
    exit;
}

// identification de la page active
const ACTIVE_PAGE = Page::Panier;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/panier.css'];

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
            <?php include_once TEMPLATE . '/paniers.php'; ?>
            <!--Bloc ?-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->
   
</body>
</html>

