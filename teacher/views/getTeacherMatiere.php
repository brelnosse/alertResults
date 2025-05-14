<?php
$host = 'localhost';      // Adresse du serveur de base de données
$dbname = 'alertResults';     // Nom de la base de données
$username = 'root'; // Nom d'utilisateur
$password = '';
header('Content-Type: application/json');

// Vérification de la méthode de requête
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Méthode non autorisée']);
    exit;
}

if(isset($_GET['id_enseignant'])) {
    $enseignant_id = $_GET['id_enseignant'];
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    $query = "SELECT m.id, m.libelle, m.code, m.credit, m.niveau, me.salle, me.status
            FROM matieres m
            INNER JOIN matieres_enseignees me ON m.libelle = me.matiere
            WHERE me.id_enseignant = :id_enseignant";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_enseignant', $enseignant_id, PDO::PARAM_INT);
    $stmt->execute();
    $matieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des matières ont été trouvées
    if ($matieres) {
        // Retourner les matières au format JSON
        echo json_encode($matieres);
        http_response_code(200); // Définir le code de réponse HTTP à 200 OK
    } else {
        // Retourner un JSON indiquant qu'aucune matière n'a été trouvée pour cet enseignant
        echo json_encode(['message' => 'Aucune matière trouvée pour cet enseignant.']);
        http_response_code(200); // Définir le code de réponse HTTP à 200 OK
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'ID enseignant manquant']);
    exit;
}
