<header>
    <div class="header">
        <a href="/index.php"><img class="logo" src="/public/img/darquest_logo_01.png" alt="Logo"></a>

        <h1 class="titre"><?= ACTIVE_PAGE->text() ?></h1>
        <div class="pages">
            <a href="<?= Page::Catalogue->url() ?>">Items</a>
            <a href="">Admin</a>
            <a href="">Inventaire</a>
            <a href="">Énigma</a>
            <a href="<?= Page::Panier->url() ?>">Panier</a>
        </div>
        <div>
            <a href="<?= Page::Connexion->url() ?>"><img class="avatar" src="/public/img/avatar.jpg" alt="Avatar"></a>
        </div>
</div>
</header>
