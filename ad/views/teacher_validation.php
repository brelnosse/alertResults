<?php
require_once __DIR__ . '/../../shared/init.php';
require_once __DIR__ . '/../init_ad.php';
require_once __DIR__ . '/../controllers/teacher_validation_controller.php';

// Vérifier si l'utilisateur est connecté en tant qu'administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../shared/views/login_view.php');
    exit;
}

// Vérifier si l'administrateur a le droit d'accéder à cette page (chef de département ou directeur)
$adminRole = $_SESSION['admin_role'] ?? '';
if (!in_array($adminRole, ['chef', 'directeur'])) {
    header('Location: dashboard.php');
    exit;
}

// Récupérer le département de l'administrateur
$department = $_SESSION['admin_department'] ?? '';
// Récupérer les filtres
$statusFilter = $_GET['status'] ?? 'pending';
$searchTerm = $_GET['search'] ?? '';

// Récupérer la liste des enseignants selon les filtres
$teachers = getFilteredTeachers($department, $adminRole, $statusFilter, $searchTerm);
// var_dump($department, $adminRole, $statusFilter, $searchTerm); // Pour le débogage, à supprimer en production
// Traitement des actions de validation/rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if (isset($_POST['teacher_id']) && ($_POST['action'] === 'approve' || $_POST['action'] === 'reject')) {
            // Actions sur toutes les matières d'un enseignant
            $action = $_POST['action'];
            $teacherId = (int)$_POST['teacher_id'];
            
            if ($action === 'approve') {
                // Valider le compte enseignant
                if (approveTeacherAccount($teacherId, $_SESSION['user_id'])) {
                    setFlashMessage('success', 'Compte enseignant validé avec succès.');
                } else {
                    setFlashMessage('error', 'Erreur lors de la validation du compte enseignant.');
                }
            } elseif ($action === 'reject') {
                // Rejeter le compte enseignant avec une raison
                $rejectReason = $_POST['reject_reason'] ?? '';
                if (rejectTeacherAccount($teacherId, $_SESSION['user_id'], $rejectReason)) {
                    setFlashMessage('success', 'Compte enseignant rejeté avec succès.');
                } else {
                    setFlashMessage('error', 'Erreur lors du rejet du compte enseignant.');
                }
            }
        } elseif (isset($_POST['matiere_id']) && ($_POST['action'] === 'approve_matiere' || $_POST['action'] === 'reject_matiere')) {
            // Actions sur une matière spécifique
            $action = $_POST['action'];
            $matiereId = (int)$_POST['matiere_id'];
            $status = ($action === 'approve_matiere') ? 'approved' : 'rejected';
            $reason = $_POST['reject_reason_matiere'] ?? null;
            
            if (updateMatiereEnseigneeStatus($matiereId, $status, $_SESSION['user_id'], $reason)) {
                $message = ($status === 'approved') ? 'Matière validée avec succès.' : 'Matière rejetée avec succès.';
                setFlashMessage('success', $message);
            } else {
                $message = ($status === 'approved') ? 'Erreur lors de la validation de la matière.' : 'Erreur lors du rejet de la matière.';
                setFlashMessage('error', $message);
            }
        }
        
        // Redirection pour éviter la soumission multiple du formulaire
        header('Location: teacher_validation.php');
        exit;
    }
}

