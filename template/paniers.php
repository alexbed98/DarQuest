<?php
// Simulation d'un panier en session (exemple statique)
if (!isset($_SESSION['panier'])) {
	$_SESSION['panier'] = [
		[
			'id' => 1,
			'nom' => 'Produit 1',
			'image' => '/public/img/produit1.jpg',
			'prix' => 15.00,
			'quantite' => 2
		],
		[
			'id' => 2,
			'nom' => 'Produit 2',
			'image' => '/public/img/produit2.jpg',
			'prix' => 25.00,
			'quantite' => 1
		],
	];
}

// Retirer un item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Retirer
	if (isset($_POST['remove_id'])) {
		$_SESSION['panier'] = array_filter(
			$_SESSION['panier'],
			function ($item) {
				return $item['id'] != $_POST['remove_id'];
			}
		);
		$_SESSION['panier'] = array_values($_SESSION['panier']);
	}
	// Modifier quantité
	if (isset($_POST['update_id'], $_POST['update_qty'])) {
		foreach ($_SESSION['panier'] as &$item) {
			if ($item['id'] == $_POST['update_id']) {
				$qty = (int)$_POST['update_qty'];
				$item['quantite'] = max(1, $qty);
				break;
			}
		}
		unset($item);
	}
}
$total = 0;
?>
<div class="container py-5">
	<h2 class="mb-4">Mon Panier</h2>
	<div class="table-responsive">
		<table class="table align-middle">
			<thead class="table-light">
				<tr>
					<th scope="col">Produit</th>
					<th scope="col">Prix unitaire</th>
					<th scope="col">Quantité</th>
					<th scope="col">Total</th>
					<th scope="col">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($_SESSION['panier'] as $item):
					$sous_total = $item['prix'] * $item['quantite'];
					$total += $sous_total;
				?>
				<tr>
					<td>
						<div class="d-flex align-items-center">
							<img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" width="60" class="me-3 rounded">
							<span><?= htmlspecialchars($item['nom']) ?></span>
						</div>
					</td>
					<td><?= number_format($item['prix'], 2, ',', ' ') ?>&nbsp;$</td>
					<td>
						<form method="post" class="d-flex align-items-center auto-submit-form" style="gap:0.5rem;">
							<input type="hidden" name="update_id" value="<?= $item['id'] ?>">
							<input type="number" name="update_qty" class="form-control auto-submit-input" value="<?= $item['quantite'] ?>" min="1" style="width:80px;">
						</form>
					</td>
					<td><?= number_format($sous_total, 2, ',', ' ') ?>&nbsp;$</td>
					<td>
						<form method="post" style="display:inline;">
							<input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
							<button type="submit" class="btn btn-danger btn-sm">Retirer</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-end align-items-center mt-4">
		<h4 class="me-4">Total : <span class="text-success"><?= number_format($total, 2, ',', ' ') ?>&nbsp;$</span></h4>
		<button class="btn btn-primary btn-lg" <?= empty($_SESSION['panier']) ? 'disabled' : '' ?>>Passer la commande</button>
	</div>
</div>
<script>
// Soumission automatique du formulaire à chaque changement de quantité
document.querySelectorAll('.auto-submit-input').forEach(function(input) {
	input.addEventListener('change', function() {
		input.form.submit();
	});
});
</script>
