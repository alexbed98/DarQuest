<?php

use Dom\Document;

include_once 'core/Database.php';
include_once 'src/ItemDAL.php';
include_once 'src/initialization.php';

$connexion = Database::getConnexion($dbConfig);
$items = ItemDAL::selectAll($connexion);

?>

<div class="catalogue">
    <div class="options">
        <div style="border: 1px solid black; border-radius: 5px;">
            Nb pièces
        </div>
            <div>
                <legend>Trier par:</legend>
                <input type="radio" id="prix" value="prix"/>
                <label for="prix">Prix</label>
                <div></div>
                <input type="radio" id="type" value="Type" />
                <label for="type">Type</label>
                <div></div>
                <input type="radio" id="" value=""/>
                <label for=""></label>
            </div>
            <div>
                <legend>Filtrer:</legend>
                <input class="filtre" type="checkbox" id="arme" name="filtre" value="A" onclick="changeLayout('A')"/>
                <label for="armes">Armes</label>
                <div></div>
                <input class="filtre" type="checkbox" id="armure" name="filtre" value="R"/>
                <label for="armure">Armures</label>
                <div></div>
                <input class="filtre" type="checkbox" id="potion" name="filtre" value="P"/>
                <label for="potion">Potion</label>
                <div></div>
                <input class="filtre" type="checkbox" id="sort" name="filtre" value="S"/>
                <label for="sort">Sort</label>
                <div></div>

                <input class="filtre" type="checkbox" id="sub" id="sub"/>

            </div>

    </div>
        <form id="filtrer" method="post">
    <div class="list-item">
        <?php foreach ($items as $item): ?>

            <div class="item">
                <div style="border: 2px solid black;">
                    <!-- <img src="< ?= $item['photo'] ?>" alt="Image de l'article" style="width: 100px; height: 100px;"> -->
                </div>
                <div
                    style="border: 2px solid black; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 5px; margin-top: 10px; width: 300px;">
                    <div class="nom"><?= $item["nom"] ?></div>
                    <div class="quantite"><?= $item["quantiteStock"] ?></div>
                    <div class="prix"><?= $item["prix"] ?></div>
                    <?php if (IS_AUTH) : ?>
                    <button>Ajouter</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </form>
    </div>
</div>
@{
    <script>
        function changeLayout(val) {
            const items = document.querySelectorAll('.filtre');
            alert(val);
            alert(items);

        }
    </script>
}