<?php

include_once 'core/Database.php';
include_once 'src/ItemDAL.php';
include_once 'src/initialization.php';
$dbConfig = [
    "dbHost" => "127.0.0.1",
    "dbName" => "darquest",
    "dbUser" => "root",
    "dbPass" => "",
    "dbParams" => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ],
];

$connexion = Database::getConnexion($dbConfig);
ItemDAL::resetItems($connexion);
ItemDAL::insertArme($connexion, 'hache', 10, 100, 'hache.jpg', 1, 'Une hache légendaire', '60', 'hache');
ItemDAL::insertArmure($connexion, 'armure', 5, 200, 'armure.jpg', 1, 'metal', 'xl');
ItemDAL::insertSort($connexion,'sort', 20, 50, 'potion.jpg', 1, 1, 2, 's');
ItemDAL::insertPotion($connexion, 'potion', 15, 30, 'potion.jpg', 1, 0, 1);
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
        <form id="filtrer">
            <div>
                <legend>Filtrer:</legend>
                <input type="checkbox" id="arme" onclick="this.form.submit()" />
                <label for="armes">Armes</label>
                <div></div>
                <input type="checkbox" id="armure" onclick="this.form.submit()" />
                <label for="armure">Armures</label>
                <div></div>
                <input type="checkbox" id="" onclick="this.form.submit()" />
                <label for=""></label>
                <div></div>
                <input type="checkbox" id="" onclick="this.form.submit()" />
                <label for=""></label>
            </div>
        </form>

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
                    <button>Ajouter</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- <script>
    function filter(){

    }

    function changeLayout(what) {

        //     < ?php $items = ItemDAL::selectAll($connexion); ?>

        //     if(what == 'prix'){
        //         < ?php
        //         ItemDAL::$OrderBy = 'ORDER BY prix';
        //         $items = ItemDAL::selectAllOrderByPrice($connexion); ?>
        //     } 
        //     if(what == 'type'){
        //         < ?php
        //         ItemDAL::$OrderBy = 'ORDER BY typeItem';
        //         $items = ItemDAL::selectAll($connexion); ?>
        //     }
        //     console.log(< ?php echo json_encode($items); ?>);

    }
</script> -->