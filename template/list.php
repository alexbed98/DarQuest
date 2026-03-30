<?php

include_once 'core/Database.php';
include_once 'src/ItemDAL.php';
include_once 'src/initialization.php';

$connexion = Database::getConnexion($dbConfig);
$items = ItemDAL::selectAll($connexion);

?>

<div class="catalogue">
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