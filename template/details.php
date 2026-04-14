<div class="mainContainer">
<!-- Côté gauche de la page -->
<div class="conteneur">
    <h2>Le nom de l'item</h2>

    <p><strong>Description de l'item:</strong> Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit rerum porro dicta ipsum eveniet. Aliquid laborum vel aut facilis modi quod, deleniti dicta, ipsum ea dolores reprehenderit recusandae nulla nihil?</p>
    <div style="display: flex; flex-direction: row; justify-content: space-around;">
        <div style="display: flex; flex-direction: row;">
            <p>Quantité en stock: &nbsp;</p>
            <p>45</p>
        </div>

        <div style="display: flex; flex-direction: row;">
            <p>Prix: &nbsp;</p>
            <p id="prixItem" type="number">35</p>
            <p>🪙</p>
        </div>
    </div>
    
    <div style="display: flex; flex-direction: row; justify-content: space-around;">
        <input type="number" name="update_qty" class="panier-qty-input auto-submit-input" value="1" min="1">
        <div style="display: flex; flex-direction: row;">
            <p>Total: &nbsp;</p>
            <p id="prixTotal"></p>
        </div>
    </div>
    
    <div style="display: flex; flex-direction: row; justify-content: space-around; margin-top: 2em;">
        <div>
            <button class="panier-checkout-button">Ajouter au panier</button>
        </div>

        <div id="nbPiece">
            <p>Nombre de pièces: &nbsp;</p>
            <p>500🪙</p>
        </div>
    </div>
</div>

<!-- Côté droit de la page -->
<div class="conteneur">
    <div class="conteneur" style="display: flex; justify-content: center; align-items: center;">
        <img src="image/photo.avif" alt="image item" style="height: 300px; width: 500px;">
    </div>
</div>

</div>
<script>
    const prixUnitaire = parseInt(document.querySelector('#prixItem').textContent);
    const inputQty = document.querySelector('.panier-qty-input');
    const prixTotalElement = document.querySelector('#prixTotal');

    function calculPrixTotal() {
        const quantite = parseInt(inputQty.value);
        const prixTotal = prixUnitaire * quantite;

        prixTotalElement.textContent = prixTotal + ' 🪙';
    }

    calculPrixTotal();

    inputQty.addEventListener('input', calculPrixTotal);
</script>


