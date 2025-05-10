<?php
// Inclure le contrôleur commun
require_once '../../shared/controllers/user_common_controller.php';
require_once '../../shared/models/user_model.php';
require_once '../../shared/models/department_model.php';
require_once '../../shared/models/speciality_model.php';
require_once '../../shared/utils/error_handler.php';

// Initialiser le gestionnaire d'erreurs
$errorHandler = new ErrorHandler();

// Traitement du formulaire d'inscription des étudiants
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupérer et nettoyer les données du formulaire
        $firstname = sanitizeInput($_POST['firstname'] ?? '');
        $lastname = sanitizeInput($_POST['lastname'] ?? '');
        $birthdate = sanitizeInput($_POST['birthdate'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $matricule = sanitizeInput($_POST['matricule'] ?? '');
        $cycle = sanitizeInput($_POST['cycle'] ?? '');
        $niveau = sanitizeInput($_POST['niveau'] ?? '');
        $specialite = sanitizeInput($_POST['specialite'] ?? '');
        $classe = sanitizeInput($_POST['classe'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // // Validation des données
        if (empty($firstname)) {
            echo $errorHandler->addError('firstname', 'Le prénom est obligatoire');
        }
        
        if (empty($lastname)) {
            echo $errorHandler->addError('lastname', 'Le nom est obligatoire');
        }
        
        if (empty($birthdate)) {
            $errorHandler->addError('birthdate', 'La date de naissance est obligatoire');
        } elseif (!checkMinimumAge($birthdate, 15)) {
            echo $errorHandler->addError('birthdate', 'Vous devez avoir au moins 15 ans');
        }
        
        if (empty($phone)) {
            echo $errorHandler->addError('phone', 'Le numéro de téléphone est obligatoire');
        } elseif (!validateCameroonPhone($phone)) {
            echo $errorHandler->addError('phone', 'Le format du numéro de téléphone est invalide (6XXXXXXXX)');
        } elseif (phoneExists($phone)) {
            echo $errorHandler->addError('phone', 'Ce numéro de téléphone est déjà utilisé');
        }
        
        if (empty($email)) {
            echo $errorHandler->addError('email', 'L\'adresse email est obligatoire');
        } elseif (!validateEmail($email)) {
            $errorHandler->addError('email', 'L\'adresse email est invalide');
        } elseif (emailExists($email)) {
            echo $errorHandler->addError('email', 'Cette adresse email est déjà utilisée');
        }
        
        if (empty($matricule)) {
            echo $errorHandler->addError('matricule', 'Le matricule est obligatoire');
        } elseif (matriculeExists($matricule)) {
            echo $errorHandler->addError('matricule', 'Ce matricule est déjà utilisé');
        }
        
        if (empty($cycle)) {
           echo $errorHandler->addError('cycle', 'Le cycle est obligatoire');
        }
        
        if (empty($niveau)) {
           echo $errorHandler->addError('niveau', 'Le niveau est obligatoire');
        }
        
        if (empty($specialite)) {
           echo $errorHandler->addError('specialite', 'La spécialité est obligatoire');
        }
        
        if (empty($classe)) {
           echo $errorHandler->addError('classe', 'La classe est obligatoire');
        }
        
        if (empty($password)) {
            echo $errorHandler->addError('password', 'Le mot de passe est obligatoire');
        } elseif (!validatePassword($password)) {
            echo $errorHandler->addError('password', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre');
        }
        
        if (empty($confirmPassword)) {
            echo $errorHandler->addError('confirmPassword', 'La confirmation du mot de passe est obligatoire');
        } elseif (!passwordsMatch($password, $confirmPassword)) {
            echo $errorHandler->addError('confirmPassword', 'Les mots de passe ne correspondent pas');
        }
        
        // // Validation des combinaisons cycle/niveau/spécialité avec le nouveau système
        if (!empty($cycle) && !empty($niveau) && !empty($specialite)) {
            if (!isValidCycleNiveauSpecialite($cycle, $niveau, $specialite)) {
                echo $errorHandler->addError('specialite', 'La combinaison cycle/niveau/spécialité n\'est pas valide ('.$specialite.' / '.$cycle.')');
            }
        }
        
        // // Si des erreurs sont présentes, les afficher
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
            'user_type' => 'student'
        ];
        
        // // Ajout de l'utilisateur
        $userId = addUser($userData);
        
        if ($userId) {
            // Récupérer l'ID de la spécialité si le nouveau système est utilisé
            $specialiteId = null;
            if (tableExists('specialites')) {
                $specialiteId = getSpecialiteId($specialite);
                if (!$specialiteId) {
                    // Si la spécialité n'existe pas encore, l'ajouter
                    $specialiteId = addSpecialite($specialite);
                }
            }
            
            // Ajout des détails de l'étudiant
            $studentData = [
                'birthdate' => $birthdate,
                'matricule' => $matricule,
                'cycle' => $cycle,
                'niveau' => $niveau,
                'specialite' => $specialite,
                'specialite_id' => $specialiteId,
                'classe' => $classe,
                'status' => 'pending', // Statut initial: en attente de validation
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $success = addStudentDetails($userId, $studentData);
            
            if ($success) {
                // Envoyer un email de confirmation
                require_once '../../shared/utils/mailer.php';
                $mailer = new Mailer();
                $mailer->sendRegistrationEmail($email, $firstname, $lastname, $userData['user_type']);
                
                // Notifier les chefs de département concernés
                notifyDepartmentHeads($specialite, $firstname, $lastname, $email);
                
                // Journaliser l'action
                logAction('student_registration', "Nouvel étudiant inscrit: $firstname $lastname ($specialite)", $userId);
                if(isset($_SESSION['form_data'])){
                    unset($_SESSION['form_data']);
                }
                if(isset($_SESSION['form_errors'])){
                    unset($_SESSION['form_errors']);
                }
                // Redirection vers la page de connexion avec un message de succès
                redirectWithMessage('../index.php', 'Inscription réussie! Votre compte est en attente de validation par un administrateur. Vous recevrez un email lorsque votre compte sera validé.');
            } else {
                echo "Une erreur est survenue lors de l\'ajout des détails étudiant.";
                // throw new Exception('Une erreur est survenue lors de l\'ajout des détails étudiant.');
            }
        } else {
            echo "Une erreur est survenue lors de la création de l'utilisateur.";
            // throw new Exception('Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    } catch (Exception $e) {
        // Journaliser l'erreur
        logError('student_registration_error', $e->getMessage());
        echo "Une erreur est survenue lors de l\'inscription.";
        // Afficher l'erreur
        $errorHandler->addError('general', $e->getMessage());
        $errorHandler->handleErrors('Une erreur est survenue lors de l\'inscription');
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la page d'inscription
    // header('Location: ../views/register_view.php');
    echo "Méthode non autorisée.";
    exit;
}

/**
 * Vérifie si la combinaison cycle/niveau/spécialité est valide
 * Utilise le nouveau système de départements et spécialités si disponible
 */
function isValidCycleNiveauSpecialite($cycle, $niveau, $specialite) {
    // Vérifier si le nouveau système est disponible
    if (tableExists('departement_specialites')) {
        // Utiliser le nouveau système
        $specialiteId = getSpecialiteId($specialite);
        if (!$specialiteId) {
            return false;
        }
        
        // Vérifier si cette spécialité est disponible pour ce cycle et niveau
        return isSpecialiteAvailableForCycleAndNiveau($specialiteId, $cycle, $niveau);
    } else {
        // Utiliser l'ancien système (validation en dur)
        $validCombinations = [
            'prepa-ingenieur' => [
                'niveaux' => ['1', '2'],
                'specialites' => ['prepa 3il']
            ],
            'dut' => [
                'niveaux' => ['1', '2'],
                'specialites' => ['pam', 'rs']
            ],
            'ingenieur' => [
                'niveaux' => ['3', '4', '5'],
                'specialites' => [
                    '3' => ['ingenieur 1'],
                    '4' => ['dev fullstack web', 'data-science', 'robotic'],
                    '5' => ['dev fullstack web', 'data-science', 'robotic']
                ]
            ],
            'licence' => [
                'niveaux' => ['3'],
                'specialites' => ['dev fullstack web', 'data-science', 'robotic']
            ],
            'bts' => [
                'niveaux' => ['1', '2'],
                'specialites' => ['iwd', 'gl', 'rs', 'msi', 'iia']
            ]
        ];
        
        // Vérifier si le cycle est valide
        if (!isset($validCombinations[$cycle])) {
            return false;
        }
        
        // Vérifier si le niveau est valide pour ce cycle
        if (!in_array($niveau, $validCombinations[$cycle]['niveaux'])) {
            return false;
        }
        
        // Vérifier si la spécialité est valide pour ce cycle et niveau
        if ($cycle === 'ingenieur') {
            if (!isset($validCombinations[$cycle]['specialites'][$niveau]) || 
                !in_array($specialite, $validCombinations[$cycle]['specialites'][$niveau])) {
                return false;
            }
        } else {
            if (!in_array($specialite, $validCombinations[$cycle]['specialites'])) {
                return false;
            }
        }
        
        return true;
    }
}

/**
 * Notifie les chefs de département concernés qu'un nouvel étudiant s'est inscrit
 */
function notifyDepartmentHeads($specialite, $firstname, $lastname, $email) {
    //Récupérer les chefs de département concernés par cette spécialité
    $departmentHeads = getDepartmentHeadsBySpecialite($specialite);
    
    if (!empty($departmentHeads)) {
        require_once '../../shared/utils/mailer.php';
        $mailer = new Mailer();
        
        foreach ($departmentHeads as $head) {
            // Envoyer un email à chaque chef de département
            $mailer->sendNewStudentNotification(
                $head['email'],
                $head['firstname'],
                $firstname,
                $lastname,
                $specialite,
                $email
            );
        }
    }
}
?>
