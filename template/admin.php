<?php
    $connexion = Database::getConnexion($dbConfig);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

        $action = $_POST['action'];

        if ($action === 'accepter_demande') {

            $idDemande = (int)$_POST['idDemande'];

            EnigneDAL::accepterDemande($connexion, $idDemande);

            header("Location: admin.php");
            exit;
        }

        if ($action === 'refuser_demande') {

            $idDemande = (int)$_POST['idDemande'];

            EnigneDAL::refuserDemande($connexion, $idDemande);

            header("Location: admin.php");
            exit;
        }
    }

    $demandes = EnigneDAL::selectDemandesEnAttente($connexion);
?>

<div class="admin-toolbar">
    <button class="mainButton admin-tab-btn" onclick="toggleForm('demandesJoueurs', this)">Demandes des joueurs</button>
    <button class="mainButton admin-tab-btn" onclick="toggleForm('itemFormContainer', this)">Creation des items</button>
    <button class="mainButton admin-tab-btn" onclick="toggleForm('enigmeFormContainer', this)">Creation des quetes</button>
</div>

<?php if ($itemSuccess ?? false): ?>
    <div class="admin-alert admin-alert-success">Item cree avec succes !</div>
<?php elseif (!empty($itemError)): ?>
    <div class="admin-alert admin-alert-error"><?= htmlspecialchars($itemError) ?></div>
<?php endif; ?>
<?php if ($enigmeSuccess ?? false): ?>
    <div class="admin-alert admin-alert-success">Enigme creee avec succes !</div>
<?php elseif (!empty($enigmeError)): ?>
    <div class="admin-alert admin-alert-error"><?= htmlspecialchars($enigmeError) ?></div>
<?php endif; ?>

<div id="itemFormContainer" class="toggleForm admin-panel">
    <div class="admin-panel-card">
        <h2 class="admin-panel-title">Creation des items</h2>
        <p class="admin-panel-subtitle">Ajoute un nouvel objet a la boutique.</p>

        <form class="admin-form-grid" method="post" action="/admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create_item" />

            <!-- Champs communs -->
            <div class="admin-field">
                <label for="itemNom">Nom</label>
                <input id="itemNom" type="text" name="nom" required />
            </div>

            <div class="admin-field">
                <label for="itemStock">Quantite en stock</label>
                <input id="itemStock" type="number" name="quantiteStock" min="0" required />
            </div>

            <div class="admin-field">
                <label for="itemPrix">Prix</label>
                <input id="itemPrix" type="number" name="prix" min="0" required />
            </div>

            <div class="admin-field">
                <label for="itemPhoto">Photo</label>
                <input id="itemPhoto" type="file" name="photoFile" accept="image/*" />
            </div>

            <div class="admin-field">
                <label for="itemType">Type</label>
                <select id="itemType" name="typeItem" required onchange="showItemTypeFields(this.value)">
                    <option value="">-- Choisir un type --</option>
                    <option value="A">Arme (A)</option>
                    <option value="R">Armure (R)</option>
                    <option value="P">Potion (P)</option>
                    <option value="S">Sort (S)</option>
                </select>
            </div>

            <div class="admin-field admin-field-checkbox">
                <label for="itemDisponible">Disponible</label>
                <input id="itemDisponible" type="checkbox" name="estDisponible" checked />
            </div>

            <!-- Champs specifiques: Arme -->
            <div id="fields-arme" class="admin-field-group admin-field-full" style="display:none;">
                <h4 class="admin-subgroup-title">Details arme</h4>
                <div class="admin-form-grid two-cols">
                    <div class="admin-field">
                        <label for="armeDesc">Description</label>
                        <input id="armeDesc" type="text" name="description" />
                    </div>
                    <div class="admin-field">
                        <label for="armeEfficacite">Efficacite</label>
                        <input id="armeEfficacite" type="text" name="efficacite" />
                    </div>
                    <div class="admin-field">
                        <label for="armeGenre">Genre (ex: epee, arc)</label>
                        <input id="armeGenre" type="text" name="genreArme" />
                    </div>
                </div>
            </div>

            <!-- Champs specifiques: Armure -->
            <div id="fields-armure" class="admin-field-group admin-field-full" style="display:none;">
                <h4 class="admin-subgroup-title">Details armure</h4>
                <div class="admin-form-grid two-cols">
                    <div class="admin-field">
                        <label for="armureMatiere">Matiere</label>
                        <input id="armureMatiere" type="text" name="matiere" />
                    </div>
                    <div class="admin-field">
                        <label for="armureTaille">Taille</label>
                        <input id="armureTaille" type="text" name="taille" />
                    </div>
                </div>
            </div>

            <!-- Champs specifiques: Potion -->
            <div id="fields-potion" class="admin-field-group admin-field-full" style="display:none;">
                <h4 class="admin-subgroup-title">Details potion</h4>
                <div class="admin-form-grid two-cols">
                    <div class="admin-field">
                        <label for="potionEffet">Effet</label>
                        <input id="potionEffet" type="text" name="effet" />
                    </div>
                    <div class="admin-field">
                        <label for="potionDuree">Duree (tours)</label>
                        <input id="potionDuree" type="number" name="duree" min="0" />
                    </div>
                </div>
            </div>

            <!-- Champs specifiques: Sort -->
            <div id="fields-sort" class="admin-field-group admin-field-full" style="display:none;">
                <h4 class="admin-subgroup-title">Details sort</h4>
                <div class="admin-form-grid two-cols">
                    <div class="admin-field">
                        <label for="sortType">Type de sort</label>
                        <input id="sortType" type="text" name="typeSort" />
                    </div>
                    <div class="admin-field">
                        <label for="sortRarete">Rarete</label>
                        <input id="sortRarete" type="number" name="rarete" min="0" />
                    </div>
                    <div class="admin-field admin-field-checkbox">
                        <label for="sortInstantane">Instantane</label>
                        <input id="sortInstantane" type="checkbox" name="instantane" value="1" />
                    </div>
                </div>
            </div>

            <div class="admin-actions">
                <button type="submit" class="mainButton">Creer item</button>
            </div>
        </form>
    </div>
