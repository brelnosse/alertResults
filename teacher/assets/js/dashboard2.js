document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    document.querySelector('.toggle-menu').addEventListener('click', function() {
        document.body.classList.toggle('expanded');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideNavbar = document.querySelector('.navbar').contains(event.target);
        const isClickOnToggleButton = document.querySelector('.toggle-menu').contains(event.target);
        
        if (!isClickInsideNavbar && !isClickOnToggleButton && window.innerWidth <= 767 && document.body.classList.contains('expanded')) {
            document.body.classList.remove('expanded');
        }
    });
    userId = document.querySelector('#user_id').value;
    const addSubjectButton = document.querySelector('#add-subject-buttton');
    const xhr = new XMLHttpRequest();
    let matieres = []    
    xhr.open('GET', 'getTeacherMatiere.php?id_enseignant='+userId, false);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            matieres = JSON.parse(xhr.responseText);
        }
    };
    xhr.send(null);
    // Données d'exemple
    // Éléments DOM
    const tableBody = document.querySelector('#matieresTable tbody');
    const searchInput = document.getElementById('searchInput');
    const addBtn = document.getElementById('addBtn');
    const saveBtn = document.getElementById('saveBtn');
    const selectAll = document.getElementById('selectAll');
    const closeBtn = document.querySelector('.close-btn');
    const popup = document.querySelector('.popup');
    const addSubjectContainer = document.querySelector('#add-subject-container');
    const controls = document.querySelector('.controls');
    const tableContainer = document.querySelector('.table-container');

    addSubjectButton.addEventListener('click', function() {
        popup.style.display = 'block';
    });
    // Charger les données dans le tableau
    function loadTableData(data) {
        tableBody.innerHTML = '';
        if (data.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `<td colspan="7" style="text-align: center;">Aucune matière trouvée</td>`;
            tableBody.appendChild(emptyRow);
            return;
        }

        data.forEach(item => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', item.id);
            row.innerHTML = `
                <td>
                    <div class="checkbox-wrapper">
                        <input type="checkbox" class="row-checkbox custom-checkbox">
                    </div>
                </td>
                <td>${item.libelle}</td>
                <td>${item.code}</td>
                <td>${item.credit}</td>
                <td>${item.niveau}</td>
                <td>${item.salle}</td>
                <td>
                    <button class="delete-btn">Supprimer</button>
                </td>
                <td>
                    (${item.status === 'approved' ? '<i style="color: green">Approved</i>' : 
                      item.status === 'pending' ? '<i style="color: orange">Pending</i>' : 
                      '<i style="color: red">Rejected</i>'})
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Ajouter des événements pour les boutons de suppression
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const id = parseInt(row.getAttribute('data-id'));
                deleteMatiere(id);
            });
        });
    }

    // Recherche dans le tableau
    function searchTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredData = matieres.filter(item => {
            return (
                item.libelle.toLowerCase().includes(searchTerm) ||
                item.code.toLowerCase().includes(searchTerm) ||
                item.niveau.toLowerCase().includes(searchTerm) ||
                item.salle.toLowerCase().includes(searchTerm)
            );
        });
        loadTableData(filteredData);
    }

    // Suppression d'une matière
    function deleteMatiere(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette matière?')) {
            const index = matieres.findIndex(item => item.id === id);
            let mattodelete = matieres[index];
            if (index !== -1) {
                loadTableData(matieres);
            }
            const formData = new FormData();
            formData.append('id_enseignant', userId);
            formData.append('libelle', mattodelete.libelle);
            formData.append('credit', mattodelete.credit);
            formData.append('niveau', mattodelete.niveau);
            formData.append('classe', mattodelete.salle);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'deleteTeacherMatiere.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    xhr.open('GET', 'getTeacherMatiere.php?id_enseignant='+userId, false);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            matieres = JSON.parse(xhr.responseText);
                            loadTableData(matieres);

                        }
                    };
                    xhr.send(null);
                }
            };
            xhr.send(formData);
        }
    }

    // Ajouter une nouvelle matière
    // function addMatiere(matiere) {
    //     // Générer un nouvel ID (simple pour cet exemple)
    //     const newId = matieres.length > 0 ? Math.max(...matieres.map(m => m.id)) + 1 : 1;
    //     const newMatiere = { id: newId, ...matiere };
    //     matieres.push(newMatiere);
    //     loadTableData(matieres);
    // }

    // Réinitialiser le formulaire
    // function resetForm() {
    //     document.getElementById('libelle').value = '';
    //     document.getElementById('code').value = '';
    //     document.getElementById('credit').value = '';
    //     document.getElementById('niveau').value = '';
    //     document.getElementById('classe').value = '';
    //     addSubjectContainer.style.display = 'none';
    // }

    // Fermer le popup
    closeBtn.addEventListener('click', function() {
        if(controls.style.display === 'none') {
            controls.style.display = 'block';
            tableContainer.style.display = 'block';
            addSubjectContainer.style.display = 'none';
        }else{
            popup.style.display = 'none';
            controls.style.display = 'block';
            tableContainer.style.display = 'block';
            addSubjectContainer.style.display = 'none';
        }
    });

    // Écouteurs d'événements
    searchInput.addEventListener('input', searchTable);

    addBtn.addEventListener('click', function() {
        addSubjectContainer.style.display = 'block';
        controls.style.display = 'none';
        tableContainer.style.display = 'none';
    });

    // Sélectionner/déselectionner toutes les lignes
    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Initialiser le tableau
    loadTableData(matieres);
});
// alert(addSubjectButton);