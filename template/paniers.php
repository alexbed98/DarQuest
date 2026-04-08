<?php
// La clé de session du panier est liée à l'utilisateur connecté.
if (!empty($_SESSION['id'])) {
	$cartSessionKey = 'panier_user_' . (int) $_SESSION['id'];
} elseif (!empty($_SESSION['email'])) {
	$cartSessionKey = 'panier_user_' . md5(strtolower((string) $_SESSION['email']));
} else {
	$cartSessionKey = 'panier_guest';
}

if (!isset($_SESSION[$cartSessionKey]) || !is_array($_SESSION[$cartSessionKey])) {
	$_SESSION[$cartSessionKey] = [];
}

// Alias local vers le panier de l'utilisateur courant.
$panier = &$_SESSION[$cartSessionKey];

// Retirer un item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Retirer
	if (isset($_POST['remove_id'])) {
		$panier = array_filter(
			$panier,
			function ($item) {
				return $item['id'] != $_POST['remove_id'];
			}
		);
		$panier = array_values($panier);
	}
	// Modifier quantité
	if (isset($_POST['update_id'], $_POST['update_qty'])) {
		foreach ($panier as &$item) {
			if ($item['id'] == $_POST['update_id']) {
				$qty = (int)$_POST['update_qty'];
				$item['quantite'] = max(1, $qty);
				break;
			}
		}
		unset($item);
	}

	// Synchroniser avec la BD si l'utilisateur est connecté
	if (!empty($_SESSION['id']) && isset($connexion)) {
		CartDAL::saveCart($connexion, (int) $_SESSION['id'], $panier);
	}
}
$total = 0;
?>
<section class="panier-section">
	<div class="panier-table-wrapper">
		<table class="panier-table">
			<thead>
				<tr>
					<th scope="col">Produit</th>
					<th scope="col">Prix unitaire</th>
					<th scope="col">Quantité</th>
					<th scope="col">Total</th>
					<th scope="col">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($panier)): ?>
					<tr>
						<td colspan="5" class="panier-empty">Votre panier est vide.</td>
					</tr>
				<?php endif; ?>
				<?php foreach ($panier as $item):
					$sous_total = $item['prix'] * $item['quantite'];
					$total += $sous_total;
				?>
				<tr>
					<td>
						<div class="panier-product">
							<img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="panier-thumb">
							<span class="panier-product-name"><?= htmlspecialchars($item['nom']) ?></span>
						</div>
					</td>
					<td><?= number_format($item['prix']) ?>&nbsp;Pièces d'or</td>
					<td>
						<form method="post" class="panier-qty-form auto-submit-form">
							<input type="hidden" name="update_id" value="<?= $item['id'] ?>">
							<input type="number" name="update_qty" class="panier-qty-input auto-submit-input" value="<?= $item['quantite'] ?>" min="1">
						</form>
					</td>
					<td><?= number_format($sous_total) ?>&nbsp;Pièces d'or</td>
					<td>
						<form method="post">
							<input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
							<button type="submit" class="panier-remove-button">Retirer</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="panier-summary">
		<p class="panier-total">Total : <span class="panier-total-value"><?= number_format($total) ?>&nbsp;Pièces d'or</span></p>
		<button class="panier-checkout-button" <?= empty($panier) ? 'disabled' : '' ?>>Passer la commande</button>
	</div>
</section>
<script>
// Soumission automatique du formulaire à chaque changement de quantité
document.querySelectorAll('.auto-submit-input').forEach(function(input) {
	input.addEventListener('change', function() {
		input.form.submit();
	});
});
</script>
