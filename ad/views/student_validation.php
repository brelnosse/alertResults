<?php
require_once __DIR__ . '/../../shared/init.php';
require_once __DIR__ . '/../init_ad.php';
require_once __DIR__ . '/../controllers/student_validation_controller.php';

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
$_SESSION['admin_department_name'] = getDepartmentById($_SESSION['admin_department'])['nom'];
// Récupérer le département de l'administrateur
$department = getDepartmentById($_SESSION['admin_department'])['nom'] ?? '';
// Récupérer les filtres
$statusFilter = $_GET['status'] ?? 'pending';
$specialiteFilter = $_GET['specialite'] ?? '';
$cycleFilter = $_GET['cycle'] ?? '';
$niveauFilter = $_GET['niveau'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Récupérer la liste des étudiants selon les filtres
$students = getFilteredStudents($department, $adminRole, $statusFilter, $specialiteFilter, $cycleFilter, $niveauFilter, $searchTerm);

// Récupérer la liste des spécialités disponibles pour ce département
$specialites = getSpecialitesByDepartment($department);

// Traitement des actions de validation/rejet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['student_id'])) {
        $action = $_POST['action'];
        $studentId = (int)$_POST['student_id'];
        
        if ($action === 'approve') {
            // Valider le compte étudiant
            if (approveStudentAccount($studentId, $_SESSION['user_id'])) {
                setFlashMessage('success', 'Compte étudiant validé avec succès.');
            } else {
                setFlashMessage('error', 'Erreur lors de la validation du compte étudiant.');
            }
        } elseif ($action === 'reject') {
            // Rejeter le compte étudiant avec une raison
            $rejectReason = $_POST['reject_reason'] ?? '';
            if (rejectStudentAccount($studentId, $_SESSION['user_id'], $rejectReason)) {
                setFlashMessage('success', 'Compte étudiant rejeté avec succès.');
            } else {
                setFlashMessage('error', 'Erreur lors du rejet du compte étudiant.');
            }
        }
        // Redirection pour éviter la soumission multiple du formulaire
        header('Location: student_validation.php');
        exit;
    }
}

