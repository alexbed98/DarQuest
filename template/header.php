<header>
    <div class="header">

        <div class="headerLeft">
            <a href="/index.php"><img class="logo" src="/public/img/darquest_logo_01.png" alt="Logo"></a>
            <h1 class="titre"><?= ACTIVE_PAGE->text() ?></h1>
        </div>

        <div class="headerRight">
            <a class='headerButtons' href="<?= Page::Catalogue->url() ?>">Items</a>
            <a class='headerButtons' href="">Admin</a>
            <a class='headerButtons' href="">Inventaire</a>
            <a class='headerButtons' href="">Énigma</a>
            <a class='headerButtons' href="<?= Page::Panier->url() ?>">Panier</a>
            <a href="<?= Page::Connexion->url() ?>"><img class="avatar" src="/public/img/avatar.jpg" alt="Avatar"></a>
        </div>

    </div>
</header>