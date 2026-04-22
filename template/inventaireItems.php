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
                : '/public/img/items/' . ltrim($photo, '/');
        ?>
            <div class="item inventaire-item">
                <div class="inventaire-img-wrapper">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                </div>
                <div class="inventaire-info">
                    <span class="inventaire-nom"><?= htmlspecialchars($item['nom']) ?></span>
                    <span class="inventaire-prix"><?= number_format((float) $item['prix']) ?>&nbsp;🥇</span>
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
