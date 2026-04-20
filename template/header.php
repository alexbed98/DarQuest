<header>
    <div class="header">

        <div class="headerLeft">
            <a href="/index.php"><img class="logo" src="/public/img/darquest_logo_01.png" alt="Logo"></a>
            <h1 class="titre"><?= ACTIVE_PAGE->text() ?></h1>
        </div>

        <div class="headerRight">
            <?php if (IS_AUTH): ?>
                <a class='headerButtons' href="<?= Page::Catalogue->url() ?>">Items</a>
                <?php if (IS_ADMIN): ?>
                    <a class='headerButtons' href="">Admin</a>
                <?php endif; ?>
                <a class='headerButtons' href="<?= Page::Inventaire->url() ?>">Inventaire</a>
                <a class='headerButtons' href="">Énigma</a>
                <a class='headerButtons' href="<?= Page::Panier->url() ?>">Panier</a>
            <?php endif; ?>
            <!-- si on est connecté, en cliquant sur l'avatar on arrive a la page profil -->
            <?php if (IS_AUTH): ?>
                <a href="<?= Page::Profil->url() ?>"><img class="avatar" src="<?= AVATAR . $_SESSION['avatar'] ?>" alt="Avatar"></a>
            <!-- si on n'est pas connecté, en cliquant sur l'avatar on arrive a la page de connexion -->
            <?php else: ?>
                <a href="<?= Page::Connexion->url() ?>"><img class="avatar" src="<?= AVATAR . 'avatar_default.jpg' ?>" alt="Avatar"></a>
            <?php endif; ?>
            <?php if (IS_AUTH): ?>
                <a href="logout.php" class="logout-btn" title="Se déconnecter">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>