// Titre de la page
$pageTitle = 'Validation des comptes étudiants';
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
        .student-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .student-card h3 {
            margin-top: 0;
            color: #333;
            display: flex;
            justify-content: space-between;
        }
        .student-card .status {
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
        .student-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .student-detail {
            margin-bottom: 5px;
        }
        .student-detail strong {
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
        .no-students {
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
                    onclick="window.location.href='?status=pending<?= $specialiteFilter ? '&specialite='.$specialiteFilter : '' ?><?= $cycleFilter ? '&cycle='.$cycleFilter : '' ?><?= $niveauFilter ? '&niveau='.$niveauFilter : '' ?><?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                En attente <span class="badge"><?= countStudentsByStatus($department, $adminRole, 'pending', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'approved' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=approved<?= $specialiteFilter ? '&specialite='.$specialiteFilter : '' ?><?= $cycleFilter ? '&cycle='.$cycleFilter : '' ?><?= $niveauFilter ? '&niveau='.$niveauFilter : '' ?><?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Validés <span class="badge"><?= countStudentsByStatus($department, $adminRole, 'approved', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'rejected' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=rejected<?= $specialiteFilter ? '&specialite='.$specialiteFilter : '' ?><?= $cycleFilter ? '&cycle='.$cycleFilter : '' ?><?= $niveauFilter ? '&niveau='.$niveauFilter : '' ?><?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Rejetés <span class="badge"><?= countStudentsByStatus($department, $adminRole, 'rejected', $searchTerm) ?></span>
            </button>
            <button class="tab-button <?= $statusFilter === 'all' ? 'active' : '' ?>" 
                    onclick="window.location.href='?status=all<?= $specialiteFilter ? '&specialite='.$specialiteFilter : '' ?><?= $cycleFilter ? '&cycle='.$cycleFilter : '' ?><?= $niveauFilter ? '&niveau='.$niveauFilter : '' ?><?= $searchTerm ? '&search='.$searchTerm : '' ?>'">
                Tous <span class="badge"><?= countStudentsByStatus($department, $adminRole, 'all', $searchTerm) ?></span>
            </button>
        </div>
        
        <div class="filters">
            <form id="search-form" action="" method="get" class="search-box">
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                <?php if ($specialiteFilter): ?>
                    <input type="hidden" name="specialite" value="<?= htmlspecialchars($specialiteFilter) ?>">
                <?php endif; ?>
                <?php if ($cycleFilter): ?>
                    <input type="hidden" name="cycle" value="<?= htmlspecialchars($cycleFilter) ?>">
                <?php endif; ?>
                <?php if ($niveauFilter): ?>
                    <input type="hidden" name="niveau" value="<?= htmlspecialchars($niveauFilter) ?>">
                <?php endif; ?>
                
                <input type="text" name="search" id="search-input" placeholder="Rechercher par nom, email, matricule..." value="<?= htmlspecialchars($searchTerm) ?>" autocomplete="off">
                <button type="button" class="clear-input" onclick="clearSearch()">&times;</button>
                <button type="submit">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
            
            <div class="filter-group">
                <span class="filter-label">Spécialité:</span>
                <select id="specialite-filter" onchange="applyFilters()">
                    <option value="">Toutes</option>
                    <?php foreach ($specialites as $specialite): ?>
                        <option value="<?= htmlspecialchars($specialite) ?>" <?= $specialiteFilter === $specialite ? 'selected' : '' ?>>
                            <?= htmlspecialchars($specialite) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- <div class="filter-group">
                <span class="filter-label">Cycle:</span>
                <select id="cycle-filter" onchange="applyFilters()">
                    <option value="">Tous</option>
                    <option value="Licence" <?= $cycleFilter === 'Licence' ? 'selected' : '' ?>>Licence</option>
                    <option value="Master" <?= $cycleFilter === 'Master' ? 'selected' : '' ?>>Master</option>
                    <option value="Doctorat" <?= $cycleFilter === 'Doctorat' ? 'selected' : '' ?>>Doctorat</option>
                </select>
            </div> -->
            
            <div class="filter-group">
                <span class="filter-label">Niveau:</span>
                <select id="niveau-filter" onchange="applyFilters()">
                    <option value="">Tous</option>
                    <option value="1" <?= $niveauFilter === '1' ? 'selected' : '' ?>>Niveau 1</option>
                    <option value="2" <?= $niveauFilter === '2' ? 'selected' : '' ?>>Niveau 2</option>
                </select>
            </div>
            
            <?php if ($specialiteFilter || $cycleFilter || $niveauFilter || $searchTerm): ?>
                <a href="?status=<?= $statusFilter ?>" class="reset-filters">Réinitialiser les filtres</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($searchTerm)): ?>
            <div class="search-results">
                <div>
                    <strong>Résultats de recherche pour "<?= htmlspecialchars($searchTerm) ?>":</strong> 
                    <?= count($students) ?> étudiant(s) trouvé(s)
                </div>
                <a href="?status=<?= $statusFilter ?><?= $specialiteFilter ? '&specialite='.$specialiteFilter : '' ?><?= $cycleFilter ? '&cycle='.$cycleFilter : '' ?><?= $niveauFilter ? '&niveau='.$niveauFilter : '' ?>" class="clear-search">
                    Effacer la recherche
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($students)): ?>
            <div class="no-students">
                <p>Aucun étudiant trouvé avec les critères sélectionnés.</p>
            </div>
        <?php else: ?>
            <?php foreach ($students as $student): ?>
                <div class="student-card">
                    <h3>
                        <?= highlightSearchTerm($student['firstname'] . ' ' . $student['lastname'], $searchTerm) ?>
                        <span class="status status-<?= $student['status'] ?>">
                            <?= $student['status'] === 'pending' ? 'En attente' : ($student['status'] === 'approved' ? 'Validé' : 'Rejeté') ?>
                        </span>
                    </h3>
                    
                    <div class="student-details">
                        <div class="student-detail">
                            <strong>Matricule:</strong> <?= highlightSearchTerm($student['matricule'], $searchTerm) ?>
                        </div>
                        <div class="student-detail">
                            <strong>Email:</strong> <?= highlightSearchTerm($student['email'], $searchTerm) ?>
                        </div>
                        <div class="student-detail">
                            <strong>Cycle:</strong> <?= htmlspecialchars($student['cycle']) ?>
                        </div>
                        <div class="student-detail">
                            <strong>Niveau:</strong> <?= htmlspecialchars($student['niveau']) ?>
                        </div>
                        <div class="student-detail">
                            <strong>Spécialité:</strong> <?= htmlspecialchars($student['specialite']) ?>
                        </div>
                        <div class="student-detail">
                            <strong>Classe:</strong> <?= htmlspecialchars($student['classe']) ?>
                        </div>
                        <?php if ($student['status'] === 'rejected' && !empty($student['rejection_reason'])): ?>
                            <div class="student-detail" style="grid-column: 1 / -1;">
                                <strong>Raison du rejet:</strong> <?= htmlspecialchars($student['rejection_reason']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($student['status'] !== 'pending'): ?>
                            <div class="student-detail">
                                <strong>Validé par:</strong> <?= htmlspecialchars($student['validator_name'] ?? 'N/A') ?>
                            </div>
                            <div class="student-detail">
                                <strong>Date:</strong> <?= formatDate($student['validated_at']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($student['status'] === 'pending'): ?>
                        <div class="actions">
                            <form method="post" class="approve-form">
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="approve-btn">Valider</button>
                            </form>
                            
                            <button class="reject-btn" onclick="showRejectForm(<?= $student['id'] ?>)">Rejeter</button>
                            
                            <div id="reject-form-<?= $student['id'] ?>" class="rejection-form">
                                <form method="post">
                                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
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
        function showRejectForm(studentId) {
            const formId = `reject-form-${studentId}`;
            const form = document.getElementById(formId);
            if (form.style.display === 'block') {
                form.style.display = 'none';
            } else {
                form.style.display = 'block';
            }
        }
        
        function applyFilters() {
            const specialite = document.getElementById('specialite-filter').value;
            const cycle = document.getElementById('cycle-filter').value;
            const niveau = document.getElementById('niveau-filter').value;
            const status = '<?= $statusFilter ?>';
            const search = '<?= $searchTerm ?>';
            
            let url = `?status=${status}`;
            if (specialite) url += `&specialite=${encodeURIComponent(specialite)}`;
            if (cycle) url += `&cycle=${encodeURIComponent(cycle)}`;
            if (niveau) url += `&niveau=${encodeURIComponent(niveau)}`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            
            window.location.href = url;
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
