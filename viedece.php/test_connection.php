<?php
// Paramètres de connexion à la base de données
$host = 'localhost';  // Hôte (localhost car tu utilises XAMPP)
$user = 'root';       // Nom d'utilisateur par défaut pour XAMPP
$pass = '';           // Mot de passe par défaut est vide pour XAMPP
$dbname = 'ViedECE';  // Le nom de ta base de données

// Connexion à la base de données MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
} else {
    echo "Connexion réussie à la base de données " . $dbname;
}

// Fermer la connexion
$conn->close();
?>
