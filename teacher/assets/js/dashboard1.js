let blockCounter = 0;

// Fonction pour créer un nouveau bloc de matière
function createSubjectBlock() {
    blockCounter++;
    const blockId = `subject-block-${blockCounter}`;
    
    const subjectBlock = document.createElement('div');
    subjectBlock.className = 'subject-block';
    subjectBlock.id = blockId;
    
    const blockTitle = document.createElement('div');
    blockTitle.className = 'block-title';
    blockTitle.textContent = `Matière #${blockCounter}`;
    subjectBlock.appendChild(blockTitle);
    
    // Ajout du bouton de suppression (sauf pour le premier bloc)
    if (blockCounter > 1) {
        const removeButton = document.createElement('button');
        removeButton.className = 'remove-btn';
        removeButton.textContent = 'Supprimer';
        removeButton.onclick = function() {
            document.getElementById(blockId).remove();
        };
        subjectBlock.appendChild(removeButton);
    }
    
    // Ligne 1: Niveau et Salle
    const row1 = document.createElement('div');
    row1.className = 'form-row';
    
    // Sélection du niveau
    const niveauGroup = document.createElement('div');
    niveauGroup.className = 'form-group';
    
    const niveauLabel = document.createElement('label');
    niveauLabel.textContent = 'Niveau';
    niveauLabel.htmlFor = `niveau-${blockId}`;
    niveauGroup.appendChild(niveauLabel);
    
    const niveauSelect = document.createElement('select');
    niveauSelect.id = `niveau-${blockId}`;
    niveauSelect.dataset.blockId = blockId;
    
    const niveauPlaceholder = document.createElement('option');
    niveauPlaceholder.value = '';
    niveauPlaceholder.textContent = 'Sélectionnez le niveau';
    niveauSelect.appendChild(niveauPlaceholder);
    
    Object.keys(matieresByNiveau).forEach(niveau => {
        const option = document.createElement('option');
        option.value = niveau;
        option.textContent = `Niveau ${niveau}`;
        niveauSelect.appendChild(option);
    });
    
    niveauSelect.onchange = function() {
        updateSallesAndMatieres(this.dataset.blockId);
    };
    
    niveauGroup.appendChild(niveauSelect);
    row1.appendChild(niveauGroup);
    
    // Sélection de la salle
    const salleGroup = document.createElement('div');
    salleGroup.className = 'form-group';
    
    const salleLabel = document.createElement('label');
    salleLabel.textContent = 'Salle';
    salleLabel.htmlFor = `salle-${blockId}`;
    salleGroup.appendChild(salleLabel);
    
    const salleSelect = document.createElement('select');
    salleSelect.id = `salle-${blockId}`;
    salleSelect.dataset.blockId = blockId;
    
    const sallePlaceholder = document.createElement('option');
    sallePlaceholder.value = '';
    sallePlaceholder.textContent = 'Sélectionnez la salle';
    salleSelect.appendChild(sallePlaceholder);
    
    salleSelect.onchange = function() {
        updateMatieres(this.dataset.blockId);
    };
    
    salleGroup.appendChild(salleSelect);
    row1.appendChild(salleGroup);
    
    subjectBlock.appendChild(row1);
    
    // Ligne 2: Matière
    const row2 = document.createElement('div');
    row2.className = 'form-row';
    
    const matiereGroup = document.createElement('div');
    matiereGroup.className = 'form-group';
    
    const matiereLabel = document.createElement('label');
    matiereLabel.textContent = 'Matière';
    matiereLabel.htmlFor = `matiere-${blockId}`;
    matiereGroup.appendChild(matiereLabel);
    
    const matiereSelect = document.createElement('select');
    matiereSelect.id = `matiere-${blockId}`;
    
    const matierePlaceholder = document.createElement('option');
    matierePlaceholder.value = '';
    matierePlaceholder.textContent = 'Sélectionnez d\'abord le niveau et la salle';
    matiereSelect.appendChild(matierePlaceholder);
    
    matiereGroup.appendChild(matiereSelect);
    row2.appendChild(matiereGroup);
    
    subjectBlock.appendChild(row2);
    
    // Note: Les champs jour et horaire ont été supprimés
    
    // Ligne 3: Notes complémentaires
    const row3 = document.createElement('div');
    row3.className = 'form-row';
    
    const notesGroup = document.createElement('div');
    notesGroup.className = 'form-group';
    
    const notesLabel = document.createElement('label');
    notesLabel.textContent = 'Notes complémentaires';
    notesLabel.htmlFor = `notes-${blockId}`;
    notesGroup.appendChild(notesLabel);
    
    const notesInput = document.createElement('input');
    notesInput.type = 'text';
    notesInput.id = `notes-${blockId}`;
    notesInput.placeholder = 'Ajoutez des informations supplémentaires si nécessaire';
    
    notesGroup.appendChild(notesInput);
    row3.appendChild(notesGroup);
    
    subjectBlock.appendChild(row3);
    
    // Ajouter des informations uniques pour chaque bloc
    const infoText = document.createElement('p');
    infoText.className = 'info-text';
    infoText.textContent = `Identifiant unique du bloc: #${blockId} - Créé le ${new Date().toLocaleString()}`;
    subjectBlock.appendChild(infoText);
    return subjectBlock;
}

