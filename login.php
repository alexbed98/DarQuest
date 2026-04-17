<?php

// les require et les include
// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'core/Email.php';

if (IS_AUTH) header('Location: '. Page::Home->url());

// identification de la page active
const ACTIVE_PAGE = Page::Connexion;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/form.css'];

$email = '';
$password = '';
$messages = [];

$globalMessageColor = 'text-danger';

// Valider le formulaire lorsqu'il est soumit
if (IS_POST) {

    // recuperation du email
    // si email inexsitant => null
    // si email est existant mais invalide => false
    // si email est existant et valide => email (string)
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    $email = $_POST['email'] ?? '';
    
    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $messages['email'] = 'Le courriel est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages['email'] = 'Le courriel n\'est pas valide.';
    }

    // recuperation du password
    $password = $_POST['password'] ?? '';

    // si vide
    if (empty($password)) {

        $messages['password'] = 'Le mot de passe est obligatoire.';

    }
    
    // si au moins 1 message d'erreur
    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {

        $connexion = Database::getConnexion($dbConfig);
        $user = AccountDAL::selectByEmail($connexion, $email);

        if($user !== false && password_verify($password, $user['motDePasse'])) {
            
            // regeneration de la session id pour eviter certaines erreurs
            session_regenerate_id();

            // Ajout en session de email, id et role
            $_SESSION['email'] = $email;
            $_SESSION['id'] = $user['idJoueur'];
            $_SESSION['role'] = $user['estAdmin'];
            $_SESSION['avatar'] = $user['avatar'];

            // Redirige à l'accueil
            header('Location:' . Page::Home->url());

        } else {

            $messages['global'] = 'Les informations d\'authentification sont invalides';

        }

    }
}

$showNewAccountMessage = false;

if (!empty($_SESSION['new-account'])) {

    $showNewAccountMessage = true;
    unset($_SESSION['new-account']);

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

            <?php if ($showNewAccountMessage): ?>
            <div class="py=3 text-success text-center fs-4">
                <p>Merci d'avoir créé un compte DarQuest</p>
                <p>Vous devez valider votre courriel pour vous connecter.</p>
            </div>
            <?php endif; ?>

            <div class="fs-4 text-center my-5">Connectez-vous</div>

            <!--Formulaire authenfification-Authentication form-->
            <div class="col-md-4 mx-auto">                         

                <form class="form-style" method="post" novalidate>

                    <div class="mb-3">
                        <label for="email" class="form-label">Courriel</label>
                        <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp" value="<?= htmlspecialchars($email) ?>" autofocus>
                        <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input name="password" type="password" class="form-control" id="password" aria-describedby="passwordHelp">
                        <div id="passwordHelp" class="form-text text-danger"><?= $messages['password'] ?? '' ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary submit">Envoyer</button>

                </form>

                <div id="global-message" class="my-5 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></div>

                <div style="text-align: center" class="py-3"><a href="<?= Page::CreationCompte->url() ?>">Je n'ai pas de compte</a></div>
            </div>
            <!--Formulaire authenfification-Authentication form-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->
   
</body>
</html>

