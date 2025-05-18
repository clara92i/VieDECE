<?php
// Démarrer la session pour utiliser le pseudo stocké
session_start();

// Informations de connexion à la base de données
$host = 'localhost';
$dbname = 'ViedECE';
$user = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on arrête le script et on affiche un message
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifie si le pseudo est bien passé en paramètre GET
if (!isset($_GET['pseudo'])) {
    // Si le pseudo n'est pas fourni, on arrête le script
    die("Pseudo non fourni.");
}

// Récupération du pseudo depuis le paramètre GET
$pseudo = htmlspecialchars($_GET['pseudo']); // On protège le pseudo pour éviter les failles XSS

// Préparer la requête pour récupérer les commentaires de cet utilisateur
$stmt = $pdo->prepare("
    SELECT comments.*, viedece.content AS vdece_content, viedece.id AS vdece_id 
    FROM comments 
    JOIN viedece ON comments.vdece_id = viedece.id 
    WHERE comments.pseudo = :pseudo 
    ORDER BY comments.created_at DESC
");

// Exécuter la requête avec le pseudo
$stmt->execute(['pseudo' => $pseudo]);

// Récupérer tous les commentaires sous forme de tableau associatif
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires de <?= $pseudo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <!-- Titre de la page -->
    <h2 class="mb-4 text-center text-primary">Commentaires de <?= $pseudo ?></h2>

    <!-- Si aucun commentaire trouvé, on affiche un message -->
    <?php if (count($comments) === 0): ?>
        <p class="text-center">Aucun commentaire trouvé pour cet utilisateur.</p>
    <?php endif; ?>

    <!-- Parcours des commentaires -->
    <?php foreach ($comments as $comment): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <!-- Date et lien vers la Vie d'ECE associée -->
                <h6 class="card-subtitle mb-2 text-muted">
                    Posté le <?= htmlspecialchars($comment['created_at']) ?> sur 
                    <a href="show_vdece.php?id=<?= $comment['vdece_id'] ?>" class="text-decoration-underline">cette Vie d’ECE</a>
                </h6>

                <!-- Extrait de la Vie d'ECE -->
                <p class="mb-2">
                    <strong>Extrait de la Vie :</strong> 
                    <?= htmlspecialchars(mb_substr($comment['vdece_content'], 0, 80)) ?>...
                </p>

                <!-- Contenu du commentaire -->
                <p class="card-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bouton pour retourner à l'accueil -->
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
    </div>
</div>

</body>
</html>