</div>


<div id="enigmeFormContainer" class="toggleForm admin-panel">

    <form method="post" action="/admin.php" class="admin-panel-card admin-form-quest">
        <input type="hidden" name="action" value="create_enigme" />

        <h2 class="admin-panel-title">Creation des quetes</h2>
        <p class="admin-panel-subtitle">Configure l'enigme et ses reponses en un seul formulaire.</p>

        <div class="admin-field admin-field-full">
            <label for="enigmeEnonce">Enonce</label>
            <input id="enigmeEnonce" type="text" name="enonce" required />
        </div>

        <div class="admin-form-grid two-cols">
            <div class="admin-field">
                <label for="enigmeCategorie">Categorie</label>
                <input id="enigmeCategorie" type="text" name="idCategorie" maxlength="1" />
            </div>

            <div class="admin-field">
                <label for="enigmeDifficulte">Difficulte</label>
                <input id="enigmeDifficulte" type="text" name="difficulte" maxlength="1" required />
            </div>
        </div>

        <div class="admin-field admin-field-checkbox admin-field-full">
            <input type="hidden" name="estPigee" value="0" />
            <input type="checkbox" id="estPigee" name="estPigee" value="1" />
            <label for="estPigee">Est pigee</label>
        </div>

        <h3 class="admin-section-title">Reponses</h3>

        <div class="admin-answer-row">
            <label for="rep1">Reponse 1</label>
            <input id="rep1" type="text" name="reponses[0]" />
            <label class="answer-check" for="bonneRep1">
                <input id="bonneRep1" type="radio" name="bonneReponse" value="0" />
                Bonne
            </label>
        </div>

        <div class="admin-answer-row">
            <label for="rep2">Reponse 2</label>
            <input id="rep2" type="text" name="reponses[1]" />
            <label class="answer-check" for="bonneRep2">
                <input id="bonneRep2" type="radio" name="bonneReponse" value="1" />
                Bonne
            </label>
        </div>

        <div class="admin-answer-row">
            <label for="rep3">Reponse 3</label>
            <input id="rep3" type="text" name="reponses[2]" />
            <label class="answer-check" for="bonneRep3">
                <input id="bonneRep3" type="radio" name="bonneReponse" value="2" />
                Bonne
            </label>
        </div>

        <div class="admin-actions">
            <button type="submit" class="mainButton">Creer l'enigme</button>
        </div>
    </form>
</div>



<div id="demandesJoueurs" class="toggleForm" style="display:none;">
    <?php if (!empty($demandes)): ?>
        <?php foreach ($demandes as $demande): ?>
            <div class="demande-card">
                <div class="demande-text">
                    <p><strong>Joueur:</strong> <?= htmlspecialchars($demande['alias']) ?></p>
                    <p><strong>Demande:</strong> Demande d'argent</p>
                </div>
                <div class="demande-actions">

                    <!-- ACCEPTER -->
                    <form method="post" action="/admin.php" style="display:inline;">
                        <input type="hidden" name="action" value="accepter_demande">
                        <input type="hidden" name="idDemande" value="<?= $demande['idDemande'] ?>">
                        <button class="mainButton btn-accepter">Accepter</button>
                    </form>

                    <!-- REFUSER -->
                    <form method="post" action="/admin.php" style="display:inline;">
                        <input type="hidden" name="action" value="refuser_demande">
                        <input type="hidden" name="idDemande" value="<?= $demande['idDemande'] ?>">
                        <button class="mainButton btn-refuser">Refuser</button>
                    </form>
                </div>
            </div>  
        <?php endforeach; ?>

    <?php else: ?>
        <p>Aucune demande pour le moment.</p>
    <?php endif; ?>

</div>



<script>
    function toggleForm(formId, button) {

        var forms = document.querySelectorAll(".toggleForm");
        var tabs = document.querySelectorAll(".admin-tab-btn");

        tabs.forEach(t => t.classList.remove("is-active"));

        forms.forEach(f => {
            if (f.id !== formId) {
                f.style.display = "none";
            }
        });

        var target = document.getElementById(formId);

        if (target.style.display === "none" || target.style.display === "") {
            target.style.display = "block";
            if (button) {
                button.classList.add("is-active");
            }
        } else {
            target.style.display = "none";
        }
    }

    function showItemTypeFields(type) {
        var groups = ['fields-arme', 'fields-armure', 'fields-potion', 'fields-sort'];
        var map = { 'A': 'fields-arme', 'R': 'fields-armure', 'P': 'fields-potion', 'S': 'fields-sort' };

        groups.forEach(function(id) {
            document.getElementById(id).style.display = 'none';
        });

        if (map[type]) {
            document.getElementById(map[type]).style.display = 'block';
        }
    }
</script>