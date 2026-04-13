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

        if (isset($_POST['order'])) //Modifie l'odre selon l'input radio coché (name="order")
            ItemDAL::changeOrder($_POST['order']);

        $items = ItemDAL::select($connexion); //change/reset la liste d'items pour satisfaire les nouveaux critères
        ?>

        document.getElementById('setLayout').submit(); //Soumet le formulaire pour rafraichir la page et afficher les items selon les nouveaux critères
    }

</script>

<div class="catalogue">
    <div class="options">
        <div style="border: 1px solid black; border-radius: 5px;">
            <?php if (IS_AUTH) {
                //besoin de changer pour qu'il affiche le nombre d'or de l'utilisateur connecté
            } else
                echo '0'; ?>
        </div>
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
                <div style="border: 2px solid black;">
                    <img src="<?= $item['photo'] ?>" alt="Image de l'article" style="width: 100px; height: 100px;">
                </div>
                <div
                    style="border: 2px solid black; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 5px; margin-top: 10px; width: 300px;">
                    <div class="nom"><?= $item["nom"] ?></div>
                    <div class="quantite"><?= $item["quantiteStock"] ?></div>
                    <div class="prix"><?= $item["prix"] ?></div>
                    <?php if (IS_AUTH): ?>
                        <button>Ajouter</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>