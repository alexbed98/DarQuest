<?php

use Dom\Document;

include_once 'core/Database.php';
include_once 'src/ItemDAL.php';
include_once 'src/initialization.php';

$connexion = Database::getConnexion($dbConfig);
$items = ItemDAL::select($connexion);

?>
<script>
    function changeLayout() {
        <?php
        $filtre = $_POST['filtre'] ?? [];

        foreach ($filtre as $f) //Modifie les filtres selon les inputs checkbox cochés (name="filtre[]")
            ItemDAL::changeFilter($f);

        if (isset($_POST['order'])) //Modifie l'ordre selon l'input radio coché (name="order")
            ItemDAL::changeOrder($_POST['order']);

        $items = ItemDAL::select($connexion); //change/reset la liste d'items pour satisfaire les nouveaux critères
        ?>

        document.getElementById('setLayout').submit(); //Soumet le formulaire pour rafraichir la page et afficher les items selon les nouveaux critères
    }

</script>

<div class="catalogue">
    <div class="options">

        <form id="setLayout" method="POST" action="">
            <div>
                <legend>Trier par:</legend>
                <input type="radio" id="prix" name="order" value="prix" onclick="changeLayout()"
                    <?= (ItemDAL::$OrderByWhat == "prix") ? "checked" : "" ?> />
                <label for="prix">Prix</label>
                <div></div>
                <input type="radio" id="type" name="order" value="Type" onclick="changeLayout()"
                    <?= (ItemDAL::$OrderByWhat == "typeItem") ? "checked" : "" ?> />
                <label for="type">Type</label>
            </div>
            <div>
                <legend>Filtrer:</legend>
                <input class="filtre" type="checkbox" id="arme" name="filtre[]" value="A" onclick="changeLayout()"
                    <?= in_array("A", ItemDAL::$filtre) ? "checked" : "" ?> />
                <!-- //changeLayout()" /> -->
                <label for="armes">Armes</label>
                <div></div>
                <input class="filtre" type="checkbox" id="armure" name="filtre[]" value="R" onclick="changeLayout()"
                    <?= in_array("R", ItemDAL::$filtre) ? "checked" : "" ?> />
                <label for="armure">Armures</label>
                <div></div>
                <input class="filtre" type="checkbox" id="potion" name="filtre[]" value="P" onclick="changeLayout()"
                    <?= in_array("P", ItemDAL::$filtre) ? "checked" : "" ?> />
                <label for="potion">Potion</label>
                <div></div>
                <input class="filtre" type="checkbox" id="sort" name="filtre[]" value="S" onclick="changeLayout()"
                    <?= in_array("S", ItemDAL::$filtre) ? "checked" : "" ?> />
                <label for="sort">Sort</label>
                <div></div>
            </div>

        </form>
    </div>
    <div class="list-item">
        <?php foreach ($items as $item): ?>

            <div class="item">
                <a href="detail.php?idItem=<?= $item['idItem'] ?>">
                    <div class="background" style="background-image: url('/public/img/backgrounds/background06');">
                        <img class="image-item" src=/public/img/items/<?= $item['photo'] ?> alt="Image de l'article">
                    </div>
                </a>

                <div <div class="item-info">
                    <div class="item-info-top">
                        <div class="nom"><?= $item["nom"] ?></div>
                    </div>
                    <div class="item-info-bottom">
                        <div class="quantite">Qty: <?= $item["quantiteStock"] ?></div>
                        <div class="prix"><?= $item["prix"] ?>&nbsp;🥇</div>
                        <!-- Ce formulaire envoie l'id de l'item à catalogue.php pour l'ajout panier utilisateur. -->
                        <form method="post" action="" style="margin: 0;">
                            <input type="hidden" name="add_item_id" value="<?= (int) $item['idItem'] ?>">
                            <button type="submit" class="item-add-btn">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>