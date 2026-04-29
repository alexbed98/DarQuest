<?php

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'core/Email.php';

// on retourne a l'accueil si lutilisateur est deja logged in
if (IS_AUTH)
    header('Location: ' . Page::Home->url());

// identification de la page active
const ACTIVE_PAGE = Page::CreationCompte;

$cssAdd = [
    '/public/css/catalogue.css',
    '/public/css/layout.css',
    '/public/css/form.css',
    '/public/css/signup.css'
];

$email = '';
$username = '';
$prenom = '';
$nom = '';
$messages = [];

$globalMessageColor = 'text-danger';

if (IS_POST) {

    $prenom = $_POST['prenom'] ?? '';

    if (empty($prenom)) {
        $messages['prenom'] = 'Le prenom est obligatoire.';
    }

    $nom = $_POST['nom'] ?? '';

    if (empty($nom)) {
        $messages['nom'] = 'Le nom est obligatoire.';
    }

    $username = $_POST['username'] ?? '';

    if (empty($username)) {
        $messages['username'] = 'Le username est obligatoire.';
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!is_string($email)) {

        $messages['email'] = 'Le courriel est obligatoire.';

        $email = $_POST['email'] ?? '';

    }

    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($password) || $password !== $password2 || !Validation::passwordIsValid($password, MATCH_PATTERN, PASSWORD_SIZE)) {

        $messages['password'] = 'Le mot de passe est obligatoire et les deux champs Mots de passe doivent être identiques.';

    }

    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {

        $connexion = Database::getConnexion($dbConfig);
        $user = AccountDAL::selectByEmail($connexion, $email);

        if ($user === false) {

            $motDePasse = password_hash($password, PASSWORD_DEFAULT);
            $courriel = $email;
            $guid = generateGUID();

            if (AccountDAL::insertOne($connexion, $username, $prenom, $nom, $courriel, $motDePasse, $guid)) {

                $subject = 'Création de compte DarQuest.';

                $message = <<<HTML
                <h1>Merci d'avoir créé un compte.</h1>
                <h2>Bienvenue sur DarQuest!</h2>
                <p><a style="text-decoration: underline; color: blue;" href="http://darquest.ca/login.php?guid=">Validez votre courriel pour accéder a votre compte</a></p>
                HTML;

                Email::readConfig(SRC . '/gmail.ini');

                if (Email::send($email, $subject, $message)) {

                    $_SESSION['new-account'] = true;
                    header('Location: ' . Page::Connexion->url());

                }

            }

        } else {

            $messages['global'] = 'Ce courriel n\'est pas disponible pour créer un compte. ';

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

            <div class="fs-4 text-center my-5">Entrez vos informations.</div>

            <!--Formulaire authenfification-Authentication form-->
            <div class="col-md-4 mx-auto">

                <form class="form-style" method="post" novalidate>

                    <div style="display: flex; flex-direction: row;">
                        <div class="mb-3" style="margin-right: 8px">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input name="prenom" class="form-control" id="prenom" autofocus>
                            <div id="prenomHelp" class="form-text text-danger"><?= $messages['prenom'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input name="nom" class="form-control" id="nom" autofocus>
                            <div id="nomHelp" class="form-text text-danger"><?= $messages['nom'] ?? '' ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input name="username" class="form-control" id="username" autofocus>
                        <div id="usernameHelp" class="form-text text-danger"><?= $messages['username'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Courriel</label>
                        <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp">
                        <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
                    </div>

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

                    <button type="submit" name="submit" class="btn btn-primary submit">Envoyer</button>

                </form>

                <div id="global-message" class="my-3 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></div>

                <div style="text-align: center" class="py-3"><a href="<?= Page::Connexion->url() ?>">Connexion à un
                        compte</a></div>

            </div>

        </main>

        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->

    </div>
    <!--Contenant principal-->

</body>

</html>