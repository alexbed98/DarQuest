<?php

// require_once 'core/error-exception.php';
require_once 'src/initialization.php';
require_once 'src/Page.php';
require_once 'core/Validation.php';
require_once 'core/Database.php';
require_once 'src/AccountDAL.php';
require_once 'core/Email.php';
require_once 'core/Upload.php';

// on retourne a l'accueil si lutilisateur est deja logged in
if (!IS_AUTH)
    header('Location: ' . Page::Home->url());

// identification de la page active
const ACTIVE_PAGE = Page::Profil;

$cssAdd = [
    '/public/css/catalogue.css',
    '/public/css/layout.css',
    '/public/css/form.css',
    '/public/css/signup.css',
    '/public/css/profil.css'
];

$email = $_SESSION['email'];

$connexion = Database::getConnexion($dbConfig);
$user = AccountDAL::selectByEmail($connexion, $email);

$username = $user['alias'];
$prenom = $user['prenom'];
$idJoueur = $user['idJoueur'];
$nom = $user['nom'];
$motDePasse = $user['motDePasse'];
$gold = $user['gold'];
$silver = $user['argent'];
$bronze = $user['bronze'];
$estMage = $user['estMage'];
$avatar = $user['avatar'];
$ancienEmail = $email;

// pour les messages d'erreurs
$messages = [];
$globalMessageColor = 'text-danger';

// type de fichiers accepter comme avatar
$allowedTypes = ['image/png', 'image/jpeg', 'image/avif', 'image/webp'];

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
    else {
        if(AccountDAL::selectByEmail($connexion, $email) && $email != $ancienEmail) {
            $messages['email'] = 'Ce courriel existe déjà';
        }
    }

    $password = $_POST['password'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';

    if (!empty($newPassword))
    {
        if (empty($password)) {
            $messages['password'] = 'Vous devez entrer votre mot de passe actuel';
        }
        else {
            if (!Validation::passwordIsValid($newPassword, MATCH_PATTERN, PASSWORD_SIZE)){
                $messages['newPassword'] = 'Le mot de passe ne respecte pas le format requis';
            }
            else{
                if (!password_verify($password, $motDePasse)) {
                    $messages['password'] = 'Le mot de passe est incorrect';
                }
            }
        }
    }

    $avatarFinal = $avatar;

    $newAvatar = Upload::move(
        'image',
        UPLOAD,
        $allowedTypes
    );

    if (!empty($_FILES['image']['name']) && $newAvatar === false) {
        $messages['image'] = is_writable(UPLOAD)
            ? 'Le fichier avatar n\'a pas pu etre televerse. Verifiez le format accepte.'
            : 'Le dossier upload n\'est pas accessible en ecriture sur le serveur.';
    }

    if ($newAvatar !== false && $newAvatar !== null) {
        $avatarFinal = $newAvatar;
    }

    if (count($messages) > 0) {

        $messages['global'] = 'Le formulaire est invalide.';

    } else {
        $finalPassword = $motDePasse;

        if (!empty($newPassword)){
            $finalPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (AccountDAL::updateJoueur($connexion, $idJoueur, $username, $prenom, $nom, $email, $finalPassword, $avatarFinal)){
            $_SESSION['email'] = $email;
            $_SESSION['avatar'] = $avatarFinal;

            $user = AccountDAL::selectByEmail($connexion, $email);

            $avatar = $user['avatar'];
            $username = $user['alias'];
            $prenom = $user['prenom'];
            $nom = $user['nom'];
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

            <div class="fs-4 text-center my-2">Vos informations personnelles</div>

            <div class="col-md-8 mx-auto">

                <form class="form-style" method="post" enctype="multipart/form-data">

                    <div style="display: flex; flex-direction: row; gap: 20px">
                        <div style="flex: 1">
                            <div style="display: flex; flex-direction: row;">
                                <div class="mb-3" style="margin-right: 8px">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <input name="prenom" value="<?= $prenom ?>" class="form-control" id="prenom" autofocus>
                                    <div id="prenomHelp" class="form-text text-danger"><?= $messages['prenom'] ?? '' ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input name="nom" value="<?= $nom ?>" class="form-control" id="nom" autofocus>
                                    <div id="nomHelp" class="form-text text-danger"><?= $messages['nom'] ?? '' ?></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input name="username" class="form-control" 
                                       id="username" value="<?= $username ?>" autofocus>
                                <div id="usernameHelp" class="form-text text-danger"><?= $messages['username'] ?? '' ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Courriel</label>
                                <input name="email" value="<?= $email ?>" type="email" class="form-control" id="email"
                                    aria-describedby="emailHelp">
                                <div id="emailHelp" class="form-text text-danger"><?= $messages['email'] ?? '' ?></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input name="password" type="password" class="form-control" id="empasswordail2"
                                    aria-describedby="passwordHelp">
                                <div id="passwordHelp" class="form-text text-danger">
                                    <?= $messages['password'] ?? '' ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div style="display: flex;">
                                    <label for="newPassword" class="form-label">Nouveau mot de passe</label>
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
                                <input name="newPassword" type="password" class="form-control" id="empasswordail"
                                    aria-describedby="newPasswordHelp">
                                <div id="newPasswordHelp" class="form-text text-danger"><?= $messages['newPassword'] ?? '' ?>
                                </div>
                            </div>

                        </div>
                        <div style="flex: 1">
                            <div class="mb-3">
                                <label for="image" class="form-label"></span>Avatar</label>

                                <div style="margin-bottom: 10px; display: flex;">
                                    <img src="<?= AVATAR . $avatar ?>" alt="Avatar actuel" style="max-width: 60px; border-radius: 8px; margin: auto">
                                </div>

                                <input name="image" type="file" class="form-control" 
                                        id="image" aria-describedby="imageHelp" accept=".jpg,.png,.webp,.avif">

                                <div id="imageHelp" class="form-text text-danger"><?= $messages['image'] ?? '' ?></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre de pièces</label>
                                <span class="form-control profil-gold">
                                    <?= $gold ?>🥇,&nbsp;<?= $silver ?>🥈,&nbsp;<?= $bronze ?>🥉
                                </span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Statistiques</label>
                                <span class="form-control profil-stats">
                                    <Text class="stats-text">Statut: <?= $estMage == 1 ? "Mage" : "Apprenti" ?></Text>
                                    <Text class="stats-text">Questions sur la magie restante(s): 1 (hardcoded)</Text>
                                    <Text class="stats-text">Questions réussites: 145 (hardcoded)</Text> 
                                    <Text class="stats-text">Taux de réussite: 78% (hardcoded)</Text>
                                </span>
                            </div>

                        </div>
                    </div>

                    <button type="submit" name="submit" class="btn btn-primary submit" style="width: auto;">Enregistrer
                        les modifications</button>


                </form>

                <div id="global-message" class="my-3 <?= $globalMessageColor ?>"><?= $messages['global'] ?? '' ?></div>

            </div>

        </main>

        <!--Bloc pied de page-Footer block-->
        <?php include_once TEMPLATE . '/footer.php'; ?>
        <!--Bloc pied de page-Footer block-->

    </div>
    <!--Contenant principal-->

</body>

</html>