// Fonction pour mettre à jour les salles disponibles en fonction du niveau sélectionné
function updateSallesAndMatieres(blockId) {
    const niveauSelect = document.getElementById(`niveau-${blockId}`);
    const salleSelect = document.getElementById(`salle-${blockId}`);
    const matiereSelect = document.getElementById(`matiere-${blockId}`);
    
    // Réinitialiser la sélection de salle
    salleSelect.innerHTML = '<option value="">Sélectionnez la salle</option>';
    
    // Réinitialiser la sélection de matière
    matiereSelect.innerHTML = '<option value="">Sélectionnez d\'abord la salle</option>';
    
    const niveauValue = niveauSelect.value;
    
    if (niveauValue && matieresByNiveau[niveauValue]) {
        // Ajouter les salles disponibles pour ce niveau
        Object.keys(matieresByNiveau[niveauValue]).forEach(salle => {
            const option = document.createElement('option');
            option.value = salle;
            option.textContent = `Salle ${salle}`;
            salleSelect.appendChild(option);
        });
    }
}

// Fonction pour mettre à jour les matières disponibles en fonction du niveau et de la salle
function updateMatieres(blockId) {
    const niveauSelect = document.getElementById(`niveau-${blockId}`);
    const salleSelect = document.getElementById(`salle-${blockId}`);
    const matiereSelect = document.getElementById(`matiere-${blockId}`);
    
    // Réinitialiser la sélection de matière
    matiereSelect.innerHTML = '<option value="">Sélectionnez la matière</option>';
    
    const niveauValue = niveauSelect.value;
    const salleValue = salleSelect.value;
    
    if (niveauValue && salleValue && matieresByNiveau[niveauValue] && matieresByNiveau[niveauValue][salleValue]) {
        // Ajouter les matières disponibles pour ce niveau et cette salle
        matieresByNiveau[niveauValue][salleValue].forEach(matiere => {
            const option = document.createElement('option');
            option.value = matiere.toLowerCase().replace(/\s+/g, '-');
            option.textContent = matiere;
            matiereSelect.appendChild(option);
        });
    }
}

// Fonction pour valider les données d'un bloc
function validateBlock(blockId) {
    const niveau = document.getElementById(`niveau-${blockId}`).value;
    const salle = document.getElementById(`salle-${blockId}`).value;
    const matiere = document.getElementById(`matiere-${blockId}`).value;
    
    if (!niveau || !salle || !matiere) {
        return false;
    }
    
    return true;
}

