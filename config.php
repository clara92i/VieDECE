<?php

// Paramètres de connexion à la base de données
$host = 'localhost';   
$dbname = 'ViedECE';   
$user = 'root';       
$pass = '';            

// Connexion à la base de données
try {
    // Création de l'objet PDO pour se connecter
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);

    // Activer le mode d'affichage des erreurs (utile pour le débogage)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // En cas d'erreur, on arrête le script et on affiche le message d'erreur
    die("Erreur de connexion : " . $e->getMessage());
}
