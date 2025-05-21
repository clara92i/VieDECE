<?php
// On démarre la session pour accéder aux informations de l'utilisateur
session_start();

// On active l'affichage des erreurs (utile en développement, à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informations de connexion à la base de données
$host = 'localhost';
$dbname = 'ViedECE';
$user = 'root';
$password = '';

/*
  Connexion à la base de données
  On utilise un try...catch pour capturer les erreurs éventuelles
*/
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si la connexion échoue, on affiche le message d'erreur et on arrête le script
    die("Erreur de connexion : " . $e->getMessage());
}

// On récupère le pseudo passé en paramètre dans l'URL
$pseudo = $_GET['pseudo'] ?? '';

// Si le pseudo est vide, on arrête le script avec un message d'erreur
if (empty($pseudo)) {
    die("Pseudo manquant.");
}

// Préparer la requête pour récupérer les commentaires associés à ce pseudo
$stmt = $pdo->prepare("
    SELECT * FROM comments 
    WHERE pseudo = :pseudo 
    ORDER BY created_at DESC
");

// On exécute la requête en remplaçant "pseudo" par la valeur réelle
$stmt->execute([':pseudo' => $pseudo]);

// On récupère tous les commentaires sous forme de tableau associatif
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires de <?= htmlspecialchars($pseudo) ?></title>
    <!-- Importer Bootstrap pour le style -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Conteneur principal pour centrer le contenu -->
<div class="container mt-5">

    <!-- Titre de la page : "Commentaires de pseudo" -->
    <h1>Commentaires de <?= htmlspecialchars($pseudo) ?></h1>

    <!-- Si le tableau de commentaires est vide, on affiche un message -->
    <?php if (empty($comments)): ?>
        <p>Aucun commentaire trouvé pour cet utilisateur.</p>
    <?php else: ?>
        <!-- Si on a des commentaires, on les affiche tous dans des cartes Bootstrap -->
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <!-- Affichage du contenu du commentaire avec nl2br pour les sauts de ligne -->
                    <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    <!-- Affichage de la date du commentaire en petit et en gris -->
                    <small class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Lien pour retourner à la page précédente (ou à la page principale) -->
    <a href="show_vdece.php" class="btn btn-secondary mt-3">Retour à tous les commentaires</a>

</div>

</body>
</html>
