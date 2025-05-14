<?php
/**
 * Liste des requêtes en attente pour un chef de département
 * 
 * Cette page affiche la liste des requêtes en attente de validation
 * pour le département du chef connecté.
 * 
 * @package     AlertResults
 * @subpackage  Views
 * @category    Requêtes
 * @author      v0
 * @version     1.0
 */

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requêtes en attente - AlertResults</title>
    <link rel="stylesheet" href="../../shared/assets/css/common.css">
    <link rel="stylesheet" href="../../shared/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .requetes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }
        
        .badge-erreur_note {
            background-color: #dc3545;
        }
        
        .badge-erreur_absence {
            background-color: #fd7e14;
        }
        
        .badge-autre {
            background-color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .requetes-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-user-shield"></i> AlertResults - Administration</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
                <a href="../controllers/logout_controller.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="index.php?page=departments"><i class="fas fa-building"></i> Départements</a></li>
                <li><a href="index.php?page=specialities"><i class="fas fa-graduation-cap"></i> Spécialités</a></li>
                <li><a href="index.php?page=student_validation"><i class="fas fa-user-check"></i> Validation étudiants</a></li>
                <li><a href="index.php?page=teacher_validation"><i class="fas fa-chalkboard-teacher"></i> Validation enseignants</a></li>
                <li class="active"><a href="index.php?page=requetes_admin"><i class="fas fa-clipboard-list"></i> Requêtes</a></li>
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
            
            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="requetes-header">
                <h2><i class="fas fa-clipboard-list"></i> Requêtes en attente de validation</h2>
            </div>
            
            <?php if (empty($requetes)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-check"></i>
                    <h3>Aucune requête en attente</h3>
                    <p>Il n'y a actuellement aucune requête en attente de validation pour votre département.</p>
                </div>
            <?php else: ?>
                <table class="requetes-table">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Sujet</th>
                            <th>Matière</th>
                            <th>Enseignant</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requetes as $requete): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($requete['student_firstname'] . ' ' . $requete['student_lastname']); ?></td>
                                <td><?php echo htmlspecialchars($requete['sujet']); ?></td>
                                <td><?php echo htmlspecialchars($requete['matiere_nom']); ?></td>
                                <td><?php echo htmlspecialchars($requete['teacher_firstname'] . ' ' . $requete['teacher_lastname']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $requete['type']; ?>">
                                        <?php 
                                        $types = [
                                            'erreur_note' => 'Erreur de note',
                                            'erreur_absence' => 'Erreur d\'absence',
                                            'autre' => 'Autre'
                                        ];
                                        echo $types[$requete['type']] ?? $requete['type'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($requete['date_creation'])); ?></td>
                                <td>
                                    <a href="index.php?page=voir_requete_admin&id=<?php echo $requete['id']; ?>" class="view-btn">
                                        <i class="fas fa-eye"></i> Examiner
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
</body>
</html>
