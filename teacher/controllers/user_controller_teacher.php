<?php
// Inclure le contrôleur commun
require_once '../../shared/controllers/user_common_controller.php';
require_once '../../shared/models/user_model.php';
require_once '../../shared/utils/error_handler.php';

// Initialiser le gestionnaire d'erreurs
$errorHandler = new ErrorHandler();

// Traitement du formulaire d'inscription des enseignants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et nettoyer les données du formulaire
        $firstname = sanitizeInput($_POST['firstname'] ?? '');
        $lastname = sanitizeInput($_POST['lastname'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
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
            'user_type' => 'teacher'
        ];
        
        // Ajout de l'utilisateur
        $userId = addUser($userData);
        
        if ($userId) {
            // Ajout des détails de l'enseignant
            $teacherData = [
                'status' => 'active', // Les enseignants sont actifs par défaut
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $success = addTeacherDetails($userId, $teacherData);
            
            if ($success) {
                // Ajouter les spécialités de l'enseignant
                
                // Envoyer un email de confirmation
                require_once '../../shared/utils/mailer.php';
                $mailer = new Mailer();
                $mailer->sendRegistrationEmail($email, $firstname, $lastname, $userData['user_type']);
                
                // Journaliser l'action
                logAction('teacher_registration', "Nouvel enseignant inscrit: $firstname $lastname", $userId);
                // Redirection vers la page de connexion avec un message de succès
                redirectWithMessage('../index.php', 'Inscription réussie! Vous pouvez maintenant vous connecter.');
            } else {
                throw new Exception('Une erreur est survenue lors de l\'ajout des détails enseignant.');
            }
        } else {
            throw new Exception('Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    } catch (Exception $e) {
        // Journaliser l'erreur
        logError('teacher_registration_error', $e->getMessage());
        
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
