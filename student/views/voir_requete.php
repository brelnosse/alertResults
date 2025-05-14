<?php
/**
 * Détails d'une requête
 * 
 * Cette page affiche les détails d'une requête spécifique et permet
 * à l'étudiant de voir les réponses et d'ajouter des commentaires.
 * 
 * @package     AlertResults
 * @subpackage  Views
 * @category    Requêtes
 * @author      v0
 * @version     1.0
 */

// Vérifier si l'utilisateur est connecté et est un étudiant
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ../index.php');
    exit;
}

// Vérifier si l'ID de la requête est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?page=requetes');
    exit;
}

$requeteId = intval($_GET['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la requête - AlertResults</title>
    <link rel="stylesheet" href="../../shared/assets/css/common.css">
    <link rel="stylesheet" href="../../shared/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .requete-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .requete-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .requete-title h3 {
            margin: 0 0 5px;
            color: #333;
        }
        
        .requete-meta {
            color: #666;
            font-size: 14px;
        }
        
        .requete-meta span {
            margin-right: 15px;
        }
        
        .requete-status {
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
        
        .requete-content {
            margin-bottom: 20px;
        }
        
        .requete-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
            font-weight: 400;
        }
        
        .requete-description {
            line-height: 1.6;
            color: #333;
        }
        
        .reponses-container {
            margin-top: 30px;
        }
        
        .reponses-title {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .reponse-item {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
            position: relative;
        }
        
        .reponse-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .reponse-author {
            font-weight: 500;
            color: #333;
        }
        
        .reponse-date {
            color: #666;
            font-size: 12px;
        }
        
        .reponse-content {
            line-height: 1.5;
            color: #333;
        }
        
        .reponse-form {
            margin-top: 30px;
        }
        
        .reponse-form h4 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #004080;
            outline: none;
        }
        
        .form-control.error {
            border-color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background-color: #004080;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #002d5a;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-secondary:hover {
            background-color: #e9ecef;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .user-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 5px;
            color: white;
        }
        
        .badge-student {
            background-color: #5bc0de;
        }
        
        .badge-teacher {
            background-color: #5cb85c;
        }
        
        .badge-admin {
            background-color: #d9534f;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        @media (max-width: 768px) {
            .requete-header {
                flex-direction: column;
            }
            
            .requete-status {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> AlertResults</h1>
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
                <li class="active"><a href="index.php?page=requetes"><i class="fas fa-clipboard-list"></i> Mes requêtes</a></li>
                <li><a href="index.php?page=notes"><i class="fas fa-chart-bar"></i> Mes notes</a></li>
                <li><a href="index.php?page=profile"><i class="fas fa-user"></i> Mon profil</a></li>
            </ul>
        </nav>
        
        <main>
            <div class="back-link">
                <a href="index.php?page=requetes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>
            
            <?php if (!$requete): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> La requête demandée n'existe pas ou a été supprimée.
                </div>
            <?php else: ?>
                <div class="requete-container">
                    <div class="requete-header">
                        <div class="requete-title">
                            <h3><?php echo htmlspecialchars($requete['sujet']); ?></h3>
                            <div class="requete-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($requete['date_creation'])); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($requete['student_firstname'] . ' ' . $requete['student_lastname']); ?></span>
                            </div>
                        </div>
                        <div>
                            <span class="requete-status status-<?php echo $requete['statut']; ?>">
                                <?php 
                                $statuts = [
                                    'en_attente' => 'En attente de validation',
                                    'approuvee' => 'Approuvée - En attente de réponse',
                                    'rejetee' => 'Rejetée par le département',
                                    'resolue' => 'Résolue'
                                ];
                                echo $statuts[$requete['statut']] ?? $requete['statut'];
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="requete-content">
                        <div class="requete-details">
                            <div class="detail-item">
                                <div class="detail-label">Matière</div>
                                <div class="detail-value"><?php echo htmlspecialchars($requete['matiere_nom']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Enseignant</div>
                                <div class="detail-value"><?php echo htmlspecialchars($requete['teacher_firstname'] . ' ' . $requete['teacher_lastname']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Département</div>
                                <div class="detail-value"><?php echo htmlspecialchars($requete['department_nom']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Type</div>
                                <div class="detail-value">
                                    <?php 
                                    $types = [
                                        'erreur_note' => 'Erreur de note',
                                        'erreur_absence' => 'Erreur d\'absence',
                                        'autre' => 'Autre'
                                    ];
                                    echo $types[$requete['type']] ?? $requete['type'];
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="requete-description">
                            <?php echo nl2br(htmlspecialchars($requete['description'])); ?>
                        </div>
                    </div>
                    
                    <?php if ($requete['statut'] === 'rejetee'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Cette requête a été rejetée par le chef de département et ne sera pas transmise à l'enseignant.
                        </div>
                    <?php elseif ($requete['statut'] === 'en_attente'): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Cette requête est en attente de validation par le chef de département.
                        </div>
                    <?php endif; ?>
                    
                    <div class="reponses-container">
                        <h3 class="reponses-title"><i class="fas fa-comments"></i> Réponses et commentaires</h3>
                        
                        <?php if (empty($reponses)): ?>
                            <p>Aucune réponse pour le moment.</p>
                        <?php else: ?>
                            <?php foreach ($reponses as $reponse): ?>
                                <div class="reponse-item">
                                    <div class="reponse-header">
                                        <div class="reponse-author">
                                            <?php echo htmlspecialchars($reponse['firstname'] . ' ' . $reponse['lastname']); ?>
                                            <span class="user-badge badge-<?php echo $reponse['user_type']; ?>">
                                                <?php 
                                                $userTypes = [
                                                    'student' => 'Étudiant',
                                                    'teacher' => 'Enseignant',
                                                    'admin' => 'Admin'
                                                ];
                                                echo $userTypes[$reponse['user_type']] ?? $reponse['user_type'];
                                                ?>
                                            </span>
                                        </div>
                                        <div class="reponse-date">
                                            <?php echo date('d/m/Y H:i', strtotime($reponse['date_creation'])); ?>
                                        </div>
                                    </div>
                                    <div class="reponse-content">
                                        <?php echo nl2br(htmlspecialchars($reponse['contenu'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if ($requete['statut'] !== 'rejetee'): ?>
                            <div class="reponse-form">
                                <h4>Ajouter un commentaire</h4>
                                <form action="index.php?page=voir_requete&id=<?php echo $requeteId; ?>" method="post">
                                    <div class="form-group">
                                        <textarea name="reponse" class="form-control <?php echo $errorHandler->hasError('reponse') ? 'error' : ''; ?>" placeholder="Votre commentaire..."><?php echo htmlspecialchars($_POST['reponse'] ?? ''); ?></textarea>
                                        <?php if ($errorHandler->hasError('reponse')): ?>
                                            <div class="error-message"><?php echo $errorHandler->getError('reponse'); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> AlertResults. Tous droits réservés.</p>
        </footer>
    </div>
    
    <script src="../../shared/assets/js/common.js"></script>
</body>
</html>
