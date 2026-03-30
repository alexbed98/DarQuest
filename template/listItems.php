<?php

use Dom\Document;

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
ItemDAL::insertSort($connexion, 'sort', 20, 50, 'potion.jpg', 1, 1, 2, 's');
ItemDAL::insertPotion($connexion, 'potion', 15, 30, 'potion.jpg', 1, 0, 1);
$items = ItemDAL::selectAll($connexion);

// $electedFilters = isset($_POST['filtre']) ? $_POST['filtre'] : [];
function a(){
ItemDAL::addToFilter("A");

}
if ($_SERVER['REQUEST_METHOD'] === 'POST') //{ function fixFiltre()
{

$items = ItemDAL::selectAll($connexion);
    // if (isset($_POST['arme'])) {
    //     ItemDAL::addToFilter("A");
    //     $_GET['arme'] = true;
    // } 
    // //else
    //     ItemDAL::removeFromFilter("A");

    // if (isset($_REQUEST['armure']))
    //     ItemDAL::addToFilter("R");
    // else
    //     ItemDAL::removeFromFilter("R");

    // if (isset($_REQUEST['potion']))
    //     ItemDAL::addToFilter("P");
    // else
    //     ItemDAL::removeFromFilter("P");

    // if (isset($_REQUEST['sort']))
    //     ItemDAL::addToFilter("S");
    // else
    //     ItemDAL::removeFromFilter("S");

    //echo "<script>alert('" . count(ItemDAL::$filtre) . "');</script>";

}

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
        <form id="filtrer" method="post">
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
        </form>
    </div>
</div>
<script>
    // $("#filtre").on('change', function () {
    //     this.form.submit();
    // });
    function filter() {
        // alert(< ?= count(ItemDAL::$filtre) ?>);


        // < ?php
        // if ($_POST['input#arme'])
        //     echo "alert('yes');";
        // // ItemDAL::addToFilter("A");
        // else
        //     echo "alert('nope');";
        // // ItemDAL::removeFromFilter("A");
        // // if($_GET['armure'] != null)
        // //     ItemDAL::addToFilter("R");
        // // else
        // //     ItemDAL::removeFromFilter("R");
        // // if($_GET['potion'] != null)
        // //     ItemDAL::addToFilter("P");
        // // else
        // //     ItemDAL::removeFromFilter("P");
        // // if($_GET['sort'] != null)
        // //     ItemDAL::addToFilter("S");
        // // else
        // //     ItemDAL::removeFromFilter("S");

        // ?>

        // //  alert(< ?= count(ItemDAL::$filtre) ?>);
    }

    function changeLayout(what) { }
</script>