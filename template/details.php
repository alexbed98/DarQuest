<div class="mainContainer">
<!-- Côté gauche de la page -->
<div class="conteneur">
    <h2>Le nom de l'item</h2>

    <p><strong>Description de l'item:</strong> Lorem ipsum dolor sit amet consectetur adipisicing elit. Sit rerum porro dicta ipsum eveniet. Aliquid laborum vel aut facilis modi quod, deleniti dicta, ipsum ea dolores reprehenderit recusandae nulla nihil?</p>
    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Est quam ipsam sit dolor ipsa aliquam impedit, atque ullam consequuntur placeat molestiae quia? Aliquid optio iste sint magni laudantium asperiores exercitationem.</p>
    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ab maiores nostrum amet alias officiis sequi, et voluptatibus reiciendis cumque doloribus dolores, voluptatum nulla ullam obcaecati, sunt est tenetur delectus adipisci!</p>
    <div id="quantiteStock">
        <p>Quantité en stock: </p>
        <p>45</p>
    </div>
    
    <div id="prix">
        <p>Prix: </p>
        <p id="prixItem" type="number">35</p>
        <p>Balise img avec logo pièce</p>
    </div>
    <input type="number" name="update_qty" class="panier-qty-input auto-submit-input" value="1" min="1">
</div>

<!-- Côté droit de la page -->
<div class="conteneur">
    <div id="nbPiece">
        <p>Nombre de pièces:</p>
        <p>500🪙</p>
    </div>

    <div class="conteneur" style="display: flex; justify-content: center; align-items: center;">
        <img src="image/photo.avif" alt="image item" style="height: 300px; width: 500px;">
    </div>

    <div style="display: flex; flex-direction: row; justify-content: space-around; margin-top: 4em;">
        <p id="prixTotal"></p>
        <button>Ajouter au panier</button>
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


