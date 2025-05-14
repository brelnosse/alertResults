<?php
session_start();
/**
 * Script de traitement pour l'insertion des matières enseignées
 * Ce fichier reçoit les données du formulaire en AJAX et les insère dans la base de données
 */

// Paramètres de connexion à la base de données
$host = 'localhost';      // Adresse du serveur de base de données
$dbname = 'alertResults';     // Nom de la base de données
$username = 'root'; // Nom d'utilisateur
$password = ''; // Mot de passe

// Entête pour AJAX
header('Content-Type: application/json');

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupération des données JSON envoyées
$postData = file_get_contents('php://input');
$requestData = json_decode($postData, true);

// Vérification si les données sont valides
if (!$requestData || !is_array($requestData) || empty($requestData)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

// ID de l'enseignant (à récupérer de la session ou d'un paramètre)
// Dans un système réel, cette valeur viendrait probablement de la session de l'utilisateur connecté
$id_enseignant = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Valeur par défaut pour l'exemple

try {
    // Connexion à la base de données avec PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Préparation de la requête d'insertion
    $stmt = $pdo->prepare("
        INSERT INTO matieres_enseignees (
            id_enseignant, 
            niveau, 
            salle, 
            matiere,
            notes, 
            identifiant_bloc
        ) VALUES (
            :id_enseignant,
            :niveau,
            :salle,
            :matiere,
            :notes,
            :identifiant_bloc
        )
    ");
    
    // Compteurs pour les statistiques
    $insertedCount = 0;
    $errors = [];
    
    // Traitement de chaque entrée
    foreach ($requestData as $item) {
        // Vérification des champs requis
        if (empty($item['niveau']) || empty($item['salle']) || empty($item['matiere']) || empty($item['id'])) {
            $errors[] = "Données incomplètes pour le bloc " . ($item['id'] ?? 'inconnu');
            continue;
        }
        
        try {
            // Exécution de la requête avec les paramètres
            $success = $stmt->execute([
                ':id_enseignant' => $id_enseignant,
                ':niveau' => $item['niveau'],
                ':salle' => $item['salle'],
                ':matiere' => $item['matiere'],
                ':notes' => $item['notes'] ?? '',
                ':identifiant_bloc' => $item['id']
            ]);
            
            if ($success) {
                $insertedCount++;
            }
        } catch (PDOException $e) {
            // Gestion des erreurs spécifiques
            if ($e->getCode() == '23000') { // Code d'erreur pour violation de contrainte d'unicité
                $errors[] = "Conflit d'horaire détecté pour {$item['matiere']} (Niveau {$item['niveau']}, Salle {$item['salle']}, {$item['jour']} à {$item['heure']})";
            } else {
                $errors[] = "Erreur lors de l'insertion de {$item['matiere']} : " . $e->getMessage();
            }
        }
    }
    
    // Préparation de la réponse
    $response = [
        'success' => count($errors) === 0,
        'message' => $insertedCount . " matière(s) enregistrée(s) avec succès"
    ];
    
    // Ajout des erreurs éventuelles à la réponse
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    // Envoi de la réponse au format JSON
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Gestion des erreurs générales de base de données
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
    ]);
}
?>