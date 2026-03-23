<?php

// les require et les include
// a decommenter quand on les utilisent

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';

// on retourne a l'accueil si lutilisateur est deja logged in
if (IS_AUTH)
    header('Location: ' . Page::Home->url());

// identification de la page active
const ACTIVE_PAGE = Page::CreationCompte;

$cssAdd = [
    '/public/css/catalogue.css',
    '/public/css/layout.css'
];

$email = '';
$username = '';
$prenom = '';
$nom = '';
$messages = [];

$globalMessageColor = 'text-danger';

if (IS_POST) {

    if (!empty($prenom)) {
        $prenom = $_POST['prenom'] ?? '';
    } else {
        $messages['prenom'] = 'Le prenom est obligatoire.';
    }

    if (!empty($nom)) {
        $nom = $_POST['nom'] ?? '';
    } else {
        $messages['nom'] = 'Le nom est obligatoire.';
    }

    if (!empty($username)) {
        $username = $_POST['username'] ?? '';
    } else {
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

            $hash = password_hash($password, PASSWORD_DEFAULT);

            if (AccountDAL::insertOne($connexion, $email, $hash)) {

                $subject = 'Merci d\'avoir créé un compte.';

                $message = <<<HTML
                <h1>Merci d'avoir créé un compte.</h1>
                <a href="http://darquest.ca:8080">Validez votre courriel</a>
                HTML;

                Email::readConfig(SRC . '/gmail.ini');

                if (true || Email::send($email, $subject, $message)) {

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
            <h1 class="py-3 mt-3">DarQuest</h1>

            <div class="fs-4 text-center my-5">Entrez vos informations de création de compte.</div>

            <!--Formulaire authenfification-Authentication form-->
            <div class="col-md-4 mx-auto">

                <form method="post" novalidate>

                    <div style="display: flex; flex-direction: row;">
                        <div class="mb-3" style="margin-right: 8px">
                            <label for="prenom" class="form-label"><span class="text-danger">* </span>Prénom</label>
                            <input name="prenom" class="form-control" id="prenom" autofocus>
                            <div id="prenomHelp" class="form-text text-danger"><?= $messages['prenom'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label"><span class="text-danger">* </span>Nom</label>
                            <input name="nom" class="form-control" id="nom" autofocus>
                            <div id="nomHelp" class="form-text text-danger"><?= $messages['nom'] ?? '' ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label"><span class="text-danger">* </span>Username</label>
                        <input name="username" class="form-control" id="username" autofocus>
                        <div id="usernameHelp" class="form-text text-danger"><?= $messages['username'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><span class="text-danger">* </span>Courriel</label>
                        <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp">
                        <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><span class="text-danger">* </span>Mot de passe</label>
                        <input name="password" type="password" class="form-control" id="empasswordail"
                            aria-describedby="passwordHelp">
                        <div id="passwordHelp" class="form-text text-danger"><?= $messages['password'] ?? '' ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password2" class="form-label"><span class="text-danger">* </span>Confirmez le mot de
                            passe</label>
                        <input name="password2" type="password" class="form-control" id="empasswordail2"
                            aria-describedby="password2Help">
                        <div id="passwordHelp2" class="form-text text-danger"><?= $messages['password2'] ?? '' ?></div>
                    </div>

                    <div>
                        Le mot de passe doit contenir :
                        <ul>
                            <li>au moins une lettre minuscule</li>
                            <li>au moins une lettre majuscule</li>
                            <li>au moins un chiffre</li>
                            <li>au moins un symbole @#-_$%^&+=§!?</li>
                        </ul>

                    </div>

                    <div class="py-3 text-danger">* Champs requis</div>

                    <button type="submit" class="btn btn-primary">Envoyer</button>

                </form>

                <div id="global-message" class="my-3 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></dib>

                    <div class="py-3"><a href="<?= Page::Connexion->url() ?>">Connexion à un compte</a></div>

                </div>

        </main>

        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->

    </div>
    <!--Contenant principal-->

</body>

</html>