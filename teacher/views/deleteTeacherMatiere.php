<?php
// Inclure le fichier de configuration de la base de données

$host = 'localhost';      // Adresse du serveur de base de données
$dbname = 'alertResults';     // Nom de la base de données
$username = 'root'; // Nom d'utilisateur
$password = '';
// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Retourner une erreur JSON si la requête n'est pas de type POST
    echo json_encode(['error' => 'Requête invalide. Veuillez utiliser la méthode POST.']);
    http_response_code(405); // Définir le code de réponse HTTP à 405 Method Not Allowed
    exit;
}

// Vérifier si les paramètres nécessaires sont présents
$required_params = ['id_enseignant', 'libelle', 'credit', 'niveau', 'classe'];
foreach ($required_params as $param) {
    if (!isset($_POST[$param])) {
        // Retourner une erreur JSON si un paramètre est manquant
        echo json_encode(['error' => "Le paramètre '$param' est manquant."]);
        http_response_code(400); // Définir le code de réponse HTTP à 400 Bad Request
        exit;
    }
}

// Récupérer les paramètres de la requête
$id_enseignant = $_POST['id_enseignant'];
$libelle = $_POST['libelle'];
$credit = $_POST['credit'];
$niveau = $_POST['niveau'];
$classe = $_POST['classe']; // Assurez-vous de recevoir également la classe

// Établir une connexion à la base de données
try {
    $db = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    // Retourner une erreur JSON si la connexion à la base de données échoue
    echo json_encode(['error' => 'Erreur de connexion à la base de données : ' . $e->getMessage()]);
    http_response_code(500); // Définir le code de réponse HTTP à 500 Internal Server Error
    exit;
}

// Préparer la requête de suppression
$query = "DELETE FROM matieres_enseignees 
          WHERE id_enseignant = :id_enseignant
          AND matiere IN (SELECT libelle FROM matieres WHERE libelle = :libelle AND credit = :credit AND niveau = :niveau)
          AND salle = :salle"; // Supprimer en utilisant directement la colonne classe

$stmt = $db->prepare($query);
$stmt->bindParam(':id_enseignant', $id_enseignant, PDO::PARAM_INT);
$stmt->bindParam(':libelle', $libelle, PDO::PARAM_STR);
$stmt->bindParam(':credit', $credit, PDO::PARAM_INT);
$stmt->bindParam(':niveau', $niveau, PDO::PARAM_STR);
$stmt->bindParam(':salle', $classe, PDO::PARAM_STR); // Bind le paramètre classe
$stmt->execute();

// Vérifier si la suppression a réussi
if ($stmt->rowCount() > 0) {
    // Retourner un JSON indiquant que la suppression a réussi
    echo json_encode(['message' => 'La matière a été supprimée avec succès.']);
    http_response_code(200); // Définir le code de réponse HTTP à 200 OK
} else {
    // Retourner un JSON indiquant qu'aucune matière correspondant aux critères n'a été trouvée
    echo json_encode(['message' => 'Aucune matière correspondant aux critères n\'a été trouvée.']);
    http_response_code(404); // Définir le code de réponse HTTP à 404 Not Found
}

// Fermer la connexion à la base de données
$db = null;
?>
