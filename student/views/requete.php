<?php
require_once '../init_std.php';
require_once __DIR__ . '/../../shared/models/requete_model.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes requêtes - AlertResults</title>
    <!-- <link rel="stylesheet" href="../../shared/assets/css/common.css"> -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .requetes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .new-request-btn {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .new-request-btn:hover {
            background-color: #002d5a;
        }
        
        .new-request-btn i {
            margin-right: 8px;
        }
        
        .filter-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background-color: #004080;
            color: white;
            border-color: #004080;
        }
        
        .requetes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .requetes-table th, 
        .requetes-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .requetes-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .requetes-table tr:last-child td {
            border-bottom: none;
        }
        
        .requetes-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: white;
            display: inline-block;
        }
        
        .status-en_attente {
            background-color: #f0ad4e;
        }
        
        .status-approuvee {
            background-color: #5bc0de;
        }
        
        .status-rejetee {
            background-color: #d9534f;
        }
        
        .status-resolue {
            background-color: #5cb85c;
        }
        
        .view-btn {
            padding: 5px 10px;
            background-color: #004080;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            transition: background-color 0.3s;
        }
        
        .view-btn:hover {
            background-color: #002d5a;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
        }
        
        .empty-state h3 {
            margin-top: 0;
            color: #333;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .requetes-table {
                display: block;
                overflow-x: auto;
            }
            
            .filter-container {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 10px;
            }
            
            .requetes-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .new-request-btn {
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> AlertResults</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../controllers/logout_controller.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="active"><a href="requete.php"><i class="fas fa-clipboard-list"></i> Mes requêtes</a></li>
                <li><a href="index.php?page=notes"><i class="fas fa-chart-bar"></i> Mes notes</a></li>
                <li><a href="index.php?page=profile"><i class="fas fa-user"></i> Mon profil</a></li>
            </ul>
        </nav>
        
        <main>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="requetes-header">
                <h2><i class="fas fa-clipboard-list"></i> Mes requêtes</h2>
                <a href="nouvelle_requete.php" class="new-request-btn">
                    <i class="fas fa-plus"></i> Nouvelle requête
                </a>
            </div>
            
            <div class="filter-container">
                <button class="filter-btn active" data-filter="all">Toutes (<?php echo $counts['total']; ?>)</button>
                <button class="filter-btn" data-filter="en_attente">En attente (<?php echo $counts['en_attente']; ?>)</button>
                <button class="filter-btn" data-filter="approuvee">Approuvées (<?php echo $counts['approuvee']; ?>)</button>
                <button class="filter-btn" data-filter="rejetee">Rejetées (<?php echo $counts['rejetee']; ?>)</button>
                <button class="filter-btn" data-filter="resolue">Résolues (<?php echo $counts['resolue']; ?>)</button>
            </div>
            
            <?php if (empty($requetes)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard"></i>
                    <h3>Aucune requête trouvée</h3>
                    <p>Vous n'avez pas encore soumis de requêtes. Cliquez sur le bouton ci-dessous pour en créer une.</p>
                    <a href="nouvelle_requete.php" class="new-request-btn">
                        <i class="fas fa-plus"></i> Nouvelle requête
                    </a>
                </div>
            <?php else: ?>
                <table class="requetes-table">
                    <thead>
                        <tr>
                            <th>Sujet</th>
                            <th>Matière</th>
                            <th>Enseignant</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requetes as $requete): ?>
                            <tr class="requete-row" data-status="<?php echo $requete['statut']; ?>">
                                <td><?php echo htmlspecialchars($requete['sujet']); ?></td>
                                <td><?php echo htmlspecialchars($requete['matiere_nom']); ?></td>
                                <td><?php echo htmlspecialchars($requete['teacher_firstname'] . ' ' . $requete['teacher_lastname']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($requete['date_creation'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $requete['statut']; ?>">
                                        <?php 
                                        $statuts = [
                                            'en_attente' => 'En attente',
                                            'approuvee' => 'Approuvée',
                                            'rejetee' => 'Rejetée',
                                            'resolue' => 'Résolue'
                                        ];
                                        echo $statuts[$requete['statut']] ?? $requete['statut'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="voir_requete.php?id=<?php echo $requete['id']; ?>" class="view-btn">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> AlertResults. Tous droits réservés.</p>
        </footer>
    </div>
    
    <script src="../../shared/assets/js/common.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtrage des requêtes
            const filterButtons = document.querySelectorAll('.filter-btn');
            const requeteRows = document.querySelectorAll('.requete-row');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Retirer la classe active de tous les boutons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
                    
                    // Récupérer le filtre
                    const filter = this.getAttribute('data-filter');
                    
                    // Filtrer les lignes
                    requeteRows.forEach(row => {
                        if (filter === 'all' || row.getAttribute('data-status') === filter) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
