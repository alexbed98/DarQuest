<header>
    <div class="header">

        <div class="headerLeft">
            <a href="<?= URL_ROOT ?>index.php"><img class="logo" src="<?= IMG ?>/darquest_logo_01.png" alt="Logo"></a>
            <h1 class="titre"><?= ACTIVE_PAGE->text() ?></h1>
        </div>

        <div class="headerRight">
            <?php if (IS_AUTH): ?>
                <?php
                    $headerCon = $connexion ?? Database::getConnexion($dbConfig);
                    $goldStmt = $headerCon->prepare("SELECT gold, alias FROM Joueurs WHERE idJoueur = :id");
                    $goldStmt->bindValue(':id', (int) $_SESSION['id'], PDO::PARAM_INT);
                    $goldStmt->execute();
                    $headerRow = $goldStmt->fetch();
                    $headerGold = (int) ($headerRow['gold'] ?? 0);
                    $headerAlias = htmlspecialchars($headerRow['alias'] ?? 'Joueur');
                ?>
                <span class="header-gold">Nombre de pièces: <?= number_format($headerGold) ?>&nbsp;🥇</span>
                <a class='headerButtons' href="<?= Page::Catalogue->url() ?>">Items</a>
                <?php if (IS_ADMIN): ?>
                    <a class='headerButtons' href="<?= Page::Admin->url() ?>">Admin</a>
                <?php endif; ?>
                <a class='headerButtons' href="<?= Page::Inventaire->url() ?>">Inventaire</a>
                <?php
                    $headerCartKey = !empty($_SESSION['id'])
                        ? 'panier_user_' . (int) $_SESSION['id']
                        : 'panier_guest';
                    $headerCartCount = isset($_SESSION[$headerCartKey]) && is_array($_SESSION[$headerCartKey])
                        ? array_sum(array_column($_SESSION[$headerCartKey], 'quantite'))
                        : 0;
                ?>
                <a class='headerButtons cart-btn' href="<?= Page::Panier->url() ?>">Panier
                    <?php if ($headerCartCount > 0): ?>
                        <span class="cart-badge" id="cart-badge"><?= $headerCartCount ?></span>
                    <?php else: ?>
                        <span class="cart-badge cart-badge-hidden" id="cart-badge">0</span>
                    <?php endif; ?>
                </a>
                <a class='headerButtons' href="<?= Page::Enigme->url() ?>">Énigma</a>
                <span class="header-status connected" title="Connecté"><?= $headerAlias ?></span>
                <a href="<?= Page::Profil->url() ?>"><img class="avatar" src="<?= AVATAR . $_SESSION['avatar'] ?>" alt="Avatar"></a>
                <a href="logout.php" class="logout-btn" title="Se déconnecter">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            <?php else: ?>
                <span class="header-status disconnected" title="Non connecté">Visiteur</span>
                <a href="<?= Page::Connexion->url() ?>"><img class="avatar" src="<?= AVATAR . 'avatar_default.jpg' ?>" alt="Avatar"></a>
            <?php endif; ?>
        </div>

    </div>
</header>