<input type="hidden" name="user_id" id="user_id" value="<?= $_SESSION['user_id']; ?>">
<div class="main-content">
<header class="header">
    <button class="toggle-menu">
        <i class="fas fa-bars"></i>
    </button>
    <div class="header-title">Tableau de bord</div>
    <div class="header-actions">
        <button class="btn btn-outline">
            <i class="fas fa-bell"></i>
        </button>
        <button class="btn btn-primary" id="add-subject-buttton">
            <i class="fas fa-plus"></i> Nouvelle matière
        </button>
    </div>
</header>
    <div class="popup">
        <div class="popup-header">
            <h2 class="popup-title">Vos matières</h2>
            <button class="close-btn">&times;</button>
        </div>
        <div class="popup-body">
            <div class="container" id="add-subject-container">
                <h1>Sélection des Matières Enseignées</h1>

                <div id="subject-blocks">
                    <!-- Le premier bloc de matière sera généré automatiquement -->
                </div>
                
                <div class="actions">
                    <button id="add-subject" type="button">Ajouter une matière</button>
                    <button id="save-all" type="button">Soumettre les matières à la vérification du chef de département</button>
                </div>
                
                <div id="summary" class="summary hidden">
                    <h2>Récapitulatif des matières sélectionnées</h2>
                    <div id="summary-content">
                    </div>
                </div>
            </div>
            <!-- Barre de recherche et bouton d'ajout -->
            <div class="controls">
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Rechercher une matière...">
                    <i>🔍</i>
                </div>
                <button class="add-btn" id="addBtn">
                    <span>+</span> Ajouter
                </button>
            </div>

            <!-- Tableau des matières -->
            <div class="table-container">
                <table id="matieresTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="selectAll" class="custom-checkbox">
                                </div>
                            </th>
                            <th>Libellé</th>
                            <th>Code</th>
                            <th>Crédit</th>
                            <th>Niveau</th>
                            <th>Classe</th>
                            <th>Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Les données seront insérées ici via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>