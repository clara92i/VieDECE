<?php
// Démarre la session pour accéder aux informations de l'utilisateur connecté
session_start();

// On indique que le contenu de la réponse sera en JSON
header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté (son pseudo est stocké en session)
if (!isset($_SESSION['pseudo'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit; // On arrête le script
}

// Vérifie que l'ID du post a bien été envoyé via POST
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit; // On arrête le script
}

// Récupère l'ID du post et le pseudo de l'utilisateur connecté
$id = intval($_POST['id']); // On convertit en entier pour plus de sécurité
$pseudo = $_SESSION['pseudo']; // On récupère le pseudo de l'utilisateur connecté

// Connexion à la base de données
$host = 'localhost';
$dbname = 'ViedECE';
$user = 'root';
$password = '';

try {
    // On essaie de se connecter à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, on envoie un message d'erreur au format JSON
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données.']);
    exit;
}

// On vérifie que la Vie d'ECE existe bien et que l'utilisateur en est l'auteur
$stmt = $pdo->prepare("SELECT pseudo FROM viedece WHERE id = :id");
$stmt->execute(['id' => $id]);
$vdece = $stmt->fetch(PDO::FETCH_ASSOC);

// Si aucun post n'a été trouvé
if (!$vdece) {
    echo json_encode(['success' => false, 'message' => 'Vie d’ECE non trouvée.']);
    exit;
}

// Si l'utilisateur n'est pas l'auteur du post
if ($vdece['pseudo'] !== $pseudo) {
    echo json_encode(['success' => false, 'message' => 'Tu ne peux supprimer que tes propres posts.']);
    exit;
}

// Suppression du post
$stmt = $pdo->prepare("DELETE FROM viedece WHERE id = :id");
$stmt->execute(['id' => $id]);

// Réponse JSON confirmant la suppression
echo json_encode(['success' => true]);
