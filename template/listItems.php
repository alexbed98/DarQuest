<?php

// include_once '../DAL/Database.php';
//$items = ItemsDAL::selectAll(Database::getConnexion($dbConfig));
// $affiche = $_GET['submit'] ?? '';


$items = [
    [
        "id" => 1,
        "nom" => "Épée de feu",
        "categorie" => "arme",
        "image" => "TheBoi.png",
        "quantite" => 10,
        "prix" => 100,
    ],[
        "id" => 1,
        "nom" => "Bouclier de glace",
        "categorie" => "armure",
        "image" => "TheBoi.png",
        "quantite" => 10,
        "prix" => 100,
    ],
];

if(isset($_GET['prix'])) {
echo "prix";
}

        // <?php foreach($items as $item) : ?>

<div class="catalogue">
    <div class="options">
        <div style="border: 1px solid black; border-radius: 5px;">
            Nb pièces
        </div>
        <form action="changeAffichage" method="post">
        <div>
            <legend>Trier par:</legend>
            <input type="radio" id="prix" value="prix" />
            <label for="prix">Prix</label>
            <div></div>
            <input type="radio" id="type" value="Type" />
            <label for="type">Type</label>
            <div></div>
            <input type="radio" id="" value="" />
            <label for=""></label>
        </div>
        <div>
            <legend>Filtrer:</legend>
            <input type="checkbox" id="arme" />
            <label for="armes">Armes</label>
            <div></div>
            <input type="checkbox" id="armure" />
            <label for="armure">Armures</label>
            <div></div>
            <input type="checkbox" id="" />
            <label for=""></label>
            <div></div>
            <input type="checkbox" id="" />
            <label for=""></label>
        </div>
        <div>
            <!-- <input type="submit" value="Submit" /> -->
        </div>
        </form>
        <!-- <div>
            <legend>Trier par:</legend>
            <input type="radio" id="prix" />
            <label for="prix">Prix</label>
            <div></div>
            <input type="radio" id="type" />
            <label for="type">Type</label>
            <div></div>
            <input type="radio" id="" />
            <label for=""></label>
        </div>
        <div>
            <legend>Filtrer:</legend>
            <input type="checkbox" id="arme" />
            <label for="armes">Armes</label>
            <div></div>
            <input type="checkbox" id="armure" />
            <label for="armure">Armures</label>
            <div></div>
            <input type="checkbox" id="" />
            <label for=""></label>
            <div></div>
            <input type="checkbox" id="" />
            <label for=""></label>
        </div> -->
    </div>

    <div class="list-item">
        <?php foreach($items as $item) : ?>
        <div class="item">
            <div style="border: 2px solid black;">
                <img src=<?= PRODUCT_IMG . '/' . $item['image'] ?> alt="Image de l'article" style="width: 100px; height: 100px;">
            </div>
            <div
                style="border: 2px solid black; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 5px; margin-top: 10px; width: 300px;">
                <div class="nom"><?= $item["nom"] ?></div>
                <div class="quantite"><?= $item["quantite"] ?></div>
                <div class="prix"><?= $item["prix"] ?></div>
                <button>Ajouter</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>