<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Classes | Système Académique</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --sidebar-width: 250px;
            --header-height: 60px;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar style */
        .navbar {
            background-color: var(--primary-color);
            color: white;
            position: fixed;
            width: var(--sidebar-width);
            height: 100vh;
            left: 0;
            top: 0;
            box-shadow: var(--shadow);
            z-index: 100;
            transition: var(--transition);
            overflow-y: auto;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            height: var(--header-height);
            background-color: var(--primary-dark);
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .navbar-user {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: var(--shadow);
        }

        .user-name {
            margin-top: 10px;
            font-weight: 600;
            color: white;
            font-size: 1rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .badge {
            position: absolute;
            right: 20px;
            top: 12px;
            background-color: var(--accent-color);
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main content style */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            transition: var(--transition);
        }

        /* Header bar */
        .header {
            height: var(--header-height);
            background-color: white;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            padding: 0 20px;
            justify-content: space-between;
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
        }

        .header-actions .btn {
            margin-left: 10px;
        }

        /* Toggle Menu button */
        .toggle-menu {
            display: none;
            background: none;
            border: none;
            color: var(--dark-color);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Classes content */
        .classes-container {
            padding: 20px;
        }

        /* Filter tabs */
        .filter-tabs {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            padding: 15px;
        }

        .filter-section {
            margin-bottom: 15px;
        }

        .filter-section:last-child {
            margin-bottom: 0;
        }

        .filter-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }

        .filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            background-color: #f5f7fa;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .filter-btn:hover {
            background-color: #e0e0e0;
        }

        .filter-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Student table */
        .student-table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 20px;
            overflow-x: auto;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th,
        .student-table td {
            padding: 12px 15px;
            text-align: left;
        }

        .student-table th {
            background-color: #f5f7fa;
            font-weight: 600;
            color: var(--dark-color);
        }

        .student-table tr {
            border-bottom: 1px solid #eee;
        }

        .student-table tr:last-child {
            border-bottom: none;
        }

        .student-table tr:hover {
            background-color: #f5f7fa;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-right: 5px;
            cursor: pointer;
        }

        .view-btn {
            background-color: #3498db;
            color: white;
            border: none;
        }

        .view-btn:hover {
            background-color: #2980b9;
        }

        .message-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
        }

        .message-btn:hover {
            background-color: #27ae60;
        }

        /* Responsive styles */
        @media (max-width: 991px) {
            .navbar {
                width: 70px;
                overflow: visible;
            }

            .navbar-brand, .user-name, .user-role, .nav-link span {
                display: none;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
            }

            .nav-link i {
                margin-right: 0;
                font-size: 1.5rem;
            }

            .nav-link {
                justify-content: center;
                padding: 12px 0;
            }

            .main-content {
                margin-left: 70px;
            }

            .toggle-menu {
                display: block;
            }

            body.expanded .navbar {
                width: var(--sidebar-width);
            }

            body.expanded .navbar-brand, 
            body.expanded .user-name, 
            body.expanded .user-role, 
            body.expanded .nav-link span {
                display: block;
            }

            body.expanded .nav-link {
                justify-content: flex-start;
                padding: 12px 25px;
            }

            body.expanded .nav-link i {
                margin-right: 10px;
            }
        }

        @media (max-width: 767px) {
            .navbar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .navbar-brand, .user-name, .user-role, .nav-link span {
                display: block;
            }

            .nav-link {
                justify-content: flex-start;
                padding: 12px 25px;
            }

            .nav-link i {
                margin-right: 10px;
            }

            .main-content {
                margin-left: 0;
            }

            body.expanded .navbar {
                transform: translateX(0);
            }

            .filter-options {
                flex-direction: column;
                gap: 5px;
            }

            .filter-btn {
                width: 100%;
                text-align: center;
            }
        }

        /* Button styles */
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Search and pagination */
        .table-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-container {
            position: relative;
            max-width: 300px;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .page-item {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .page-item:hover {
            background-color: #f5f7fa;
        }

        .page-item.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">SGA</div>
        
        <div class="navbar-user">
            <img src="/api/placeholder/100/100" alt="Photo de profil" class="user-avatar">
            <div class="user-name">M. Dubois</div>
            <div class="user-role">Administrateur</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Accueil</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Étudiants</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistiques</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <span class="badge">5</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <header class="header">
            <button class="toggle-menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-title">Gestion des étudiants</div>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="fas fa-bell"></i>
                </button>
            </div>
        </header>

        <div class="classes-container">
            <div class="filter-tabs">
                <div class="filter-section">
                    <div class="filter-title">Cycle</div>
                    <div class="filter-options">
                        <button class="filter-btn active" data-filter="cycle" data-value="prepa">Prépa</button>
                        <button class="filter-btn" data-filter="cycle" data-value="bts">BTS</button>
                        <button class="filter-btn" data-filter="cycle" data-value="ti">TI</button>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-title">Niveau</div>
                    <div class="filter-options">
                        <button class="filter-btn active" data-filter="niveau" data-value="prepa-3il-1">Prépa 3IL 1</button>
                        <button class="filter-btn" data-filter="niveau" data-value="prepa-3il-2">Prépa 3IL 2</button>
                        <button class="filter-btn" data-filter="niveau" data-value="ti-1">TI 1</button>
                    </div>
                </div>

                <div class="filter-section">
                    <div class="filter-title">Classe</div>
                    <div class="filter-options">
                        <button class="filter-btn active" data-filter="classe" data-value="A">A</button>
                        <button class="filter-btn" data-filter="classe" data-value="B">B</button>
                        <button class="filter-btn" data-filter="classe" data-value="C">C</button>
                        <button class="filter-btn" data-filter="classe" data-value="D">D</button>
                    </div>
                </div>
            </div>

            <div class="student-table-container">
                <div class="table-controls">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Rechercher un étudiant...">
                    </div>
                    
                    <div class="pagination">
                        <div class="page-item disabled">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="page-item active">1</div>
                        <div class="page-item">2</div>
                        <div class="page-item">3</div>
                        <div class="page-item">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>

                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-list">
                        <!-- Les étudiants seront chargés dynamiquement par JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Données des étudiants (simule une base de données)
        const students = [
            { id: 1, nom: "Martin", prenom: "Thomas", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 2, nom: "Dubois", prenom: "Emma", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 3, nom: "Richard", prenom: "Lucas", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 4, nom: "Petit", prenom: "Léa", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 5, nom: "Robert", prenom: "Hugo", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 6, nom: "Simon", prenom: "Chloé", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 7, nom: "Laurent", prenom: "Nathan", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 8, nom: "Michel", prenom: "Camille", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 9, nom: "Lefebvre", prenom: "Jules", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 10, nom: "Garcia", prenom: "Sarah", cycle: "prepa", niveau: "prepa-3il-1", classe: "A" },
            { id: 11, nom: "Roux", prenom: "Mathis", cycle: "prepa", niveau: "prepa-3il-1", classe: "B" },
            { id: 12, nom: "Bonnet", prenom: "Louise", cycle: "prepa", niveau: "prepa-3il-1", classe: "B" },
            { id: 13, nom: "Lambert", prenom: "Gabriel", cycle: "prepa", niveau: "prepa-3il-2", classe: "A" },
            { id: 14, nom: "Girard", prenom: "Inès", cycle: "prepa", niveau: "prepa-3il-2", classe: "A" },
            { id: 15, nom: "Vincent", prenom: "Raphaël", cycle: "prepa", niveau: "prepa-3il-2", classe: "B" },
            { id: 16, nom: "Dupont", prenom: "Jade", cycle: "bts", niveau: "ti-1", classe: "A" },
            { id: 17, nom: "Leroy", prenom: "Ethan", cycle: "bts", niveau: "ti-1", classe: "A" },
            { id: 18, nom: "Mercier", prenom: "Alice", cycle: "bts", niveau: "ti-1", classe: "B" },
            { id: 19, nom: "Boyer", prenom: "Arthur", cycle: "ti", niveau: "ti-1", classe: "C" },
            { id: 20, nom: "Faure", prenom: "Lina", cycle: "ti", niveau: "ti-1", classe: "D" }
        ];

        // Variables pour le filtrage
        let currentFilters = {
            cycle: "prepa",
            niveau: "prepa-3il-1",
            classe: "A"
        };

        // Afficher les étudiants selon les filtres
        function renderStudents() {
            const filteredStudents = students.filter(student => 
                student.cycle === currentFilters.cycle && 
                student.niveau === currentFilters.niveau && 
                student.classe === currentFilters.classe
            );

            const tableBody = document.getElementById('students-list');
            tableBody.innerHTML = '';

            if (filteredStudents.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `<td colspan="4" style="text-align: center; padding: 20px;">Aucun étudiant trouvé</td>`;
                tableBody.appendChild(row);
                return;
            }

            filteredStudents.forEach(student => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${student.id}</td>
                    <td>${student.nom}</td>
                    <td>${student.prenom}</td>
                    <td>
                        <button class="action-btn view-btn" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i> Voir
                        </button>
                        <button class="action-btn message-btn" onclick="messageStudent(${student.id})">
                            <i class="fas fa-envelope"></i> Message
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Gérer les clics sur les boutons de filtre
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                const filterType = this.dataset.filter;
                const filterValue = this.dataset.value;

                // Mettre à jour l'état actif du bouton
                document.querySelectorAll(`.filter-btn[data-filter="${filterType}"]`).forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                // Mettre à jour les filtres
                currentFilters[filterType] = filterValue;

                // Mettre à jour les niveaux disponibles en fonction du cycle
                if (filterType === 'cycle') {
                    updateNiveauFilters(filterValue);
                }

                // Afficher les étudiants filtrés
                renderStudents();
            });
        });

        // Mettre à jour les filtres de niveau en fonction du cycle sélectionné
        function updateNiveauFilters(cycle) {
            // Déterminer les niveaux disponibles pour ce cycle
            let niveaux = [];
            if (cycle === 'prepa') {
                niveaux = ['prepa-3il-1', 'prepa-3il-2'];
            } else if (cycle === 'bts' || cycle === 'ti') {
                niveaux = ['ti-1'];
            }

            // Mettre à jour l'affichage des boutons de niveau
            const niveauOptions = document.querySelector('.filter-options:nth-child(2)');
            niveauOptions.innerHTML = '';

            niveaux.forEach((niveau, index) => {
                const buttonText = niveau === 'prepa-3il-1' ? 'Prépa 3IL 1' : 
                                 niveau === 'prepa-3il-2' ? 'Prépa 3IL 2' : 'TI 1';
                
                const isActive = index === 0;
                niveauOptions.innerHTML += `
                    <button class="filter-btn ${isActive ? 'active' : ''}" 
                            data-filter="niveau" 
                            data-value="${niveau}">${buttonText}</button>
                `;
            });

            // Mettre à jour le filtre de niveau actuel
            currentFilters.niveau = niveaux[0];

            // Réattacher les événements de clic
            document.querySelectorAll('.filter-btn[data-filter="niveau"]').forEach(button => {
                button.addEventListener('click', function() {
                    const filterType = this.dataset.filter;
                    const filterValue = this.dataset.value;

                    // Mettre à jour l'état actif du bouton
                    document.querySelectorAll(`.filter-btn[data-filter="${filterType}"]`).forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');

                    // Mettre à jour les filtres
                    currentFilters[filterType] = filterValue;

                    // Afficher les étudiants filtrés
                    renderStudents();
                });
            });
        }

        // Fonctions pour les actions sur les étudiants
        function viewStudent(id) {
            // Récupérer l'étudiant par son ID
            const student = students.find(s => s.id === id);
            if (student) {
                alert(`Voir le profil de ${student.prenom} ${student.nom}`);
                // Dans une application réelle, cela pourrait ouvrir une modal ou rediriger vers une page de profil
            }
        }

        function messageStudent(id) {
            // Récupérer l'étudiant par son ID
            const student = students.find(s => s.id === id);
            if (student) {
                alert(`Envoyer un message à ${student.prenom} ${student.nom}`);
                // Dans une application réelle, cela pourrait ouvrir une interface de messagerie
            }
        }

        // Recherche d'étudiants
        document.querySelector('.search-input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Filtrer les étudiants par nom ou prénom qui contiennent le terme de recherche
            const tableBody = document.getElementById('students-list');
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const nom = row.cells[1]?.textContent.toLowerCase();
                const prenom = row.cells[2]?.textContent.toLowerCase();
                
                if (nom && prenom) {
                    if (nom.includes(searchTerm) || prenom.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });

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

        // Initialiser l'affichage des étudiants
        document.addEventListener('DOMContentLoaded', function() {
            renderStudents();
        });
    </script>
</body>
</html>