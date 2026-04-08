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
        <form id="trier">
            <div>
                <legend>Trier par:</legend>
                <!-- <input type="radio" id="prix" value="prix" onclick="this.form.submit()" /> -->
                <!-- <input type="radio" id="prix" value="prix" onclick="changeLayout('prix')" /> -->
                <input type="radio" id="prix" value="prix" onclick="changeLayout('prix')" />
                <label for="prix">Prix</label>
                <div></div>
                <input type="radio" id="type" value="Type" onclick="changeLayout('type')" />
                <label for="type">Type</label>
                <div></div>
                <input type="radio" id="" value="" onclick="this.form.submit()" />
                <label for=""></label>
            </div>
        </form>
            <div>
                <legend>Filtrer:</legend>
                <!-- <input class="filtre" type="checkbox" id="arme" name="arme" value="A" onchange="filter(this.value)" /> -->
                <!-- <input class="filtre" type="checkbox" id="arme" name="arme" value="A" onchange="filter()" /> -->
                <!-- <input class="filtre" type="checkbox" id="arme" name="arme" value="A" onchange="< ?php fixFiltre()?>" /> -->
                <input class="filtre" type="checkbox" id="arme" name="filtre" value="A" onclick="from.submit()" <?php if(in_array('A',ItemDAL::$filtre)) echo 'checked="checked"'?>/>
                <label for="armes">Armes</label>
                <div></div>
                <!-- <input class="filtre" type="checkbox" id="armure" id="armure" value="R" onchange="filter()" /> -->
                <input class="filtre" type="checkbox" id="armure" name="filtre" value="R" onchange="this.form.submit()" />
                <label for="armure">Armures</label>
                <div></div>
                <!-- <input class="filtre" type="checkbox" id="potion" id="potion" value="P" onchange="filter()" /> -->
                <input class="filtre" type="checkbox" id="potion" name="filtre" value="P" onchange="this.form.submit()" />
                <label for="potion">Potion</label>
                <div></div>
                <!-- <input class="filtre" type="checkbox" id="sort" id="sort" value="S" onchange="filter()" /> -->
                <input class="filtre" type="checkbox" id="sort" name="filtre" value="S" onchange="this.form.submit()" />
                <label for="sort">Sort</label>
                <div></div>

                <input class="filtre" type="checkbox" id="sub" id="sub" onchange="this.form.submit()" />

            </div>

    </div>
    <div class="list-item">
        <?php foreach ($items as $item): ?>

            <div class="item">
                <div style="border: 2px solid black;">
                    <img src=<?= $item['photo'] ?> alt="Image de l'article" style="width: 100px; height: 100px;">
                </div>
                <div
                    style="border: 2px solid black; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 5px; margin-top: 10px; width: 300px;">
                    <div class="nom"><?= $item["nom"] ?></div>
                    <div class="quantite"><?= $item["quantiteStock"] ?></div>
                    <div class="prix"><?= $item["prix"] ?></div>
                    <!-- Ce formulaire envoie l'id de l'item à catalogue.php pour l'ajout panier utilisateur. -->
                    <form method="post" action="" style="margin: 0;">
                        <input type="hidden" name="add_item_id" value="<?= (int) $item['idItem'] ?>">
                        <button type="submit">Ajouter</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
