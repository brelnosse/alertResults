<?php
/**
 * Contrôleur pour la gestion des départements et spécialités
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les départements
 * et les spécialités dans l'interface d'administration.
 * 
 * @package     AlertResults
 * @subpackage  Controllers
 * @category    Administration
 * @author      v0
 * @version     1.0
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../shared/models/department_model.php';
require_once __DIR__ . '/../../shared/models/speciality_model.php';
require_once __DIR__ . '/../../shared/models/department_speciality_model.php';
require_once __DIR__ . '/../../shared/utils/error_handler.php';

/**
 * Affiche la page de gestion des départements
 */
function showDepartmentsPage() {
    $departments = getAllDepartments();
    $stats = [];
    
    foreach ($departments as $department) {
        $stats[$department['id']] = getDepartmentStats($department['id']);
    }
    
    include __DIR__ . '/../views/departments_view.php';
}

/**
 * Affiche la page de gestion des spécialités
 */
function showSpecialitiesPage() {
    $specialities = getAllSpecialities();
    $departments = getAllDepartments();
    
    include __DIR__ . '/../views/specialities_view.php';
}

/**
 * Affiche la page de gestion des associations département-spécialité
 */
function showDepartmentSpecialitiesPage() {
    $departments = getAllDepartments();
    $specialities = getAllSpecialities();
    $associations = getAllDepartmentSpecialities();
    $stats = getDepartmentSpecialityStats();
    
    include __DIR__ . '/../views/department_speciality_view.php';
}

/**
 * Traite l'ajout d'un nouveau département
 */
function handleAddDepartment() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($nom)) {
            $errorHandler->addError('nom', 'Le nom du département est requis');
        }
        
        if (empty($code)) {
            $errorHandler->addError('code', 'Le code du département est requis');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = addDepartment($nom, $code, $description);
            
            if ($result) {
                // $errorHandler->addSuccess('Le département a été ajouté avec succès');
                // Rediriger vers la page des départements
                header('Location: index.php?page=departments');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout du département');
            }
        }
    }
    
    $departments = getAllDepartments();
    include __DIR__ . '/../views/departments_view.php';
}

/**
 * Traite la mise à jour d'un département
 */
function handleUpdateDepartment() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validation
        if (empty($nom)) {
            $errorHandler->addError('nom', 'Le nom du département est requis');
        }
        
        if (empty($code)) {
            $errorHandler->addError('code', 'Le code du département est requis');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = updateDepartment($id, $nom, $code, $description, $active);
            
            if ($result) {
                // $errorHandler->addSuccess('Le département a été mis à jour avec succès');
                // Rediriger vers la page des départements
                header('Location: index.php?page=departments');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de la mise à jour du département');
            }
        }
    }
    
    $departments = getAllDepartments();
    include __DIR__ . '/../views/departments_view.php';
}

/**
 * Traite la suppression d'un département
 */
function handleDeleteDepartment() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        
        $result = deleteDepartment($id);
        
        if ($result) {
            // $errorHandler->addSuccess('Le département a été supprimé avec succès');
        } else {
            $errorHandler->addError('general', 'Impossible de supprimer ce département car il est utilisé par des étudiants, des enseignants ou des administrateurs');
        }
    }
    
    // Rediriger vers la page des départements
    header('Location: index.php?page=departments');
    exit;
}

/**
 * Traite l'ajout d'une nouvelle spécialité
 */
function handleAddSpeciality() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($nom)) {
            $errorHandler->addError('nom', 'Le nom de la spécialité est requis');
        }
        
        if (empty($code)) {
            $errorHandler->addError('code', 'Le code de la spécialité est requis');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = addSpeciality($nom, $code, $description);
            
            if ($result) {
                // $errorHandler->addSuccess('La spécialité a été ajoutée avec succès');
                // Rediriger vers la page des spécialités
                header('Location: index.php?page=specialities');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout de la spécialité');
            }
        }
    }
    
    $specialities = getAllSpecialities();
    $departments = getAllDepartments();
    include __DIR__ . '/../views/specialities_view.php';
}

/**
 * Traite la mise à jour d'une spécialité
 */
function handleUpdateSpeciality() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;
        
        // Validation
        if (empty($nom)) {
            $errorHandler->addError('nom', 'Le nom de la spécialité est requis');
        }
        
        if (empty($code)) {
            $errorHandler->addError('code', 'Le code de la spécialité est requis');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = updateSpeciality($id, $nom, $code, $description, $active);
            
            if ($result) {
                // $errorHandler->addSuccess('La spécialité a été mise à jour avec succès');
                // Rediriger vers la page des spécialités
                header('Location: index.php?page=specialities');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de la mise à jour de la spécialité');
            }
        }
    }
    
    $specialities = getAllSpecialities();
    $departments = getAllDepartments();
    include __DIR__ . '/../views/specialities_view.php';
}

/**
 * Traite la suppression d'une spécialité
 */
