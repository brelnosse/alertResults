<?php
/**
 * Contrôleur pour la gestion des requêtes côté administrateur
 * 
 * Ce fichier contient toutes les fonctions nécessaires pour gérer les requêtes
 * des étudiants du côté administrateur (chef de département).
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
require_once __DIR__ . '/../../shared/utils/error_handler.php';
require_once __DIR__ . '/../../shared/utils/mailer.php';

/**
 * Affiche la liste des requêtes en attente pour un département
 */
function showPendingRequetes() {
    // Vérifier si l'utilisateur est connecté et est un administrateur
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
    
    $adminId = $_SESSION['user_id'];
    $adminDetails = getAdminDetails($adminId);
    
    // Vérifier si l'administrateur est associé à un département
    if (!$adminDetails || !isset($adminDetails['departement_id'])) {
        $errorMessage = "Vous n'êtes pas associé à un département.";
        include __DIR__ . '/../views/requetes_admin.php';
        return;
    }
    
    $departmentId = $adminDetails['departement_id'];
    $requetes = getPendingRequetesByDepartment($departmentId);
    
    include __DIR__ . '/../views/requetes_admin.php';
}

/**
 * Affiche les détails d'une requête pour un administrateur
 * 
 * @param int $requeteId ID de la requête à afficher
 */
function showRequeteDetailsAdmin($requeteId) {
    // Vérifier si l'utilisateur est connecté et est un administrateur
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
    
    $adminId = $_SESSION['user_id'];
    $adminDetails = getAdminDetails($adminId);
    
    // Vérifier si l'administrateur est associé à un département
    if (!$adminDetails || !isset($adminDetails['departement_id'])) {
        $_SESSION['error_message'] = "Vous n'êtes pas associé à un département.";
        header('Location: index.php?page=requetes_admin');
        exit;
    }
    
    $departmentId = $adminDetails['departement_id'];
    $requete = getRequeteById($requeteId);
    
    // Vérifier si la requête existe et appartient au département de l'administrateur
    if (!$requete || $requete['department_id'] != $departmentId) {
        $_SESSION['error_message'] = "La requête demandée n'existe pas ou n'appartient pas à votre département.";
        header('Location: index.php?page=requetes_admin');
        exit;
    }
    
    $reponses = getReponsesForRequete($requeteId);
    
    // Traiter l'ajout d'une réponse
    $errorHandler = new ErrorHandler();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'approve' || $action === 'reject') {
                $contenu = trim($_POST['reponse'] ?? '');
                
                if (empty($contenu)) {
                    $errorHandler->addError('reponse', 'Veuillez fournir une explication pour votre décision');
                } else {
                    // Mettre à jour le statut de la requête
                    $newStatus = ($action === 'approve') ? 'approuvee' : 'rejetee';
                    $result = updateRequeteStatus($requeteId, $newStatus, $adminId);
                    
                    if ($result) {
                        // Ajouter la réponse
                        $reponseId = addReponseToRequete($requeteId, $adminId, $contenu);
                        
                        if ($reponseId) {
                            // Envoyer une notification à l'étudiant
                            $mailer = new Mailer();
                            $studentEmail = $requete['student_email'] ?? '';
                            $studentFirstname = $requete['student_firstname'] ?? '';
                            $studentLastname = $requete['student_lastname'] ?? '';
                            
                            if ($action === 'approve') {
                                // Notifier l'enseignant également
                                $teacherEmail = $requete['teacher_email'] ?? '';
                                $teacherFirstname = $requete['teacher_firstname'] ?? '';
                                $teacherLastname = $requete['teacher_lastname'] ?? '';
                                
                                if (!empty($teacherEmail)) {
                                    // Envoyer un email à l'enseignant
                                    // $mailer->sendTeacherRequeteNotification(...);
                                }
                            } else {
                                // Notifier l'étudiant du rejet
                                // $mailer->sendStudentRequeteRejectionNotification(...);
                            }
                            
                            // Rediriger pour éviter la soumission multiple
                            $_SESSION['success_message'] = "La requête a été " . ($action === 'approve' ? "approuvée" : "rejetée") . " avec succès.";
                            header('Location: index.php?page=requetes_admin');
                            exit;
                        }
                    }
                    
                    $errorHandler->addError('general', 'Une erreur est survenue lors du traitement de la requête');
                }
            }
        } elseif (isset($_POST['reponse'])) {
            // Ajouter un commentaire sans changer le statut
            $contenu = trim($_POST['reponse']);
            
            if (empty($contenu)) {
                $errorHandler->addError('reponse', 'La réponse ne peut pas être vide');
            } else {
                $result = addReponseToRequete($requeteId, $adminId, $contenu);
                
                if ($result) {
                    // Rediriger pour éviter la soumission multiple
                    header('Location: index.php?page=voir_requete_admin&id=' . $requeteId);
                    exit;
                } else {
                    $errorHandler->addError('general', 'Une erreur est survenue lors de l\'ajout de la réponse');
                }
            }
        }
    }
    
    include __DIR__ . '/../views/voir_requete_admin.php';
}
?>
