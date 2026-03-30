<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($email !== false) {
        $_SESSION['email'] = $email;

        header('Location: catalogue.php');
        exit;

    } else {
        $_SESSION['email'] = $_POST['email'] ?? '';
        $_SESSION['error_email'] = 'Courriel invalide ou manquant.';

        header('Location: login.php');
        exit;
    }
}

if(isset($_SESSION['email'])) {
    echo "Bienvenue, " . $_SESSION['email'] . "!";
}
else {
    echo "Tu n'es pas connecté.";
}

// les require et les include
// a decommenter quand on les utilisent

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';

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
</html>

