<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'core/Email.php';

if (!isset($_SESSION['reset_autorise']) || 
    $_SESSION['reset_autorise'] !== true ||
    !isset($_SESSION['reset_id_joueur']))
{
    header('Location: login.php');
    exit;
}

$idJoueur = $_SESSION['reset_id_joueur'] ?? '';

$connexion = Database::getConnexion($dbConfig);



// identification de la page active
const ACTIVE_PAGE = Page::ResetMDP;

$cssAdd = ['/public/css/catalogue.css',
           '/public/css/layout.css',
           '/public/css/form.css',
           '/public/css/signup.css'];

$messages = [];

$globalMessageColor = 'text-danger';

if (IS_POST) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($password) || $password !== $password2 || !Validation::passwordIsValid($password, MATCH_PATTERN, PASSWORD_SIZE)) {

        $messages['password'] = 'Le mot de passe est obligatoire et les deux champs Mots de passe doivent être identiques.';

    }

    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {

        $connexion = Database::getConnexion($dbConfig);

        $newPassword = password_hash($password, PASSWORD_DEFAULT);

        if (AccountDAL::updatePassword($connexion, $idJoueur, $newPassword)){

            AccountDAL::removeResetGuid($connexion, $idJoueur);
            
            unset($_SESSION['reset_id_joueur']);
            unset($_SESSION['reset_autorise']);

            header('Location: ' . Page::Connexion->url());
            exit;
        }

    }
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
            <div class="fs-4 text-center my-5">Rénitialisation du mot de passe.</div>
            <div class="fs-5 text-center my-5">Entrez votre nouveau mot de passe.</div>

            <!--Formulaire authenfification-Authentication form-->
            <div class="col-md-4 mx-auto">

                <form class="form-style" method="post" novalidate>

                    <div class="mb-3">
                        <div style="display: flex;">
                            <label for="password" class="form-label">Mot de passe</label>
                        <div class="my-tooltip">
                        <span class="my-info-icon">ⓘ</span>

                        <div class="my-tooltip-text">
                            Le mot de passe doit contenir :
                            <ul>
                                <li>au moins une lettre minuscule</li>
                                <li>au moins une lettre majuscule</li>
                                <li>au moins un chiffre</li>
                                <li>au moins un symbole @#-_$%^&+=§!?</li>
                            </ul>
                        </div>
                        </div>
                    </div>
                        <input name="password" type="password" class="form-control" id="empasswordail"
                            aria-describedby="passwordHelp">
                        <div id="passwordHelp" class="form-text text-danger"><?= $messages['password'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password2" class="form-label">Confirmation du mot de
                            passe</label>
                        <input name="password2" type="password" class="form-control" id="empasswordail2"
                            aria-describedby="password2Help">
                        <div id="passwordHelp2" class="form-text text-danger"><?= $messages['password2'] ?? '' ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary submit">Envoyer</button>

                </form>

                <div id="global-message" class="my-5 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></div>

                <div style="text-align: center" class="py-3"><a href="<?= Page::Connexion->url() ?>">Revenir à la page
                        de connexion</a>
                </div>

            </div>
            <!--Bloc ?-->

        </main>
        
        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->
        
    </div>
    <!--Contenant principal-->
   
</body>
</html>

