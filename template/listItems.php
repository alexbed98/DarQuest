<div class="catalogue">
    <div class="options">
        <div style="border: 1px solid black; border-radius: 5px;">
            Nb pièces
        </div>
        <div>
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
        </div>
    </div>

    <div class="list-item">
        <?php for ($i = 0; $i < 10; $i++) : ?>
        <div class="item">
            <div style="border: 2px solid black;">
                <img src="TheBoi.png" alt="Image de l'article" style="width: 100px; height: 100px;">
            </div>
            <div
                style="border: 2px solid black; display: flex; flex-direction: row; align-items: center; justify-content: space-between; padding: 5px; margin-top: 10px;">
                <div>Item 1</div>
                <div>qte</div>
                <div>prix</div>
                <button>Ajouter</button>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>