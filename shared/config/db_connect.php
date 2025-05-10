<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'alertResults';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// Options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Chaîne de connexion DSN
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    // Création de l'instance PDO
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // En cas d'erreur, afficher un message et arrêter le script
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}
?>