// Titre de la page
$pageTitle = 'Validation des comptes enseignants';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Portail Administrateur</title>
    <link rel="stylesheet" href="../assets/css/authentication.css">
    <style>
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filters select, .search-box input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .search-box {
            display: flex;
            flex-grow: 1;
            max-width: 400px;
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding-right: 40px;
        }
        .search-box button {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            background: none;
            border: none;
            padding: 0 10px;
            cursor: pointer;
            color: #555;
        }
        .search-box button:hover {
            color: #000;
        }
        .search-icon {
            width: 16px;
            height: 16px;
        }
        .teacher-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .teacher-card h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
        }
        .teacher-card .status {
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: normal;
        }
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .teacher-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .teacher-detail {
            margin-bottom: 5px;
        }
        .teacher-detail strong {
            font-weight: bold;
            color: #555;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .approve-btn {
            background-color: #28a745;
            color: white;
        }
        .reject-btn {
            background-color: #dc3545;
            color: white;
        }
        .view-btn {
            background-color: #17a2b8;
            color: white;
        }
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: bold;
        }
        .tab-button.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }
        .rejection-form {
            margin-top: 10px;
            display: none;
        }
        .rejection-form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 12px;
            font-weight: bold;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10px;
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .no-teachers {
            padding: 20px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-top: 20px;
        }
        .search-results {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-results .clear-search {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }
        .search-results .clear-search:hover {
            text-decoration: underline;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px;
            border-radius: 2px;
        }
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        .reset-filters {
            margin-left: auto;
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }
        .reset-filters:hover {
            text-decoration: underline;
        }
        .search-box .clear-input {
            position: absolute;
            right: 35px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 16px;
            display: none;
        }
        .search-box input:not(:placeholder-shown) + .clear-input {
            display: block;
        }
        .subjects-list {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .subjects-list h4 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #333;
        }
        .subject-item {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .subject-item:last-child {
            margin-bottom: 0;
        }
        .subject-detail {
            margin-bottom: 5px;
        }
        .subject-detail strong {
            font-weight: bold;
            color: #555;
        }
        .no-subjects {
            font-style: italic;
            color: #6c757d;
        }
        
        .subject-actions {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .subject-actions button {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .subject-item {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            position: relative;
        }
        
        .subject-item .status {
            font-size: 12px;
            padding: 3px 6px;
        }
        
        .subject-detail {
            margin-bottom: 8px;
        }
        
        .rejection-form textarea {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $pageTitle ?></h1>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?>">
                <?= $_SESSION['flash_message']['message'] ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
        
        <div class="tab-buttons">
            <button class="tab-button <?= $statusFilter === 'pending' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=pending<?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                En attente <span class="badge"><?= countTeachersByStatus($department, $adminRole, 'pending', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'approved' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=approved<?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Validés <span class="badge"><?= countTeachersByStatus($department, $adminRole, 'approved', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'rejected' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=rejected<?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Rejetés <span class="badge"><?= countTeachersByStatus($department, $adminRole, 'rejected', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'all' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=all<?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Tous <span class="badge"><?= countTeachersByStatus($department, $adminRole, 'all', $searchTerm) ?></span>
            </button>
        </div>
        
        <div class="filters">
            <form id="search-form" action="" method="get" class="search-box">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                
                <input type="text" name="search" id="search-input" placeholder="Rechercher par nom, email, téléphone..." value="<?= htmlspecialchars($searchTerm) ?>" autocomplete="off">
                <button type="button" class="clear-input" onclick="clearSearch()">&times;</button>
                <button type="submit">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
            
            <?php if (!empty($searchTerm)): ?>
                <a href="?status=<?= $statusFilter ?>" class="reset-filters">Réinitialiser les filtres</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($searchTerm)): ?>
            <div class="search-results">
                <div>
                    <strong>Résultats de recherche pour "<?= htmlspecialchars($searchTerm) ?>":</strong> 
                    <!-- <?php //count($teachers) ?> enseignant(s) trouvé(s) -->
                </div>
                <a href="?status=<?= $statusFilter ?>" class="clear-search">
                    Effacer la recherche
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($teachers)): ?>
            <div class="no-teachers">
                <p>Aucun enseignant trouvé avec les critères sélectionnés.</p>
            </div>
        <?php else: ?>
            <?php foreach ($teachers as $teacher): ?>
                <div class="teacher-card">
                    <h3>
                        <?= highlightSearchTerm($teacher['firstname'] . ' ' . $teacher['lastname'], $searchTerm) ?>
                        <span class="status status-<?= $teacher['status'] ?>">
                            <?= $teacher['status'] === 'pending' ? 'En attente' : ($teacher['status'] === 'approved' ? 'Validé' : 'Rejeté') ?>
                        </span>
                    </h3>
                    
                    <div class="teacher-details">
                        <div class="teacher-detail">
                            <strong>Email:</strong> <?= highlightSearchTerm($teacher['email'], $searchTerm) ?>
                        </div>
                        <div class="teacher-detail">
                            <strong>Téléphone:</strong> <?= highlightSearchTerm($teacher['phone'], $searchTerm) ?>
                        </div>
                        
                        <?php if ($teacher['status'] === 'rejected' && !empty($teacher['rejection_reason'])): ?>
                            <div class="teacher-detail" style="grid-column: 1 / -1;">
                                <strong>Raison du rejet:</strong> <?= htmlspecialchars($teacher['rejection_reason']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($teacher['status'] !== 'pending'): ?>
                            <div class="teacher-detail">
                                <strong>Validé par:</strong> <?= htmlspecialchars($teacher['validator_name'] ?? 'N/A') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Liste des matières enseignées -->
                    <div class="subjects-list">
                        <h4>Matières à enseigner</h4>
                        <?php 
                        $subjects = getTeacherSubjects($teacher['id']);
                        if (empty($subjects)): 
                        ?>
                            <p class="no-subjects">Aucune matière enregistrée</p>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                                <div class="subject-item">
                                    <div class="subject-detail">
                                        <strong>Matière:</strong> <?= htmlspecialchars($subject['matiere']) ?>
                                    </div>
                                    <div class="subject-detail">
                                        <strong>Code:</strong> <?= htmlspecialchars($subject['matiere_code'] ?? 'N/A') ?>
                                    </div>
                                    <div class="subject-detail">
                                        <strong>Crédits:</strong> <?= htmlspecialchars($subject['credit'] ?? 'N/A') ?>
                                    </div>
                                    <div class="subject-detail">
                                        <strong>Niveau:</strong> <?= htmlspecialchars($subject['niveau_matiere'] ?? $subject['niveau']) ?>
                                    </div>
                                    <div class="subject-detail">
                                        <strong>Salle:</strong> <?= htmlspecialchars($subject['salle']) ?>
                                    </div>
                                    <div class="subject-detail">
                                        <strong>Statut:</strong> 
                                        <span class="status status-<?= $subject['status'] ?? 'pending' ?>">
                                            <?= ($subject['status'] ?? 'pending') === 'pending' ? 'En attente' : 
                                                (($subject['status'] ?? 'pending') === 'approved' ? 'Validé' : 'Rejeté') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($subject['notes'])): ?>
                                        <div class="subject-detail" style="grid-column: 1 / -1;">
                                            <strong>Notes:</strong> <?= htmlspecialchars($subject['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($subject['rejection_reason'])): ?>
                                        <div class="subject-detail" style="grid-column: 1 / -1;">
                                            <strong>Raison du rejet:</strong> <?= htmlspecialchars($subject['rejection_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($subject['status'] === 'pending'): ?>
                                        <div class="subject-actions" style="grid-column: 1 / -1; margin-top: 10px;">
                                            <form method="post" style="display: inline-block; margin-right: 10px;">
                                                <input type="hidden" name="matiere_id" value="<?= $subject['id'] ?>">
                                                <input type="hidden" name="action" value="approve_matiere">
                                                <button type="submit" class="approve-btn" style="padding: 5px 10px; font-size: 12px;">Valider cette matière</button>
                                            </form>
                                            
                                            <button class="reject-btn" style="padding: 5px 10px; font-size: 12px;" onclick="showRejectMatiereForm(<?= $subject['id'] ?>)">Rejeter cette matière</button>
                                            
                                            <div id="reject-matiere-form-<?= $subject['id'] ?>" class="rejection-form" style="margin-top: 10px; display: none;">
                                                <form method="post">
                                                    <input type="hidden" name="matiere_id" value="<?= $subject['id'] ?>">
                                                    <input type="hidden" name="action" value="reject_matiere">
                                                    <textarea name="reject_reason_matiere" placeholder="Raison du rejet" required style="width: 100%; padding: 5px;"></textarea>
                                                    <button type="submit" class="reject-btn" style="margin-top: 5px;">Confirmer le rejet</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (array_search('pending', array_column($subjects, 'status')) !== false): ?>
                        <div class="actions">
                            <form method="post" class="approve-form">
                                <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="approve-btn">Valider toutes les matières</button>
                            </form>
                            
                            <button class="reject-btn" onclick="showRejectForm(<?= $teacher['id'] ?>)">Rejeter toutes les matières</button>
                            
                            <div id="reject-form-<?= $teacher['id'] ?>" class="rejection-form">
                                <form method="post">
                                    <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <textarea name="reject_reason" placeholder="Raison du rejet" required></textarea>
                                    <button type="submit" class="reject-btn">Confirmer le rejet</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function showRejectForm(teacherId) {
            const formId = `reject-form-${teacherId}`;
            const form = document.getElementById(formId);
            if (form.style.display === 'block') {
                form.style.display = 'none';
            } else {
                form.style.display = 'block';
            }
        }
        
        function showRejectMatiereForm(matiereId) {
            const formId = `reject-matiere-form-${matiereId}`;
            const form = document.getElementById(formId);
            if (form.style.display === 'block') {
                form.style.display = 'none';
            } else {
                form.style.display = 'block';
            }
        }
        
        function clearSearch() {
            document.getElementById('search-input').value = '';
            document.getElementById('search-form').submit();
        }
        
        // Ajouter un événement pour soumettre le formulaire lorsque l'utilisateur appuie sur Entrée
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('search-form').submit();
            }
        });
        
        // Ajouter un événement pour effacer le champ de recherche lorsque l'utilisateur clique sur le bouton X
        document.querySelector('.clear-input').addEventListener('click', function() {
            document.getElementById('search-input').value = '';
            document.getElementById('search-form').submit();
        });
    </script>
</body>
</html>

<?php
/**
 * Met en surbrillance le terme de recherche dans un texte
 * 
 * @param string $text Le texte à traiter
 * @param string $term Le terme de recherche
 * @return string Le texte avec le terme de recherche en surbrillance
 */
function highlightSearchTerm($text, $term) {
    if (empty($term)) {
        return htmlspecialchars($text);
    }
    
    $text = htmlspecialchars($text);
    $term = htmlspecialchars($term);
    
    // Échapper les caractères spéciaux dans le terme de recherche pour l'utiliser dans une regex
    $escapedTerm = preg_quote($term, '/');
    
    // Remplacer le terme de recherche par la version en surbrillance
    return preg_replace('/(' . $escapedTerm . ')/i', '<span class="highlight">$1</span>', $text);
}
?>
