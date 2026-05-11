<?php

session_start();

require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'core/Email.php';

$connexion = Database::getConnexion($dbConfig);

// identification de la page active
const ACTIVE_PAGE = Page::Email;

$cssAdd = [
    '/public/css/catalogue.css',
    '/public/css/layout.css',
    '/public/css/form.css'
];

$email = '';
$messages = [];

$globalMessageColor = 'text-danger';

if (IS_POST) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    $email = $_POST['email'] ?? '';

    if (empty($email)) {
        $messages['email'] = 'Le courriel est obligatoire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages['email'] = 'Le courriel n\'est pas valide.';
    }

    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {

        if (AccountDAL::courrielExistant($connexion, $email)) {

            $reset_guid = generateGUID();

            AccountDAL::addResetGuid($connexion, $email, $reset_guid);

            $subject = 'Rénitialisation du mot de passe.';

            $message = <<<HTML
                <h1>Rénitialisation du mot de passe</h1>
                <p><a style="text-decoration: underline; color: blue;" href="http://158.69.48.57/~darquest12/DarQuestMain-main/validateReset.php?reset_guid=$reset_guid">
                    Cliquer ici pour rénitialiser votre mot de passe</a>
                </p>
                HTML;

            Email::readConfig(SRC . '/gmail.ini');

            if (Email::send($email, $subject, $message)) {

                header('Location: ' . Page::Connexion->url());
                exit;
            }

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
            <div class="fs-5 text-center my-5">Entrez votre courriel pour recevoir un lien de rénitialisation.</div>

            <!--Formulaire authenfification-Authentication form-->
            <div class="col-md-4 mx-auto">

                <form class="form-style" method="post" novalidate>

                    <div class="mb-3">
                        <label for="email" class="form-label">Courriel</label>
                        <input name="email" type="email" class="form-control" id="email" aria-describedby="emailHelp"
                            value="<?= htmlspecialchars($email) ?>" autofocus>
                        <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
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