function handleDeleteSpeciality() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        
        $result = deleteSpeciality($id);
        
        if ($result) {
            // $errorHandler->addSuccess('La spécialité a été supprimée avec succès');
        } else {
            $errorHandler->addError('general', 'Impossible de supprimer cette spécialité car elle est utilisée par des étudiants ou des enseignants');
        }
    }
    
    // Rediriger vers la page des spécialités
    header('Location: index.php?page=specialities');
    exit;
}

/**
 * Traite l'ajout d'une association département-spécialité
 */
function handleAddDepartmentSpeciality() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $departmentId = intval($_POST['departement_id'] ?? 0);
        $specialityId = intval($_POST['specialite_id'] ?? 0);
        
        // Validation
        if ($departmentId <= 0) {
            $errorHandler->addError('departement_id', 'Veuillez sélectionner un département');
        }
        
        if ($specialityId <= 0) {
            $errorHandler->addError('specialite_id', 'Veuillez sélectionner une spécialité');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = addDepartmentSpeciality($departmentId, $specialityId);
            
            if ($result) {
                // $errorHandler->addSuccess('L\'association a été ajoutée avec succès');
                // Rediriger vers la page des associations
                header('Location: index.php?page=department_specialities');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout de l\'association');
            }
        }
    }
    
    $departments = getAllDepartments();
    $specialities = getAllSpecialities();
    $associations = getAllDepartmentSpecialities();
    $stats = getDepartmentSpecialityStats();
    include __DIR__ . '/../views/department_speciality_view.php';
}

/**
 * Traite la suppression d'une association département-spécialité
 */
function handleDeleteDepartmentSpeciality() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        
        $result = deleteDepartmentSpeciality($id);
        
        if ($result) {
            // $errorHandler->addSuccess('L\'association a été supprimée avec succès');
        } else {
            $errorHandler->addError('general', 'Impossible de supprimer cette association car elle est utilisée par des étudiants ou des enseignants');
        }
    }
    
    // Rediriger vers la page des associations
    header('Location: index.php?page=department_specialities');
    exit;
}

/**
 * Traite l'ajout d'une association spécialité-cycle-niveau
 */
function handleAddSpecialityCycleLevel() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $specialityId = intval($_POST['specialite_id'] ?? 0);
        $cycle = trim($_POST['cycle'] ?? '');
        $niveau = intval($_POST['niveau'] ?? 0);
        
        // Validation
        if ($specialityId <= 0) {
            $errorHandler->addError('specialite_id', 'Veuillez sélectionner une spécialité');
        }
        
        if (empty($cycle)) {
            $errorHandler->addError('cycle', 'Veuillez sélectionner un cycle');
        }
        
        if ($niveau <= 0) {
            $errorHandler->addError('niveau', 'Veuillez sélectionner un niveau');
        }
        
        if (!$errorHandler->hasErrors()) {
            $result = associateSpecialityToCycleAndLevel($specialityId, $cycle, $niveau);
            
            if ($result) {
                // $errorHandler->addSuccess('L\'association a été ajoutée avec succès');
                // Rediriger vers la page des spécialités
                header('Location: index.php?page=specialities');
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout de l\'association');
            }
        }
    }
    
    $specialities = getAllSpecialities();
    $departments = getAllDepartments();
    include __DIR__ . '/../views/specialities_view.php';
}

/**
 * Traite la suppression d'une association spécialité-cycle-niveau
 */
function handleDeleteSpecialityCycleLevel() {
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $specialityId = intval($_POST['specialite_id'] ?? 0);
        $cycle = trim($_POST['cycle'] ?? '');
        $niveau = intval($_POST['niveau'] ?? 0);
        
        $result = dissociateSpecialityFromCycleAndLevel($specialityId, $cycle, $niveau);
        
        if ($result) {
            // $errorHandler->addSuccess('L\'association a été supprimée avec succès');
        } else {
            $errorHandler->addError('general', 'Impossible de supprimer cette association car elle est utilisée par des étudiants');
        }
    }
    
    // Rediriger vers la page des spécialités
    header('Location: index.php?page=specialities');
    exit;
}

/**
 * Récupère les spécialités d'un département au format JSON (pour AJAX)
 */
function getSpecialitiesByDepartmentJson() {
    header('Content-Type: application/json');
    
    $departmentId = intval($_GET['department_id'] ?? 0);
    
    if ($departmentId <= 0) {
        echo json_encode(['error' => 'ID de département invalide']);
        exit;
    }
    
    $specialities = getSpecialitiesByDepartment($departmentId, true);
    echo json_encode($specialities);
    exit;
}

/**
 * Récupère les spécialités pour un cycle et un niveau dans un département au format JSON (pour AJAX)
 */
function getSpecialitiesForCycleAndLevelInDepartmentJson() {
    header('Content-Type: application/json');
    
    $departmentId = intval($_GET['department_id'] ?? 0);
    $cycle = trim($_GET['cycle'] ?? '');
    $niveau = intval($_GET['niveau'] ?? 0);
    
    if ($departmentId <= 0 || empty($cycle) || $niveau <= 0) {
        echo json_encode(['error' => 'Paramètres invalides']);
        exit;
    }
    
    $specialities = getSpecialitiesForCycleAndLevelInDepartment($departmentId, $cycle, $niveau);
    echo json_encode($specialities);
    exit;
}
?>
