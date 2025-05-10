<?php
// Inclure le modèle utilisateur
// require_once '../models/user_model.php';

/**
 * Valide un email
 * 
 * @param string $email L'email à valider
 * @return bool True si l'email est valide, false sinon
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide un numéro de téléphone camerounais
 * 
 * @param string $phone Le numéro à valider
 * @return bool True si le numéro est valide, false sinon
 */
function validateCameroonPhone($phone) {
    return preg_match('/^6[0-9]{8}$/', $phone) === 1;
}

/**
 * Valide un mot de passe (au moins 8 caractères, une majuscule, une minuscule et un chiffre)
 * 
 * @param string $password Le mot de passe à valider
 * @return bool True si le mot de passe est valide, false sinon
 */
function validatePassword($password) {
    // Au moins 8 caractères, une majuscule, une minuscule et un chiffre
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
}

/**
 * Vérifie si les mots de passe correspondent
 * 
 * @param string $password Le mot de passe
 * @param string $confirmPassword La confirmation du mot de passe
 * @return bool True si les mots de passe correspondent, false sinon
 */
function passwordsMatch($password, $confirmPassword) {
    return $password === $confirmPassword;
}

/**
 * Nettoie les données d'entrée
 * 
 * @param string $data Les données à nettoyer
 * @return string Les données nettoyées
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Vérifie l'âge minimum (pour les étudiants)
 * 
 * @param string $birthdate La date de naissance au format YYYY-MM-DD
 * @param int $minAge L'âge minimum requis
 * @return bool True si l'âge est suffisant, false sinon
 */
function checkMinimumAge($birthdate, $minAge = 15) {
    $today = new DateTime();
    $birth = new DateTime($birthdate);
    $age = $today->diff($birth)->y;
    
    return $age >= $minAge;
}

// /**
//  * Génère une réponse JSON
//  * 
//  * @param bool $success Indique si l'opération a réussi
//  * @param string $message Le message à afficher
//  * @param array $data Les données supplémentaires (optionnel)
//  */
// function jsonResponse($success, $message, $data = []) {
//     header('Content-Type: application/json');
//     echo json_encode([
//         'success' => $success,
//         'message' => $message,
//         'data' => $data
//     ]);
//     exit;
// }

/**
 * Redirige vers une page avec un message
 * 
 * @param string $url L'URL de redirection
 * @param string $message Le message à afficher
 * @param string $type Le type de message (success, error)
 */
function redirectWithMessage($url, $message, $type = 'success') {
    session_start();
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
    
    header("Location: $url");
    exit;
}
?>