// Fonction pour collecter les données de tous les blocs
function collectAllData() {
    const blocks = document.querySelectorAll('.subject-block');
    const data = [];
    let isValid = true;
    
    blocks.forEach(block => {
        const blockId = block.id;
        
        if (!validateBlock(blockId)) {
            isValid = false;
            alert(`Veuillez remplir tous les champs requis pour la ${block.querySelector('.block-title').textContent}`);
            return;
        }
        
        const niveau = document.getElementById(`niveau-${blockId}`).value;
        const salle = document.getElementById(`salle-${blockId}`).value;
        const matiereSelect = document.getElementById(`matiere-${blockId}`);
        const matiere = matiereSelect.options[matiereSelect.selectedIndex].textContent;
        const notes = document.getElementById(`notes-${blockId}`).value;
        
        data.push({
            id: blockId,
            niveau,
            salle,
            matiere,
            notes
        });
    });
    
    return isValid ? data : null;
}

function enregistrerNotes() {
    const data = collectAllData();
    
    if (!data) {
        return; // La validation a échoué
    }
    
    // Afficher un indicateur de chargement
    const saveButton = document.getElementById('save-all');
    const originalText = saveButton.textContent;
    saveButton.textContent = 'Enregistrement en cours...';
    saveButton.disabled = true;
    
    // Envoyer les données au serveur via AJAX
    fetch('addTeacherSubject.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau: ' + response.statusText);
        }
        return response.json();
    })
    .then(result => {
        // Traitement de la réponse du serveur
        if (result.success) {
            // Mise à jour du récapitulatif
            
            // Afficher un message de succès
            const messageElement = document.createElement('div');
            messageElement.className = 'success';
            messageElement.style.padding = '15px';
            messageElement.style.margin = '20px 0';
            messageElement.style.backgroundColor = '#d4edda';
            messageElement.style.borderColor = '#c3e6cb';
            messageElement.style.color = '#155724';
            messageElement.style.borderRadius = '4px';
            messageElement.textContent = result.message;
            
            // Insérer le message avant le récapitulatif
            const summaryElement = document.getElementById('summary');
            summaryElement.parentNode.insertBefore(messageElement, summaryElement);
            
            // Faire défiler jusqu'au message
            messageElement.scrollIntoView({ behavior: 'smooth' });
            
            // Supprimer le message après quelques secondes
            setTimeout(() => {
                messageElement.style.opacity = '0';
                messageElement.style.transition = 'opacity 1s';
                setTimeout(() => messageElement.remove(), 1000);
                window.location.reload();
            }, 5000);
        } else {
            // Afficher les erreurs
            let errorMessage = 'Des erreurs sont survenues lors de l\'enregistrement:';
            if (result.errors && result.errors.length > 0) {
                errorMessage += '<ul>';
                result.errors.forEach(error => {
                    errorMessage += `<li>${error}</li>`;
                });
                errorMessage += '</ul>';
            } else {
                errorMessage += ' ' + (result.message || 'Erreur inconnue');
            }
            
            alert(errorMessage.replace(/<[^>]*>/g, ''));
        }
    })
    .catch(error => {
        // Gestion des erreurs lors de la requête
        console.error('Erreur:', error);
        alert('Erreur lors de l\'enregistrement: ' + error.message);
    })
    .finally(() => {
        // Réactiver le bouton d'enregistrement
        saveButton.textContent = originalText;
        saveButton.disabled = false;
    });
}        
// Initialiser le premier bloc de matière
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter le premier bloc
    const subjectBlocks = document.getElementById('subject-blocks');
    subjectBlocks.appendChild(createSubjectBlock());
    
    // Configurer le bouton d'ajout
    document.getElementById('add-subject').addEventListener('click', function() {
        subjectBlocks.appendChild(createSubjectBlock());
        const container = subjectBlocks.parentNode.parentNode.parentNode;
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    });
    
    // Code JavaScript à ajouter dans votre page HTML pour envoyer les données en AJAX
    // Configurer le bouton d'enregistrement
    document.getElementById('save-all').addEventListener('click', function() {
        enregistrerNotes();
    });
});