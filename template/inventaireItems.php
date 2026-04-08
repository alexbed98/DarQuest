<div class="catalogue">

    <!-- Panneau latéral (filtre/tri désactivé pour l'instant) -->
    <div class="options">
        <div class="inventaire-gold">
            <span><?= number_format($gold) ?>&nbsp;🪙</span>
        </div>
        <div class="inventaire-filters-placeholder">
            <p class="inventaire-soon">Trier par :</p>
            <label class="inventaire-filter-label"><input type="radio" disabled> Prix</label>
            <label class="inventaire-filter-label"><input type="radio" disabled> Type</label>

            <p class="inventaire-soon">Filtrer :</p>
            <label class="inventaire-filter-label"><input type="checkbox" disabled> Armes</label>
            <label class="inventaire-filter-label"><input type="checkbox" disabled> Armures</label>
            <label class="inventaire-filter-label"><input type="checkbox" disabled> Potions</label>
            <label class="inventaire-filter-label"><input type="checkbox" disabled> Sorts</label>
        </div>
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
                : '/public/img/' . ltrim($photo, '/');
        ?>
            <div class="item inventaire-item">
                <div class="inventaire-img-wrapper">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                </div>
                <div class="inventaire-info">
                    <span class="inventaire-nom"><?= htmlspecialchars($item['nom']) ?></span>
                    <span class="inventaire-prix"><?= number_format((float) $item['prix']) ?>&nbsp;🪙</span>
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
