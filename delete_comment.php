<?php
session_start();

// On définit le type de réponse comme étant du JSON
header('Content-Type: application/json');

// Vérifie si la requete est bien de type POST et si le comment_id est présent
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
    exit;
}

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['pseudo']) || empty($_SESSION['pseudo'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour supprimer un commentaire']);
    exit;
}

// Récupère l'ID du commentaire et le pseudo de l'utilisateur
$commentId = intval($_POST['comment_id']);
$userPseudo = $_SESSION['pseudo'];

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifie si le commentaire existe
    $stmt = $pdo->prepare("SELECT pseudo FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Commentaire non trouvé']);
        exit;
    }

    // Vérifie si le commentaire appartient à l'utilisateur
    if ($comment['pseudo'] !== $userPseudo) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez supprimer que vos propres commentaires']);
        exit;
    }

    // Suppression du commentaire
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // En cas d'erreur, on log l'erreur et on envoie une réponse JSON
    error_log("Erreur PDO : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
    exit;
}
?>
