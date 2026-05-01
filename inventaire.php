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

// Convertir les monnaies (PRG)
if (IS_POST && isset($_POST['convertir'])) {
    $type = $_POST['convertir'];
    $stmt = $connexion->prepare("SELECT bronze, argent, gold FROM Joueurs WHERE idJoueur = :id");
    $stmt->bindValue(':id', $idJoueur, PDO::PARAM_INT);
    $stmt->execute();
    $monnaie = $stmt->fetch();

    if ($monnaie) {
        if ($type === 'bronze_to_argent' && (int)$monnaie['bronze'] >= 10) {
            $fois = (int)((int)$monnaie['bronze'] / 10);
            $upd = $connexion->prepare(
                "UPDATE Joueurs SET bronze = bronze - :retrait, argent = argent + :gain WHERE idJoueur = :id"
            );
            $upd->bindValue(':retrait', $fois * 10, PDO::PARAM_INT);
            $upd->bindValue(':gain', $fois, PDO::PARAM_INT);
            $upd->bindValue(':id', $idJoueur, PDO::PARAM_INT);
            $upd->execute();
        } elseif ($type === 'argent_to_gold' && (int)$monnaie['argent'] >= 10) {
            $fois = (int)((int)$monnaie['argent'] / 10);
            $upd = $connexion->prepare(
                "UPDATE Joueurs SET argent = argent - :retrait, gold = gold + :gain WHERE idJoueur = :id"
            );
            $upd->bindValue(':retrait', $fois * 10, PDO::PARAM_INT);
            $upd->bindValue(':gain', $fois, PDO::PARAM_INT);
            $upd->bindValue(':id', $idJoueur, PDO::PARAM_INT);
            $upd->execute();
        }
    }

    header('Location: ' . Page::Inventaire->url());
    exit;
}

// Vendre un item (PRG)
if (IS_POST && isset($_POST['vendre_id'])) {
    $idItem  = filter_input(INPUT_POST, 'vendre_id', FILTER_VALIDATE_INT);
    $quantite = filter_input(INPUT_POST, 'vendre_qty', FILTER_VALIDATE_INT);

    if ($idItem && $quantite && $quantite > 0) {
        $gainVente = InventaireDAL::vendre($connexion, $idJoueur, $idItem, $quantite);
        if ($gainVente !== false) {
            $_SESSION['vente_message'] = 'Vente reussie : +' . number_format((int) $gainVente) . ' or';
        }
    }

    header('Location: ' . Page::Inventaire->url());
    exit;
}

// Consommer une potion (PRG)
if (IS_POST && isset($_POST['consommer_potion_id'])) {
    $idItem = filter_input(INPUT_POST, 'consommer_potion_id', FILTER_VALIDATE_INT);

    if ($idItem && InventaireDAL::consommerPotion($connexion, $idJoueur, $idItem)) {
        $_SESSION['potion_message'] = 'Potion consommée !';
    }

    header('Location: ' . Page::Inventaire->url());
    exit;
}

// Lancer un sort (PRG)
if (IS_POST && isset($_POST['lancer_sort_id'])) {
    $idItem = filter_input(INPUT_POST, 'lancer_sort_id', FILTER_VALIDATE_INT);

    if ($idItem && InventaireDAL::lancerSort($connexion, $idJoueur, $idItem)) {
        $_SESSION['sort_message'] = 'Sort lancé !';
    }

    header('Location: ' . Page::Inventaire->url());
    exit;
}

// Lire les paramètres de tri et filtre depuis GET
$triActif     = in_array($_GET['tri'] ?? '', ['prix_asc', 'prix_desc', 'type']) ? $_GET['tri'] : 'nom';
$filtresActifs = array_intersect((array) ($_GET['filtre'] ?? []), ['A', 'R', 'P', 'S']);

// Charger l'inventaire du joueur
$inventaire = InventaireDAL::getInventaire($connexion, $idJoueur, $triActif, array_values($filtresActifs));

// Charger les monnaies du joueur
$joueur = $connexion->prepare("SELECT gold, argent, bronze FROM Joueurs WHERE idJoueur = :id");
$joueur->bindValue(':id', $idJoueur, PDO::PARAM_INT);
$joueur->execute();
$monnaieRow = $joueur->fetch();
$gold   = (int) ($monnaieRow['gold']   ?? 0);
$argent = (int) ($monnaieRow['argent'] ?? 0);
$bronze = (int) ($monnaieRow['bronze'] ?? 0);

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
