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
ItemDAL::insertArme($connexion,'hache', 10, 100, 'hache.jpg', 1, 'Une hache légendaire', '60', 'hache');  
ItemDAL::insertArmure($connexion,'armure', 5, 200, 'armure.jpg', 1, 'metal', 'xl');  
// ItemDAL::insertSort($connexion,'sort', 20, 50, 'potion.jpg', 1, 1, 2, 97);
ItemDAL::insertPotion($connexion,'potion', 15, 30, 'potion.jpg', 1, 0, 1);
$items = ItemDAL::selectAll($connexion);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gender'])) {
    $gender_value = htmlspecialchars($_POST['gender']);
    
    // You can perform actions here, like saving to a database
    // use an official source like the PHP Manual for specific functions
    // e.g. check values as shown on [Stack Overflow](https://stackoverflow.com/questions/21147132/how-to-keep-radio-button-value-inside-php-variable-without-error)
    if ($gender_value == 'male') {
        echo "You selected Male. Value received: " . $gender_value;
    } elseif ($gender_value == 'female') {
        echo "You selected Female. Value received: " . $gender_value;
    } else {
        echo "Selection received: " . $gender_value;
    }
} else {
    echo "No gender value received or invalid request method.";
}
?>

<div class="catalogue">
    <div class="options">
        <div style="border: 1px solid black; border-radius: 5px;">
            Nb pièces
        </div>
        <form id="form" action="">
        <div>
            <legend>Trier par:</legend>
            <input type="radio" id="prix" value="prix"/>
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
            <input type="checkbox" id="arme" onclick="submitForm()" />
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
        </form>
    </div>
<div class="list-item">
    <?php foreach ($items as $item): ?>
        <div class="item">
            <div style="border: 2px solid black;">
                <img src=<?=  $item['photo'] ?> alt="Image de l'article"
                    style="width: 100px; height: 100px;">
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
<script>
    function submitForm() {
        // Find the selected radio button within the form
        const selectedRadio = document.querySelector('input[name="arme"]:checked');
        
        if (selectedRadio) {
            const value = selectedRadio.value;

            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `gender=${encodeURIComponent(value)}`
            })
            .then(response => response.text())
            .then(data => {
                // Handle the response from the PHP script
                console.log('Success:', data);
                document.getElementById('response_message').innerText = data;
            })
            .catch((error) => {
                console.error('Error:', error);
            });
        }
    }
</script>