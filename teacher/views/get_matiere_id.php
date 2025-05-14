<?php
// Initialiser la session si ce n'est pas déjà fait
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit;
}

// Connexion à la base de données
require_once '../../shared/config/db_connect.php';

// Vérifier si le paramètre libelle est présent
if (!isset($_GET['libelle']) || empty($_GET['libelle'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Libellé de matière manquant']);
    exit;
}

$libelle = $_GET['libelle'];

try {
    // Préparer la requête pour récupérer l'ID de la matière
    $stmt = $pdo->prepare("SELECT id FROM matieres WHERE libelle = ?");
    $stmt->execute([$libelle]);
    
    $matiere = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($matiere) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $matiere['id']]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Matière non trouvée']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>
