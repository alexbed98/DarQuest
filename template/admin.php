<div style="display: flex; justify-content: space-around;">
    <button class="mainButton" onclick="toggleForm('demandesJoueurs')">Demandes des joueurs</button>
    <button class="mainButton" onclick="toggleForm('itemFormContainer')"> Création des items </button>
    <button class="mainButton" onclick="toggleForm('enigmeFormContainer')"> Création des quêtes </button>
</div>

<div id="itemFormContainer" class="toggleForm" style="display:none;">
        <div>
            <label>Nom</label>
            <input type="text" name="nom" required />
        </div>

        <div>
            <label>Quantité en stock</label>
            <input type="number" name="quantiteStock" required />
        </div>

        <div>
            <label>Prix</label>
            <input type="number" name="prix" required />
        </div>

        <div>
            <label>Photo</label>
            <input type="file" name="photoFile" />
        </div>

        <div>
            <label>Type (char)</label>
            <input type="text" name="typeItem" maxlength="1" required />
        </div>

        <div>
            <label>Disponible</label>
            <input type="checkbox" name="estDisponible" checked />
        </div>

        <button type="submit" class="mainButton">Créer</button>
</div>


<div id="enigmeFormContainer" class="toggleForm" style="display:none;">

    <form method="post" action="/Enigmes/Create">

        <!-- ENIGME -->
        <h3>Énigme</h3>

        <label>Énoncé</label>
        <input type="text" name="enonce" required />

        <label>Catégorie</label>
        <input type="text" name="idCategorie" maxlength="1" />

        <label>Difficulté</label>
        <input type="text" name="difficulte" maxlength="1" required />

        <input type="hidden" name="estPigee" value="0" />
        <input type="checkbox" id="estPigee" name="estPigee" value="1" />
        <label for="estPigee">Est pigée</label>

        <hr />

        <!-- REPONSES -->
        <h3>Réponses</h3>

        <label>Réponse 1</label>
        <input type="text" name="reponses[0].reponse" />
        <input type="radio" name="bonneReponse" value="0" />

        <label>Réponse 2</label>
        <input type="text" name="reponses[1].reponse" />
        <input type="radio" name="bonneReponse" value="1" />

        <label>Réponse 3</label>
        <input type="text" name="reponses[2].reponse" />
        <input type="radio" name="bonneReponse" value="2" />

        <br /><br />

        <button type="submit" class="mainButton">Créer l’énigme</button>
    </form>
</div>



<div id="demandesJoueurs" class="toggleForm" style="display:none;">
    <div>
        <div class="demande-text">
            <p><strong>Joueur:</strong> Sam</p>
            <p><strong>Demande:</strong> Donne moi de l'argent! Je suis pauvre</p>
        </div>

        <div class="demande-actions">
            <button class="mainButton btn-accepter">Accepter</button>
            <button class="mainButton btn-refuser">Refuser</button>
        </div>
    </div>
</div>



<script>
    function toggleForm(formId) {

        var forms = document.querySelectorAll(".toggleForm");

        forms.forEach(f => {
            if (f.id !== formId) {
                f.style.display = "none";
            }
        });

        var target = document.getElementById(formId);

        if (target.style.display === "none" || target.style.display === "") {
            target.style.display = "block";
        } else {
            target.style.display = "none";
        }
    }
</script>