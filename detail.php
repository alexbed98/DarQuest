<?php

// les require et les include
// a decommenter quand on les utilisent

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';

// identification de la page active
// const ACTIVE_PAGE = Page::Home;

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
            <h1 class="py-3 mt-3">DarQuest</h1>

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

