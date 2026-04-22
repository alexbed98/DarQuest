<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'core/Upload.php';
require_once 'src/AccountDAL.php';
require_once 'src/ItemDAL.php';
require_once 'src/EnigmeDAL.php';
require_once 'core/Email.php';

// Securite: seul un admin peut acceder a cette page
if (!IS_ADMIN) {
    header('Location: ' . Page::Home->url());
    exit;
}

$connexion = Database::getConnexion($dbConfig);

$itemSuccess = isset($_GET['success']) && $_GET['success'] === 'item';
$enigmeSuccess = isset($_GET['success']) && $_GET['success'] === 'enigme';
$itemError = null;
$enigmeError = null;

// ─── Creation d'un item ──────────────────────────────────────────────────────
if (IS_POST && ($_POST['action'] ?? '') === 'create_item') {

    $nom         = trim($_POST['nom'] ?? '');
    $quantite    = (int) ($_POST['quantiteStock'] ?? 0);
    $prix        = (int) ($_POST['prix'] ?? 0);
    $estDispo    = isset($_POST['estDisponible']) ? 1 : 0;
    $typeItem    = strtoupper(trim($_POST['typeItem'] ?? ''));

    // Upload photo
    $photo = 'default.png';
    if (!empty($_FILES['photoFile']['name'])) {
        $dest = ROOT . '/public/img/items';
        if (Upload::move('photoFile', $dest, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], 5 * 1024 * 1024)) {
            $photo = basename($_FILES['photoFile']['name']);
        }
    }

    $ok = false;
    switch ($typeItem) {
        case 'A':
            $ok = ItemDAL::insertArme(
                $connexion, $nom, $quantite, $prix, $photo, $estDispo,
                trim($_POST['description'] ?? ''),
                trim($_POST['efficacite'] ?? ''),
                trim($_POST['genreArme'] ?? '')
            );
            break;
        case 'R':
            $ok = ItemDAL::insertArmure(
                $connexion, $nom, $quantite, $prix, $photo, $estDispo,
                trim($_POST['matiere'] ?? ''),
                trim($_POST['taille'] ?? '')
            );
            break;
        case 'P':
            $ok = ItemDAL::insertPotion(
                $connexion, $nom, $quantite, $prix, $photo, $estDispo,
                trim($_POST['effet'] ?? ''),
                (int) ($_POST['duree'] ?? 0)
            );
            break;
        case 'S':
            $ok = ItemDAL::insertSort(
                $connexion, $nom, $quantite, $prix, $photo, $estDispo,
                isset($_POST['instantane']) ? 1 : 0,
                (int) ($_POST['rarete'] ?? 0),
                trim($_POST['typeSort'] ?? '')
            );
            break;
        default:
            $itemError = "Type d'item invalide. Utilisez A, R, P ou S.";
    }

    if ($ok) {
        header('Location: ' . Page::Admin->url() . '?success=item');
        exit;
    } elseif ($itemError === null) {
        $itemError = "Erreur lors de la creation de l'item. Verifiez les champs.";
    }
}

// ─── Creation d'une enigme ───────────────────────────────────────────────────
if (IS_POST && ($_POST['action'] ?? '') === 'create_enigme') {

    $enonce      = trim($_POST['enonce'] ?? '');
    $idCategorie = trim($_POST['idCategorie'] ?? '') ?: null;
    $difficulte  = trim($_POST['difficulte'] ?? '');
    $estPigee    = (int) ($_POST['estPigee'] ?? 0);
    $bonneRep    = (int) ($_POST['bonneReponse'] ?? 0);
    $reponses    = $_POST['reponses'] ?? [];

    $newId = EnigneDAL::insertEnigme($connexion, $enonce, $idCategorie, $difficulte, $estPigee);

    if ($newId !== false) {
        foreach ($reponses as $i => $texte) {
            $texte = trim($texte);
            if ($texte !== '') {
                EnigneDAL::insertReponse($connexion, $texte, ($i === $bonneRep ? 1 : 0), $newId);
            }
        }
        header('Location: ' . Page::Admin->url() . '?success=enigme');
        exit;
    } else {
        $enigmeError = "Erreur lors de la creation de l'enigme.";
    }
}

// identification de la page active
const ACTIVE_PAGE = Page::Admin;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/admin.css',
           '/public/css/admin-creation.css'];

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
                <?php include_once TEMPLATE . '/admin.php'; ?>
            <!--Bloc ?-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->
   
</body>
</html>

