<?php
// Inclure le contrôleur commun
require_once '../../shared/controllers/user_common_controller.php';
require_once '../../shared/models/user_model.php';
require_once '../../shared/models/department_model.php';
require_once '../../shared/utils/error_handler.php';

// Initialiser le gestionnaire d'erreurs
$errorHandler = new ErrorHandler();

// Traitement du formulaire d'inscription des administrateurs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et nettoyer les données du formulaire
        $firstname = sanitizeInput($_POST['firstname'] ?? '');
        $lastname = sanitizeInput($_POST['lastname'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $role = sanitizeInput($_POST['role'] ?? '');
        $department = sanitizeInput($_POST['department'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validation des données
        if (empty($firstname)) {
            $errorHandler->addError('firstname', 'Le prénom est obligatoire');
        }
        
        if (empty($lastname)) {
            $errorHandler->addError('lastname', 'Le nom est obligatoire');
        }
        
        if (empty($phone)) {
            $errorHandler->addError('phone', 'Le numéro de téléphone est obligatoire');
        } elseif (!validateCameroonPhone($phone)) {
            $errorHandler->addError('phone', 'Le format du numéro de téléphone est invalide (6XXXXXXXX)');
        } elseif (phoneExists($phone)) {
            $errorHandler->addError('phone', 'Ce numéro de téléphone est déjà utilisé');
        }
        
        if (empty($email)) {
            $errorHandler->addError('email', 'L\'adresse email est obligatoire');
        } elseif (!validateEmail($email)) {
            $errorHandler->addError('email', 'L\'adresse email est invalide');
        } elseif (emailExists($email)) {
            $errorHandler->addError('email', 'Cette adresse email est déjà utilisée');
        }
        
        if (empty($role)) {
            $errorHandler->addError('role', 'Le rôle est obligatoire');
        } elseif (!in_array($role, ['directeur', 'chef'])) {
            $errorHandler->addError('role', 'Le rôle sélectionné est invalide');
        }
        
        // Vérifier s'il existe déjà un directeur si le rôle est directeur
        if ($role === 'directeur' && directorExists()) {
            $errorHandler->addError('role', 'Il existe déjà un directeur dans le système');
        }
        
        // Le département est obligatoire uniquement pour les chefs de département
        if ($role === 'chef' && empty($department)) {
            $errorHandler->addError('department', 'Le département est obligatoire pour les chefs de département');
        } elseif ($role === 'chef' && !departmentExists($department)) {
            $errorHandler->addError('department', 'Le département sélectionné n\'existe pas'.$department);
        }
        
        if (empty($password)) {
            $errorHandler->addError('password', 'Le mot de passe est obligatoire');
        } elseif (!validatePassword($password)) {
            $errorHandler->addError('password', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre');
        }
        
        if (empty($confirmPassword)) {
            $errorHandler->addError('confirmPassword', 'La confirmation du mot de passe est obligatoire');
        } elseif (!passwordsMatch($password, $confirmPassword)) {
            $errorHandler->addError('confirmPassword', 'Les mots de passe ne correspondent pas');
        }
        
        // Si des erreurs sont présentes, les afficher
        if ($errorHandler->hasErrors()) {
            $errorHandler->handleErrors('Veuillez corriger les erreurs dans le formulaire');
        }
        
        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Préparation des données pour l'insertion
        $userData = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashedPassword,
            'user_type' => 'admin'
        ];
        
        // Ajout de l'utilisateur
        $userId = addUser($userData);
        
        if ($userId) {
            // Ajout des détails de l'administrateur
            $adminData = [
                'role' => $role,
                'department' => ($role === 'directeur') ? null : getDepartmentId($department)
            ];
            
            $success = addAdminDetails($userId, $adminData);
            
            if ($success) {
                // Journaliser l'action
                logAction('admin_creation', "Nouvel administrateur créé: $firstname $lastname ($role)", $userId);

                // Redirection vers la page de connexion avec un message de succès
                redirectWithMessage('../index.php', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
            } else {
                throw new Exception('Une erreur est survenue lors de l\'ajout des détails administrateur.');
            }
        } else {
            throw new Exception('Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    } catch (Exception $e) {
        // Journaliser l'erreur
        logError('admin_creation_error', $e->getMessage());
        
        // Afficher l'erreur
        $errorHandler->addError('general', $e->getMessage());
        $errorHandler->handleErrors('Une erreur est survenue lors de l\'inscription');
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la page d'inscription
    header('Location: ../views/register_view.php');
    exit;
}
?>
