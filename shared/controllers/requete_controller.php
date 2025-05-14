<?php
/**
 * Contrôleur pour la gestion des requêtes des étudiants
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les requêtes
 * des étudiants concernant des erreurs potentielles dans les matières.
 * 
 * @package     AlertResults
 * @subpackage  Controllers
 * @category    Requêtes
 * @author      v0
 * @version     1.0
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../../shared/models/requete_model.php';
require_once __DIR__ . '/../../shared/models/user_model.php';
require_once __DIR__ . '/../../shared/models/matiere_model.php';
require_once __DIR__ . '/../../shared/utils/error_handler.php';

/**
 * Traite la soumission d'une nouvelle requête
 */
function handleSubmitRequete() {
    // Vérifier si l'utilisateur est connecté et est un étudiant
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../index.php');
        exit;
    }
    
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer et nettoyer les données du formulaire
        $teacherId = intval($_POST['teacher_id'] ?? 0);
        $matiereId = intval($_POST['matiere_id'] ?? 0);
        $sujet = trim($_POST['sujet'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? '');
        
        // Validation
        if ($teacherId <= 0) {
            $errorHandler->addError('teacher_id', 'Veuillez sélectionner un enseignant');
        }
        
        if ($matiereId <= 0) {
            $errorHandler->addError('matiere_id', 'Veuillez sélectionner une matière');
        }
        
        if (empty($sujet)) {
            $errorHandler->addError('sujet', 'Le sujet est requis');
        } elseif (strlen($sujet) > 100) {
            $errorHandler->addError('sujet', 'Le sujet ne doit pas dépasser 100 caractères');
        }
        
        if (empty($description)) {
            $errorHandler->addError('description', 'La description est requise');
        }
        
        if (empty($type)) {
            $errorHandler->addError('type', 'Le type de requête est requis');
        }
        
        // Si pas d'erreurs, créer la requête
        if (!$errorHandler->hasErrors()) {
            $studentId = $_SESSION['user_id'];
            $departmentId = getStudentDepartment($studentId);
            
            if (!$departmentId) {
                $errorHandler->addError('general', 'Impossible de déterminer votre département');
            } else {
                $result = createRequete(
                    $studentId,
                    $teacherId,
                    $matiereId,
                    $sujet,
                    $description,
                    $type,
                    $departmentId
                );
                
                if ($result) {
                    // Rediriger vers la liste des requêtes avec un message de succès
                    $_SESSION['success_message'] = 'Votre requête a été soumise avec succès et est en attente de validation par le chef de département.';
                    header('Location: index.php?page=requetes');
                    exit;
                } else {
                    $errorHandler->addError('general', 'Une erreur est survenue lors de la création de la requête');
                }
            }
        }
    }
    
    // Récupérer les données pour le formulaire
    $studentId = $_SESSION['user_id'];
    $studentDetails = getStudentDetailsByUserId($studentId);
    $teachers = getTeachersForStudent($studentId);
    
    // Récupérer les matières si un enseignant est sélectionné
    $matieres = [];
    if (isset($_POST['teacher_id']) && intval($_POST['teacher_id']) > 0) {
        $matieres = getMatieresForTeacherAndNiveau(
            intval($_POST['teacher_id']),
            $studentDetails['niveau']
        );
    }
    
    // Afficher le formulaire avec les erreurs éventuelles
    include __DIR__ . '/../views/nouvelle_requete.php';
}

/**
 * Affiche la liste des requêtes d'un étudiant
 */
function showRequetesList() {
    // Vérifier si l'utilisateur est connecté et est un étudiant
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../index.php');
        exit;
    }
    
    $studentId = $_SESSION['user_id'];
    $requetes = getRequetesByStudent($studentId);
    $counts = getRequeteCountsByStatusForStudent($studentId);
    
    include __DIR__ . '/../views/requetes.php';
}

/**
 * Affiche les détails d'une requête
 * 
 * @param int $requeteId ID de la requête à afficher
 */
function showRequeteDetails($requeteId) {
    // Vérifier si l'utilisateur est connecté et est un étudiant
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        header('Location: ../index.php');
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $userType = $_SESSION['user_type'];
    
    // Vérifier si l'utilisateur a le droit de voir cette requête
    if (!canUserAccessRequete($requeteId, $userId, $userType)) {
        $_SESSION['error_message'] = 'Vous n\'avez pas accès à cette requête';
        header('Location: index.php?page=requetes');
        exit;
    }
    
    $requete = getRequeteById($requeteId);
    $reponses = getReponsesForRequete($requeteId);
    
    // Traiter l'ajout d'une réponse
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponse'])) {
        $contenu = trim($_POST['reponse'] ?? '');
        
        if (empty($contenu)) {
            $errorHandler->addError('reponse', 'La réponse ne peut pas être vide');
        } else {
            $result = addReponseToRequete($requeteId, $userId, $contenu);
            
            if ($result) {
                // Rediriger pour éviter la soumission multiple
                header('Location: index.php?page=voir_requete&id=' . $requeteId);
                exit;
            } else {
                $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout de la réponse');
            }
        }
    }
    
    include __DIR__ . '/../views/voir_requete.php';
}

/**
 * Récupère les matières pour un enseignant via AJAX
 */
function getMatieresForTeacherAjax() {
    // Vérifier si l'utilisateur est connecté et est un étudiant
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Non autorisé']);
        exit;
    }
    
    $teacherId = intval($_GET['teacher_id'] ?? 0);
    
    if ($teacherId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID d\'enseignant invalide']);
        exit;
    }
    
    $studentId = $_SESSION['user_id'];
    $studentDetails = getStudentDetailsByUserId($studentId);
    
    $matieres = getMatieresForTeacherAndNiveau($teacherId, $studentDetails['niveau']);
    
    header('Content-Type: application/json');
    echo json_encode($matieres);
    exit;
}
?>
