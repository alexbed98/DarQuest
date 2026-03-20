<?php

// les require et les include

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';

// on retourne a l'accueil si lutilisateur est deja logged in
if (IS_AUTH) header('Location: '. Page::Home->url());

// identification de la page active
const ACTIVE_PAGE = Page::Connexion;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/form.css'];

$email = '';
$messages = [];

$globalMessageColor = 'text-danger';

if (IS_POST) {

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ( !is_string($email) ) {

        $messages['email'] = 'Le courriel est obligatoire.';

        $email = $_POST['email'] ?? '';

    }    

    $password = $_POST['password'] ?? '';

    if (empty($password)) {

        $messages['password'] = 'Le mot de passe est obligatoire.';

    }
    
    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {

        $connexion = Database::getConnexion($dbConfig);
        $user = AccountDAL::selectByEmail($connexion, $email);

        if($user !== false && password_verify($password, $user['password'])) {
            
            session_regenerate_id();

            $_SESSION['id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            header('Location:' . Page::Home->url());

        } else {

            $messages['global'] = 'Informations d\'authentification invalides.';

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

                <form method="post" novalidate>

                    <div class="mb-3">
                        <label for="email" class="form-label"><span class="text-danger">* </span>Courriel</label>
                        <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp" value="<?= htmlspecialchars($email) ?>" autofocus>
                        <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><span class="text-danger">* </span>Mot de passe</label>
                        <input name="password" type="password" class="form-control" id="empasswordail" aria-describedby="passwordHelp">
                        <div id="passwordHelp" class="form-text text-danger"><?= $messages['password'] ?? '' ?></div>
                    </div>
                    
                    <div class="py-3 text-danger">* Champs requis</div>

                    <button type="submit" class="btn btn-primary">Envoyer</button>

                </form>

                <div id="global-message" class="my-5 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></dib>

                <div class="py-3"><a href="<?= Page::CreationCompte->url() ?>">Je n'ai pas de compte</a></div>
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

