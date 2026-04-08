<?php
require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'src/InventaireDAL.php';

// Rediriger si non connecté
if (!IS_AUTH) {
    header('Location: ' . Page::Connexion->url());
    exit;
}

$connexion = Database::getConnexion($dbConfig);
$idJoueur  = (int) $_SESSION['id'];

// Vendre un item (PRG)
if (IS_POST && isset($_POST['vendre_id'])) {
    $idItem  = filter_input(INPUT_POST, 'vendre_id', FILTER_VALIDATE_INT);
    $quantite = filter_input(INPUT_POST, 'vendre_qty', FILTER_VALIDATE_INT);

    if ($idItem && $quantite && $quantite > 0) {
        InventaireDAL::vendre($connexion, $idJoueur, $idItem, $quantite);
    }

    header('Location: ' . Page::Inventaire->url());
    exit;
}

// Charger l'inventaire du joueur
$inventaire = InventaireDAL::getInventaire($connexion, $idJoueur);

// Charger les pièces d'or du joueur
$joueur = $connexion->prepare("SELECT gold FROM joueurs WHERE idJoueur = :id");
$joueur->bindValue(':id', $idJoueur, PDO::PARAM_INT);
$joueur->execute();
$gold = (int) ($joueur->fetch()['gold'] ?? 0);

const ACTIVE_PAGE = Page::Inventaire;

$cssAdd = [
    '/public/css/catalogue.css',
    '/public/css/layout.css',
    '/public/css/inventaire.css',
];
?>
<!DOCTYPE html>
<html lang="fr">

<?php include_once TEMPLATE . '/head.php'; ?>

<body>
    <div class="container">

        <?php include_once TEMPLATE . '/header.php'; ?>

        <main>
            <?php include_once TEMPLATE . '/inventaireItems.php'; ?>
        </main>

        <?php include_once TEMPLATE . '/footer.php'; ?>

    </div>
</body>
</html>
