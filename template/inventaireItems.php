<?php $potionMessage = $_SESSION['potion_message'] ?? ''; ?>
<?php unset($_SESSION['potion_message']); ?>

<?php $sortMessage = $_SESSION['sort_message'] ?? ''; ?>
<?php unset($_SESSION['sort_message']); ?>

<?php $venteMessage = $_SESSION['vente_message'] ?? ''; ?>
<?php unset($_SESSION['vente_message']); ?>

<?php if (!empty($potionMessage)): ?>
    <div class="inventaire-notification inventaire-notification-success" role="status" aria-live="polite">
        <?= htmlspecialchars($potionMessage) ?>
    </div>
<?php endif; ?>

<?php if (!empty($sortMessage)): ?>
    <div class="inventaire-notification inventaire-notification-success" role="status" aria-live="polite">
        <?= htmlspecialchars($sortMessage) ?>
    </div>
<?php endif; ?>

<?php if (!empty($venteMessage)): ?>
    <div class="inventaire-notification inventaire-notification-success" role="status" aria-live="polite">
        <?= htmlspecialchars($venteMessage) ?>
    </div>
<?php endif; ?>

<div class="catalogue">

    <!-- Panneau latéral (filtre/tri) -->
    <div class="options">
        <div class="inventaire-gold">
            <span title="Or"><?= number_format($gold) ?>&nbsp;🥇</span>
            <span title="Argent"><?= number_format($argent) ?>&nbsp;🥈</span>
            <span title="Bronze"><?= number_format($bronze) ?>&nbsp;🥉</span>
        </div>

        <!-- Conversion bronze → argent -->
        <form method="post" action="">
            <button type="submit" name="convertir" value="bronze_to_argent"
                class="headerButtons inventaire-convertir-btn"
                <?= $bronze < 10 ? 'disabled title="Il faut au moins 10 bronze"' : 'title="10 bronze = 1 argent"' ?>>
                🥉→🥈 Convertir
            </button>
        </form>

        <!-- Conversion argent → or -->
        <form method="post" action="">
            <button type="submit" name="convertir" value="argent_to_gold"
                class="headerButtons inventaire-convertir-btn"
                <?= $argent < 10 ? 'disabled title="Il faut au moins 10 argent"' : 'title="10 argent = 1 or"' ?>>
                🥈→🥇 Convertir
            </button>
        </form>

        <form method="get" action="" id="inventaire-filters-form">
            <div class="inventaire-filters-placeholder" style="opacity:1;">
                <p class="inventaire-soon">Trier par :</p>
                <label class="inventaire-filter-label">
                    <input type="radio" name="tri" value="nom" <?= ($triActif === 'nom') ? 'checked' : '' ?> onchange="this.form.submit()"> Nom
                </label>
                <label class="inventaire-filter-label">
                    <input type="radio" name="tri" value="prix_asc" <?= ($triActif === 'prix_asc') ? 'checked' : '' ?> onchange="this.form.submit()"> Prix ↑
                </label>
                <label class="inventaire-filter-label">
                    <input type="radio" name="tri" value="prix_desc" <?= ($triActif === 'prix_desc') ? 'checked' : '' ?> onchange="this.form.submit()"> Prix ↓
                </label>
                <label class="inventaire-filter-label">
                    <input type="radio" name="tri" value="type" <?= ($triActif === 'type') ? 'checked' : '' ?> onchange="this.form.submit()"> Type
                </label>

                <p class="inventaire-soon">Filtrer :</p>
                <label class="inventaire-filter-label">
                    <input type="checkbox" name="filtre[]" value="A" <?= in_array('A', $filtresActifs) ? 'checked' : '' ?> onchange="this.form.submit()"> Armes
                </label>
                <label class="inventaire-filter-label">
                    <input type="checkbox" name="filtre[]" value="R" <?= in_array('R', $filtresActifs) ? 'checked' : '' ?> onchange="this.form.submit()"> Armures
                </label>
                <label class="inventaire-filter-label">
                    <input type="checkbox" name="filtre[]" value="P" <?= in_array('P', $filtresActifs) ? 'checked' : '' ?> onchange="this.form.submit()"> Potions
                </label>
                <label class="inventaire-filter-label">
                    <input type="checkbox" name="filtre[]" value="S" <?= in_array('S', $filtresActifs) ? 'checked' : '' ?> onchange="this.form.submit()"> Sorts
                </label>
            </div>
        </form>
    </div>

    <!-- Grille des items -->
    <div class="list-item">

        <?php if (empty($inventaire)): ?>
            <p class="inventaire-empty">Votre inventaire est vide.</p>
        <?php endif; ?>

        <?php foreach ($inventaire as $item):
            $photo = (string) $item['photo'];
            $image = ($photo !== '' && $photo[0] === '/')
                ? $photo
                : IMG . '/items/' . ltrim($photo, '/');
            $detailUrl = Page::Details->url() . '?idItem=' . (int) $item['idItem'];
        ?>
            <div class="item inventaire-item">
                <a href="<?= htmlspecialchars($detailUrl) ?>" class="inventaire-detail-link" title="Voir les details de <?= htmlspecialchars($item['nom']) ?>">
                    <div class="inventaire-img-wrapper">
                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                    </div>
                </a>
                <div class="inventaire-info">
                    <a href="<?= htmlspecialchars($detailUrl) ?>" class="inventaire-detail-link inventaire-nom" title="Voir les details de <?= htmlspecialchars($item['nom']) ?>">
                        <?= htmlspecialchars($item['nom']) ?>
                    </a>
                    <span class="inventaire-prix"><?= number_format((float) $item['prix']) ?>&nbsp;🥇</span>

                    <?php
                        $isPotion = ($item['typeItem'] ?? '') === 'P';
                        $isSort = ($item['typeItem'] ?? '') === 'S';
                    ?>

                    <?php if ($isPotion): ?>
                        <form method="post" action="<?= Page::Inventaire->url() ?>" class="inventaire-consommer-form">
                            <input type="hidden" name="consommer_potion_id" value="<?= (int) $item['idItem'] ?>">
                            <button type="submit" class="inventaire-consommer-btn">Consommer</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($isSort): ?>
                        <form method="post" action="<?= Page::Inventaire->url() ?>" class="inventaire-lancer-form">
                            <input type="hidden" name="lancer_sort_id" value="<?= (int) $item['idItem'] ?>">
                            <button type="submit" class="inventaire-lancer-btn">Lancer</button>
                        </form>
                    <?php endif; ?>

                    <form method="post" action="<?= Page::Inventaire->url() ?>" class="inventaire-vendre-form">
                        <input type="hidden" name="vendre_id" value="<?= (int) $item['idItem'] ?>">
                        <input
                            type="number"
                            name="vendre_qty"
                            class="inventaire-qty-input"
                            value="1"
                            min="1"
                            max="<?= (int) $item['quantiteInventaire'] ?>"
                        >
                        <button type="submit" class="inventaire-vendre-btn">Vendre</button>
                    </form>
                    <span class="inventaire-qte">Qté : <?= (int) $item['quantiteInventaire'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>
