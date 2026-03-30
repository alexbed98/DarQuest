<?php
$stmt = $connexion->query("SELECT idItem, nom, prix, photo, quantiteStock, typeItem, estDisponible FROM items");
?>

<div class="container">
<?php while ($item = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
    <div class="card">
        <h2><?php echo htmlspecialchars($item['nom']); ?></h2>

        <p>Prix : <?php echo htmlspecialchars($item['prix']); ?> $</p>

        <p>Quantité en stock : <?php echo htmlspecialchars($item['quantiteStock']); ?></p>

        <p>Type : <?php echo htmlspecialchars($item['typeItem']); ?></p>

        <p>
            Statut : 
            <?php echo $item['estDisponible'] ? "Disponible" : "Indisponible"; ?>
        </p>

        <img src="<?php echo htmlspecialchars($item['photo']); ?>" alt="Image de <?php echo htmlspecialchars($item['nom']); ?>" width="150">

    </div>
<?php endwhile; ?>
</div>