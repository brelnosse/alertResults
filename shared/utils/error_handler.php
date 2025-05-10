<?php
/**
 * Classe pour gérer les erreurs de manière centralisée
 */
class ErrorHandler {
    private $errors = [];
    
    /**
     * Ajoute une erreur au tableau des erreurs
     * 
     * @param string $field Le champ concerné par l'erreur
     * @param string $message Le message d'erreur
     */
    public function addError($field, $message) {
        $this->errors[$field] = $message;
    }
    
    /**
     * Vérifie si des erreurs sont présentes
     * 
     * @return bool True si des erreurs sont présentes, false sinon
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Récupère toutes les erreurs
     * 
     * @return array Le tableau des erreurs
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Gère les erreurs en fonction du contexte (AJAX ou non)
     * 
     * @param string $generalMessage Le message général d'erreur
     */
    public function handleErrors($generalMessage = 'Des erreurs sont survenues') {
        // Vérifier si la requête est une requête AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            // Réponse JSON pour les requêtes AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $generalMessage,
                'errors' => $this->errors
            ]);
            exit;
        } else {
            // Pour les requêtes normales, stocker les erreurs en session et rediriger
            if (!session_id()) {
                session_start();
            }
            
            $_SESSION['form_errors'] = $this->errors;
            $_SESSION['form_error_message'] = $generalMessage;
            $_SESSION['form_data'] = $_POST; // Conserver les données du formulaire
            
            // Rediriger vers la page précédente
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $referer");
            // var_dump($this->errors);
            exit;
        }
    }
    
    /**
     * Affiche les erreurs dans un formulaire
     * 
     * @param string $field Le champ pour lequel afficher l'erreur
     * @return string Le message d'erreur HTML ou une chaîne vide
     */
    public static function displayError($field) {
        if (!session_id()) {
            session_start();
        }
        
        if (isset($_SESSION['form_errors'][$field])) {
            $error = $_SESSION['form_errors'][$field];
            return "<div class=\"invalid-feedback d-block\">$error</div>";
        }
        
        return '';
    }
    
    /**
     * Récupère la valeur précédemment soumise pour un champ
     * 
     * @param string $field Le nom du champ
     * @param string $default La valeur par défaut si aucune valeur n'est trouvée
     * @return string La valeur du champ
     */
    public static function getOldValue($field, $default = '') {
        if (!session_id()) {
            session_start();
        }
        
        if (isset($_SESSION['form_data'][$field])) {
            return htmlspecialchars($_SESSION['form_data'][$field]);
        }
        
        return $default;
    }
    
    /**
     * Vérifie si un champ a une erreur
     * 
     * @param string $field Le nom du champ
     * @return bool True si le champ a une erreur, false sinon
     */
    public static function hasError($field) {
        if (!session_id()) {
            session_start();
        }
        
        return isset($_SESSION['form_errors'][$field]);
    }
    
    /**
     * Affiche le message d'erreur général
     * 
     * @return string Le message d'erreur HTML ou une chaîne vide
     */
    public static function displayGeneralError() {
        if (!session_id()) {
            session_start();
        }
        
        if (isset($_SESSION['form_error_message'])) {
            $message = $_SESSION['form_error_message'];
            unset($_SESSION['form_error_message']);
            return "<div class=\"alert alert-danger\">$message</div>";
        }
        
        return '';
    }
    
    /**
     * Nettoie les erreurs en session
     */
    public static function clearErrors() {
        if (!session_id()) {
            session_start();
        }
        
        unset($_SESSION['form_errors']);
        unset($_SESSION['form_error_message']);
        unset($_SESSION['form_data']);
    }
}

/**
 * Fonction utilitaire pour journaliser les erreurs
 * 
 * @param string $type Le type d'erreur
 * @param string $message Le message d'erreur
 * @param array $context Contexte supplémentaire
 */
function logError($type, $message, $context = []) {
    $logFile = __DIR__ . '/../../logs/errors.log';
    $logDir = dirname($logFile);
    
    // Créer le répertoire de logs s'il n'existe pas
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Formater le message de log
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logMessage = "[$timestamp] [$type] $message$contextStr" . PHP_EOL;
    
    // Écrire dans le fichier de log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Fonction utilitaire pour journaliser les actions
 * 
 * @param string $type Le type d'action
 * @param string $message Description de l'action
 * @param int $userId ID de l'utilisateur qui a effectué l'action
 * @param array $context Contexte supplémentaire
 */
function logAction($type, $message, $userId = null, $context = []) {
    $logFile = __DIR__ . '/../../logs/actions.log';
    $logDir = dirname($logFile);
    
    // Créer le répertoire de logs s'il n'existe pas
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Formater le message de log
    $timestamp = date('Y-m-d H:i:s');
    $userStr = $userId ? " [User: $userId]" : '';
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logMessage = "[$timestamp] [$type]$userStr $message$contextStr" . PHP_EOL;
    
    // Écrire dans le fichier de log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?>
