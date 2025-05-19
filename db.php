<?php
$host = 'localhost';
$dbname = 'ViedECE';       
$user = 'root';            
$password = '';            

// Bloc try pour gérer la connexion à la base de données
try {
    // Création d'un objet PDO pour se connecter à la base de données
    // "mysql:host=localhost" indique qu'on se connecte à un serveur MySQL en local
    // "charset=utf8" permet de gérer correctement les accents et caractères spéciaux
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    
    // Configuration de PDO pour afficher les erreurs en mode Exception
    // Ça permet d'attraper les erreurs dans le bloc catch
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // En cas d'erreur de connexion on affiche un message d'erreur et on arrête le script
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
