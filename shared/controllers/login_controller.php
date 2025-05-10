<?php
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../models/login_model.php';
require_once __DIR__ . '/../models/user_model.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Récupère un utilisateur par son email
 * 
 * @param string $email L'email de l'utilisateur
 * @return array|bool Les données de l'utilisateur ou false si non trouvé
 */
function getUserByEmail($email) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM users 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Erreur lors de la récupération de l\'utilisateur par email: ' . $e->getMessage());
        return false;
    }
}


/**
 * Génère un token pour la fonctionnalité "Se souvenir de moi"
 * 
 * @param int $userId L'ID de l'utilisateur
 * @return string Le token généré
 */
function generateRememberToken($userId) {
    global $pdo;
    
    // Générer un token aléatoire
    $token = bin2hex(random_bytes(32));
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    
    // Stocker le token dans la base de données
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_tokens (user_id, token, expires_at, created_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
        ");
        $stmt->execute([$userId, $hashedToken]);
    } catch (PDOException $e) {
        error_log('Erreur lors de la génération du token: ' . $e->getMessage());
    }
    
    return $token;
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Valider les données
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = 'L\'adresse email est obligatoire';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est obligatoire';
    }
    
    // Si pas d'erreurs, tenter la connexion
    if (empty($errors)) {
        $user = getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Vérifier si le compte est actif
            // if ($user['is_active'] == 0) {
            //     $errors['general'] = 'Votre compte a été désactivé. Veuillez contacter l\'administrateur.';
            // } else {
                // Vérifier si c'est un étudiant et si son compte est validé
                if ($user['user_type'] === 'student') {
                    $accountStatus = getStudentAccountStatus($user['id']);
                    
                    if ($accountStatus && $accountStatus['status'] !== 'approved') {
                        if ($accountStatus['status'] === 'pending') {
                            $errors['general'] = 'Votre compte est en attente de validation par un administrateur. Veuillez patienter.';
                        } elseif ($accountStatus['status'] === 'rejected') {
                            $rejectReason = !empty($accountStatus['rejection_reason']) 
                                ? 'Raison : ' . $accountStatus['rejection_reason'] 
                                : 'Aucune raison spécifiée.';
                            $errors['general'] = 'Votre compte a été rejeté. ' . $rejectReason . ' Veuillez contacter l\'administration pour plus d\'informations.';
                        }
                    } else {
                        // Connexion réussie pour un étudiant validé
                        loginUser($user, $remember);
                    }
                } else {
                    // Connexion réussie pour un non-étudiant
                    loginUser($user, $remember);
                }
            // }
        } else {
            $errors['general'] = 'Email ou mot de passe incorrect';
        }
    }
    
    // S'il y a des erreurs, les stocker dans la session et rediriger
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['login_email'] = $email;
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

/**
 * Connecte l'utilisateur et redirige vers la page appropriée
 * 
 * @param array $user Les données de l'utilisateur
 * @param bool $remember Si l'utilisateur souhaite rester connecté
 */
function loginUser($user, $remember = false) {
    // Mettre à jour la dernière connexion
    updateLastLogin($user['id']);
    
    // Stocker les informations de l'utilisateur dans la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];
    $_SESSION['user_email'] = $user['email'];
    
    // Stocker des informations supplémentaires selon le type d'utilisateur
    if ($user['user_type'] === 'admin') {
        $adminDetails = getAdminDetails($user['id']);
        if ($adminDetails) {
            $_SESSION['admin_role'] = $adminDetails['role'];
            $_SESSION['admin_department'] = $adminDetails['department'];
        }
    } elseif ($user['user_type'] === 'student') {
        $studentDetails = getStudentDetailsByUserId($user['id']);
        if ($studentDetails) {
            $_SESSION['student_matricule'] = $studentDetails['matricule'];
            $_SESSION['student_cycle'] = $studentDetails['cycle'];
            $_SESSION['student_niveau'] = $studentDetails['niveau'];
            $_SESSION['student_specialite'] = $studentDetails['specialite'];
            $_SESSION['student_classe'] = $studentDetails['classe'];
        }
    } elseif ($user['user_type'] === 'teacher') {
        $teacherDetails = getTeacherDetails($user['id']);
        if ($teacherDetails) {
            // Stocker les détails de l'enseignant si nécessaire
        }
    }
    
    // Si l'utilisateur souhaite rester connecté, créer un cookie
    if ($remember) {
        $token = generateRememberToken($user['id']);
        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
    }
    
    // Rediriger vers la page appropriée selon le type d'utilisateur
    switch ($user['user_type']) {
        case 'admin':
            header('Location: /ad/views/dashboard.php');
            break;
        case 'student':
            header('Location: /student/views/dashboard.php');
            break;
        case 'teacher':
            header('Location: /teacher/views/dashboard.php');
            break;
        default:
            header('Location: /index.php');
    }
    exit;
}
?>
