r<?php
// On démarre la session pour accéder aux informations de l'utilisateur connecté
session_start();

// Vérifie si le pseudo est présent dans l'URL
if (!isset($_GET['pseudo'])) {
    die("Aucun pseudo fourni."); // Si non, on arrête le script avec un message d'erreur
}

// On récupère le pseudo depuis l'URL
$pseudo = htmlspecialchars(trim($_GET['pseudo'])); // On nettoie le pseudo pour éviter les erreurs XSS

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on arrête le script et on affiche un message
    die("Erreur de connexion : " . $e->getMessage());
}

// Requête pour récupérer tous les commentaires de ce pseudo
$stmt = $pdo->prepare("
    SELECT * FROM comments 
    WHERE pseudo = :pseudo 
    ORDER BY created_at DESC
");

// On exécute la requête en remplaçant pseudo par la vraie valeur
$stmt->execute([':pseudo' => $pseudo]);

// On récupère tous les commentaires sous forme de tableau associatif
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires de <?= htmlspecialchars($pseudo) ?></title>
    <!-- Bootstrap pour le style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Titre avec le pseudo -->
    <h3>Commentaires de <?= htmlspecialchars($pseudo) ?></h3>

    <!-- Bouton pour retourner à la page principale -->
    <a href="index.php" class="btn btn-secondary mb-3">← Retour</a>

    <!-- Si aucun commentaire n'est trouvé, on affiche un message -->
    <?php if (empty($comments)): ?>
        <p class="text-muted">Aucun commentaire trouvé.</p>
    <?php else: ?>
        <!-- Si des commentaires existent, on les affiche sous forme de cartes Bootstrap -->
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-2">
                <!-- En-tête de la carte : date et ID de la Vie d'ECE associée -->
                <div class="card-header">
                    Posté le <?= htmlspecialchars($comment['created_at']) ?> (Vie d’ECE #<?= htmlspecialchars($comment['vdece_id']) ?>)
                </div>

                <!-- Corps de la carte : contenu du commentaire -->
                <div class="card-body">
                    <!-- On convertit les retours à la ligne en <br> pour garder le formatage -->
                    <p class="card-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>

                    <!-- Lien pour aller voir la Vie d'ECE associée à ce commentaire -->
                    <a href="add_comment.php?id=<?= htmlspecialchars($comment['vdece_id']) ?>" class="btn btn-sm btn-outline-primary">Voir la Vie d’ECE</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
