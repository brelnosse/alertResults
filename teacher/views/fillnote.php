<?php
require_once __DIR__ . '/../../shared/init.php';
require_once __DIR__ . '/../../shared/models/matiere_model.php';
require_once __DIR__ . '/../init_teacher.php';

// Vérifier si l'utilisateur est connecté en tant qu'enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../index.php');
    exit;
}

// Récupérer l'ID de l'enseignant connecté
$teacher_id = $_SESSION['user_id'];

// Récupérer les matières enseignées par l'enseignant
$matieres_enseignees = getMatieresEnseigneesParEnseignant($teacher_id);

// Si l'enseignant n'enseigne aucune matière, rediriger avec un message
if (empty($matieres_enseignees)) {
    $_SESSION['error_message'] = "Vous n'êtes affecté à aucune matière. Veuillez contacter l'administration.";
    header('Location: ../index.php'); // Ou vers une autre page appropriée
    exit;
}

// Fonction pour récupérer les matières enseignées par un enseignant
function getMatieresEnseigneesParEnseignant(int $enseignant_id): array
{
    $host = 'localhost';        // Adresse du serveur de base de données
    $dbname = 'alertResults';        // Nom de la base de données
    $username = 'root'; // Nom d'utilisateur
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    $query = "SELECT 
                    me.id AS matiere_id, 
                    me.matiere AS matiere_libelle, 
                    me.salle AS salle_nom, 
                    me.niveau AS niveau_nom
                FROM matieres_enseignees me
                WHERE me.id_enseignant = :enseignant_id
                AND me.status = 'approved'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':enseignant_id', $enseignant_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les niveaux
function getNiveaux($enseignant_id) {
    $host = 'localhost';        // Adresse du serveur de base de données
    $dbname = 'alertResults';        // Nom de la base de données
    $username = 'root'; // Nom d'utilisateur
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    $query = "SELECT DISTINCT me.niveau  
                FROM matieres_enseignees me
                WHERE me.id_enseignant = :enseignant_id
                AND me.status = 'approved'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':enseignant_id', $enseignant_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$niveaux = getNiveaux($teacher_id);

// Fonction pour récupérer les salles
function getSalles($enseignant_id): array {
    $host = 'localhost';        // Adresse du serveur de base de données
    $dbname = 'alertResults';        // Nom de la base de données
    $username = 'root'; // Nom d'utilisateur
    $password = '';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    $query = "SELECT DISTINCT salle  
                FROM matieres_enseignees  
                WHERE id_enseignant = :enseignant_id
                AND status = 'approved'";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':enseignant_id', $enseignant_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$salles = getSalles($teacher_id);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Saisie des Notes</title>
    <link rel="stylesheet" href="../../shared/assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/dashboard_default.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background-color: #3498db;
            color: white;
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .note-input {
            width: calc(100% - 22px);
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-top: 5px;
        }
        .header-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        .hide-bonus-column th:nth-child(4),
        .hide-bonus-column td:nth-child(4) {
            display: none;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../views/navbar.php'; ?>
    <?php include_once __DIR__ . '/../views/sidebar.php'; ?>

    <div class="container">
        <h1>Système de Saisie des Notes</h1>

        <div class="form-row">
            <div class="form-group">
                <label for="matiere">Matière</label>
                <select id="matiere" name="matiere" onchange="updateSallesOptions()">
                    <option value="">Sélectionnez la matière</option>
                    <?php foreach ($matieres_enseignees as $matiere): ?>
                        <option value="<?php echo $matiere['matiere_libelle']; ?>">
                            <?php echo $matiere['matiere_libelle']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="niveau">Niveau</label>
                <select id="niveau" name="niveau" onchange="updateSallesOptions()">
                    <option value="">Sélectionnez le niveau</option>
                    <?php foreach ($niveaux as $niveau): ?>
                        <option value="<?php echo $niveau['niveau']; ?>"><?php echo $niveau['niveau']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="salle">Salle</label>
                <select id="salle" name="salle">
                    <option value="">Sélectionnez la salle</option>
                </select>
            </div>
            <div class="form-group">
                <label for="periode">Période</label>
                <select id="periode" onchange="updateTableHeaders()">
                    <option value="">Sélectionnez la période</option>
                    <option value="cc1">CC1</option>
                    <option value="cc2">CC2</option>
                    <option value="SN1">SN1</option>
                    <option value="RAT1">RAT1</option>
                    <option value="cc3">CC3</option>
                    <option value="cc4">CC4</option>
                    <option value="SN2">SN2</option>
                    <option value="RAT2">RAT2</option>
                </select>
            </div>
        </div>

        <div class="header-info" id="classe-info">
            Veuillez sélectionner la matière, le niveau, la salle et la période pour afficher le tableau de saisie des notes.
        </div>

        <div class="actions">
            <button onclick="chargerEleves()">Charger les élèves</button>
            <button onclick="enregistrerNotes()">Enregistrer les notes</button>
        </div>

        <div class="table-container">
            <table id="tableNotes">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Matricule</th>
                        <th>Nom et Prénom</th>
                        <th>Bonus Assiduité</th>
                        <th id="note-header">Note</th>
                        <th id="note-finale-header">Note Finale</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    </tbody>
            </table>
        </div>

        <div id="message" class="success" style="display: none;"></div>
    </div>
    <script src="../assets/js/dashboard1.js"></script>
    <script src="../assets/js/dashboard2.js"></script>
    <script>
        const matieresniveau1libelle = [];
        const matieresniveau1 = <?= json_encode(getMatieresByNiveau(1)) ?>;
        for(let i = 0; i < matieresniveau1.length; i++){
            matieresniveau1libelle.push(matieresniveau1[i].libelle);
        }

        const matieresniveau2libelle = [];
        const matieresniveau2 = <?= json_encode(getMatieresByNiveau(2)) ?>;
        for(let i = 0; i < matieresniveau2.length; i++){
            matieresniveau2libelle.push(matieresniveau2[i].libelle);
        }
        // Données des matières disponibles par niveau
        const matieresByNiveau = {
            "1": {
                "A": matieresniveau1libelle,
                "B": matieresniveau1libelle,
                "C": matieresniveau1libelle,
                "D": matieresniveau1libelle
            },
            "2": {
                "A": matieresniveau2libelle,
                "B": matieresniveau2libelle,
                "C": matieresniveau2libelle,
                "D": matieresniveau2libelle
            }
        };
        const matieres_enseignees_js = <?php echo json_encode($matieres_enseignees); ?>;
        const niveaux_js = <?php echo json_encode($niveaux); ?>;
        const salles_js = <?php echo json_encode($salles); ?>;

        
        const sallesParMatiereEtNiveau = {};

        matieres_enseignees_js.forEach(item => {
            const matiereLibelle = item.matiere_libelle; // Use matiere_libelle
            const niveauNom = item.niveau_nom;
            const salleNom = item.salle_nom;


            if (!sallesParMatiereEtNiveau[matiereLibelle]) { // Use matiere_libelle as key
                sallesParMatiereEtNiveau[matiereLibelle] = {};
            }
            if (!sallesParMatiereEtNiveau[matiereLibelle][niveauNom]) { // Use niveau_nom as key
                sallesParMatiereEtNiveau[matiereLibelle][niveauNom] = [];
            }
            sallesParMatiereEtNiveau[matiereLibelle][niveauNom].push(salleNom); // Store salleNom
           
        });

        function updateSallesOptions() {
            const matiereSelect = document.getElementById('matiere');
            const niveauSelect = document.getElementById('niveau');
            const salleSelect = document.getElementById('salle');
            const classeInfo = document.getElementById("classe-info");


            const matiereLibelle = matiereSelect.value; // Get selected matiere_libelle
            const niveauNom = niveauSelect.value;  // Get selected niveau_nom

            salleSelect.innerHTML = '<option value="">Sélectionnez la salle</option>'; // Clear previous options.
            console.log(matiereLibelle, niveauNom);

            if (matiereLibelle && niveauNom && sallesParMatiereEtNiveau[matiereLibelle] && sallesParMatiereEtNiveau[matiereLibelle][niveauNom]) {
                const sallesNoms = sallesParMatiereEtNiveau[matiereLibelle][niveauNom]; // Get salleNoms for selected matiere and niveau
                sallesNoms.forEach(salleNom => {
                    const option = document.createElement('option');
                    option.value = salleNom;         // Use salleNom as value
                    option.textContent = salleNom; // Display salleNom
                    salleSelect.appendChild(option);
                });
            }
            updateClasseInfo();
        }


        // Données simulées d'élèves (dans une application réelle, ces données viendraient d'une base de données)
        let elevesData = [];



        // Fonction pour mettre à jour les en-têtes du tableau en fonction de la période sélectionnée
        function updateTableHeaders() {
            const periode = document.getElementById("periode").value;
            const noteHeader = document.getElementById("note-header");
            const noteFinaleHeader = document.getElementById("note-finale-header");
            const table = document.getElementById("tableNotes");

            // Liste des périodes sans bonus
            const periodesSansBonus = ['SN1', 'SN2', 'RAT1', 'RAT2'];

            if (periode) {
                noteHeader.textContent = `Note ${periode}`;
                noteFinaleHeader.textContent = `Note Finale ${periode}`;

                // Ajoute/retire la classe pour cacher le bonus
                if (periodesSansBonus.includes(periode)) {
                    table.classList.add('hide-bonus-column');
                } else {
                    table.classList.remove('hide-bonus-column');
                }
            } else {
                noteHeader.textContent = "Note";
                noteFinaleHeader.textContent = "Note Finale";
                table.classList.remove('hide-bonus-column');
            }
            updateClasseInfo();
        }

        // Fonction pour mettre à jour l'information de la classe
        function updateClasseInfo() {
            const matiere = document.getElementById("matiere").value;
            const niveau = document.getElementById("niveau").value;
            const salle = document.getElementById("salle").value;
            const periode = document.getElementById("periode").value;
            const classeInfo = document.getElementById("classe-info");

            const matiereLibelle = matiere; // Use directly the selected value
            const niveauNom = niveau; // Use directly the selected value
            const salleNom = salle;


            if (matiere && niveau && salle && periode) {
                classeInfo.textContent = `Matière: ${matiereLibelle} | Niveau: ${niveauNom} | Salle: ${salleNom} | Période: ${periode}`;
            } else {
                classeInfo.textContent = "Veuillez sélectionner la matière, le niveau, la salle et la période pour afficher le tableau de saisie des notes.";
            }
        }

        // Fonction pour charger les élèves dans le tableau
        function chargerEleves() {
            const matiere = document.getElementById("matiere").value;
            const niveau = document.getElementById("niveau").value;
            const salle = document.getElementById("salle").value;
            const periode = document.getElementById("periode").value;
            const tableBody = document.getElementById("tableBody");

            // Vérifier si tous les champs nécessaires sont remplis
            if (!matiere || !niveau || !salle || !periode) {
                showMessage("Veuillez remplir tous les champs requis.", "error");
                return;
            }

            // Vider le tableau actuel
            tableBody.innerHTML = "";
                fetch('get_student_notes.php?matiere='+matiere+'&salle='+salle+'&niveau='+niveau)

                .then(response => response.json())
                .then(data => {
                    elevesData = data;
                    // Ajouter les élèves au tableau
                    elevesData.forEach((eleve, index) => {
                        const row = document.createElement("tr");
                        // Numéro
                        const cellNum = document.createElement("td");
                        cellNum.textContent = index + 1;
                        row.appendChild(cellNum);

                        // Matricule
                        const cellMatricule = document.createElement("td");
                        cellMatricule.textContent = eleve.matricule;
                        row.appendChild(cellMatricule);

                        // Nom et prénom
                        const cellNom = document.createElement("td");
                        cellNom.textContent = eleve.nom;
                        row.appendChild(cellNom);

                        // Bonus assiduité
                        const cellBonus = document.createElement("td");
                        const inputBonus = document.createElement("input");
                        inputBonus.type = "number";
                        inputBonus.className = "note-input";
                        inputBonus.min = "0";
                        inputBonus.max = "2";
                        inputBonus.step = "0.25";
                        inputBonus.value = eleve.notes[`${periode}_bonus`] || "";
                        inputBonus.dataset.matricule = eleve.matricule;
                        inputBonus.dataset.type = "bonus";
                        cellBonus.appendChild(inputBonus);
                        row.appendChild(cellBonus);

                        // Note
                        const cellNote = document.createElement("td");
                        const inputNote = document.createElement("input");
                        inputNote.type = "number";
                        inputNote.className = "note-input";
                        inputNote.min = "0";
                        inputNote.max = "20";
                        inputNote.step = "0.25";
                        inputNote.value = eleve.notes[`${periode}_note`] || "";
                        inputNote.dataset.matricule = eleve.matricule;
                        inputNote.dataset.type = "note";
                        inputNote.addEventListener("input", calculerNoteFinale);
                        cellNote.appendChild(inputNote);
                        row.appendChild(cellNote);

                        // Note finale
                        const cellNoteFinale = document.createElement("td");
                        const inputNoteFinale = document.createElement("input");
                        inputNoteFinale.type = "number";
                        inputNoteFinale.className = "note-input";
                        inputNoteFinale.min = "0";
                        inputNoteFinale.max = "20";
                        inputNoteFinale.step = "0.25";
                        inputNoteFinale.value = eleve.notes[`${periode}_final`] || "";
                        inputNoteFinale.disabled = true;
                        inputNoteFinale.dataset.matricule = eleve.matricule;
                        inputNoteFinale.dataset.type = "note_finale";
                        cellNoteFinale.appendChild(inputNoteFinale);
                        row.appendChild(cellNoteFinale);

                        tableBody.appendChild(row);
                    });

            showMessage("Les élèves ont été chargés avec succès.", "success");
                })
                .catch(error => console.error('Erreur réseau:', error));  
        }

        // Fonction pour calculer la note finale (note + bonus)
        function calculerNoteFinale(event) {
            const matricule = event.target.dataset.matricule;
            const row = event.target.closest("tr");
            const bonusInput = row.querySelector('input[data-type="bonus"]');
            const noteInput = row.querySelector('input[data-type="note"]');
            const noteFinaleInput = row.querySelector('input[data-type="note_finale"]');

            const bonus = parseFloat(bonusInput.value) || 0;
            const note = parseFloat(noteInput.value) || 0;

            // Calculer la note finale (note + bonus, maximum 20)
            let noteFinale = note + bonus;
            noteFinale = Math.min(noteFinale, 20); // La note finale ne peut pas dépasser 20

            // Arrondir à 2 décimales
            noteFinale = Math.round(noteFinale * 100) / 100;

            noteFinaleInput.value = noteFinale;
        }

        // Fonction pour enregistrer les notes
        function enregistrerNotes() {
            const matiere = document.getElementById("matiere").value;
            const niveau = document.getElementById("niveau").value;
            const salle = document.getElementById("salle").value;
            const periode = document.getElementById("periode").value;

            // Vérifier si tous les champs nécessaires sont remplis
            if (!matiere || !niveau || !salle || !periode) {
                showMessage("Veuillez remplir tous les champs requis.", "error");
                return;
            }

            // Récupérer toutes les notes
            const notesInputs = document.querySelectorAll(".note-input");
            const notes = {};

            notesInputs.forEach(input => {
                if (input.dataset.matricule && input.dataset.type) {
                    if (!notes[input.dataset.matricule]) {
                        notes[input.dataset.matricule] = {};
                    }

                    const type = input.dataset.type;
                    const valeur = input.value ? parseFloat(input.value) : null;

                    if (type === "bonus") {
                        notes[input.dataset.matricule][`${periode}_bonus`] = valeur;
                    } else if (type === "note") {
                        notes[input.dataset.matricule][periode] = valeur;
                    } else if (type === "note_finale") {
                        notes[input.dataset.matricule][`${periode}_finale`] = valeur;
                    }
                }
            });

            // Afficher un indicateur de chargement
            const saveButton = document.querySelector("button:nth-of-type(2)");
            const originalText = saveButton.textContent;
            saveButton.textContent = "Enregistrement en cours...";
            saveButton.disabled = true;

            // Récupérer l'ID de la matière
            getMatiereId(matiere)
                .then(matiereId => {
                    // Préparer les données à envoyer
                    const dataToSend = {
                        matiereId: matiereId,
                        matiereLibelle: matiere,
                        niveau: niveau,
                        salle: salle,
                        periode: periode,
                        notes: notes
                    };

                    // Envoyer les données au serveur
                    return fetch('save_notes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(dataToSend)
                    });
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        showMessage(result.message, "success");
                        
                        // Mettre à jour les données locales
                        for (const matricule in notes) {
                            const eleve = elevesData.find(e => e.matricule === matricule);
                            if (eleve) {
                                for (const key in notes[matricule]) {
                                    eleve.notes[key] = notes[matricule][key];
                                }
                            }
                        }
                    } else {
                        showMessage(result.message, "error");
                        console.error("Erreurs:", result.errors);
                    }
                })
                .catch(error => {
                    showMessage("Erreur lors de l'enregistrement des notes: " + error.message, "error");
                    console.error("Erreur:", error);
                })
                .finally(() => {
                    // Réactiver le bouton d'enregistrement
                    saveButton.textContent = originalText;
                    saveButton.disabled = false;
                });
        }

        // Fonction pour récupérer l'ID de la matière
        async function getMatiereId(matiere) {
            try {
                const response = await fetch('get_matiere_id.php?libelle=' + encodeURIComponent(matiere));
                const data = await response.json();
                
                if (data.success) {
                    return data.id; // Retourne l'ID de la matière
                } else {
                    throw new Error(data.error || "Impossible de récupérer l'ID de la matière");
                }
            } catch (error) {
                console.error("Erreur lors de la récupération de l'ID de la matière:", error);
                throw error;
            }
        }

        // Fonction pour afficher un message
        function showMessage(text, type) {
            const messageElement = document.getElementById("message");
            messageElement.textContent = text;
            messageElement.className = type;
            messageElement.style.display = "block";

            // Cacher le message après 5 secondes
            setTimeout(() => {
                messageElement.style.display = "none";
            }, 5000);
        }

        // Initialiser les écouteurs d'événements
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("matiere").addEventListener("change", updateSallesOptions);
            document.getElementById("niveau").addEventListener("change", updateSallesOptions);
            document.getElementById("salle").addEventListener("change", updateClasseInfo);
            updateTableHeaders();
            updateSallesOptions();// Pour initialiser les options de salle au chargement de la page.
        });
    </script>
</body>
</html>
