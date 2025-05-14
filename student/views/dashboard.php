<?php
require_once '../init_std.php';

// Inclure les modèles nécessaires
require_once __DIR__ . '/../../shared/models/requete_model.php';
require_once __DIR__ . '/../../shared/models/user_model.php';

// Récupérer les informations de l'étudiant
$studentId = $_SESSION['user_id'];
$studentDetails = getStudentDetailsByUserId($studentId);

// Récupérer les statistiques des requêtes
$requeteCounts = getRequeteCountsByStatusForStudent($studentId);

// Récupérer les dernières requêtes
$requetes = getRequetesByStudent($studentId);
$recentRequetes = array_slice($requetes, 0, 5); // Limiter aux 5 plus récentes
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - AlertResults</title>
    <!-- <link rel="stylesheet" href="../../shared/assets/css/common.css"> -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="requete.php"><i class="fas fa-clipboard-list"></i> Mes requêtes</a></li>
                <li><a href="voir_requete.php"><i class="fas fa-chart-bar"></i> Mes notes</a></li>
                <li><a href="profil.php"><i class="fas fa-user"></i> Mon profil</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="welcome-message">
                <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</h2>
                <p>Voici un aperçu de vos requêtes et des actions disponibles.</p>
            </div>
            
            <div class="student-info">
                <div class="student-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
                <div class="student-details">
                    <h2><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                    <p>
                        <?php 
                        if ($studentDetails) {
                            echo htmlspecialchars($studentDetails['cycle'] . ' - Niveau ' . $studentDetails['niveau'] . ' - ' . $studentDetails['specialite']);
                        }
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="stats-container">
                <div class="stat-box pending">
                    <h4>En attente</h4>
                    <p><?php echo $requeteCounts['en_attente']; ?></p>
                </div>
                <div class="stat-box approved">
                    <h4>Approuvées</h4>
                    <p><?php echo $requeteCounts['approuvee']; ?></p>
                </div>
                <div class="stat-box rejected">
                    <h4>Rejetées</h4>
                    <p><?php echo $requeteCounts['rejetee']; ?></p>
                </div>
                <div class="stat-box resolved">
                    <h4>Résolues</h4>
                    <p><?php echo $requeteCounts['resolue']; ?></p>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="nouvelle_requete.php" class="action-button">
                    <i class="fas fa-plus-circle"></i> Nouvelle requête
                </a>
                <a href="requete.php" class="action-button">
                    <i class="fas fa-list"></i> Voir toutes mes requêtes
                </a>
            </div>
            
            <div class="dashboard-container">
                <div class="dashboard-card">
                    <h3><i class="fas fa-clipboard-list"></i> Requêtes récentes</h3>
                    <?php if (empty($recentRequetes)): ?>
                        <p>Vous n'avez pas encore soumis de requêtes.</p>
                    <?php else: ?>
                        <ul class="request-list">
                            <?php foreach ($recentRequetes as $requete): ?>
                                <li class="request-item">
                                    <div>
                                        <div class="request-title">
                                            <a href="voir_requete.php?id=<?php echo $requete['id']; ?>">
                                                <?php echo htmlspecialchars($requete['sujet']); ?>
                                            </a>
                                        </div>
                                        <div class="request-date">
                                            <?php echo date('d/m/Y H:i', strtotime($requete['date_creation'])); ?>
                                        </div>
                                    </div>
                                    <span class="request-status status-<?php echo $requete['statut']; ?>">
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
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-card">
                    <h3><i class="fas fa-info-circle"></i> Informations</h3>
                    <p>Bienvenue dans votre espace étudiant. Vous pouvez soumettre des requêtes concernant des erreurs potentielles dans vos matières.</p>
                    <p>Chaque requête sera d'abord examinée par le chef de département avant d'être transmise à l'enseignant concerné.</p>
                    <p><strong>Processus de validation :</strong></p>
                    <ol>
                        <li>Soumission de la requête</li>
                        <li>Examen par le chef de département</li>
                        <li>Transmission à l'enseignant (si approuvée)</li>
                        <li>Réponse de l'enseignant</li>
                    </ol>
                </div>
            </div>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> AlertResults. Tous droits réservés.</p>
        </footer>
    </div>
    
    <script src="../../shared/assets/js/common.js"></script>
</body>
</html>
