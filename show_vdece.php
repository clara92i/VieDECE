<?php
// Démarrer la session pour utiliser les informations de l'utilisateur
session_start();

// Vérifie si le formulaire de modification de commentaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment_id'])) {
    // On stocke l'ID du commentaire à modifier dans la session
    $_SESSION['edit_comment'] = (int)$_POST['edit_comment_id'];
}

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Pagination
$limit = 5; // Nombre de commentaires par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Récupération de l'ID de la Vie d'ECE
$vdece_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Requête pour récupérer les commentaires
$stmt = $pdo->prepare("
    SELECT * FROM comments 
    WHERE vdece_id = :vdece_id 
    ORDER BY created_at DESC 
    LIMIT :start, :limit
");

// Liaison des paramètres avec un typage spécifique
$stmt->bindParam(':vdece_id', $vdece_id, PDO::PARAM_INT);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre total de commentaires pour la pagination
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE vdece_id = :vdece_id");
$stmtCount->bindParam(':vdece_id', $vdece_id, PDO::PARAM_INT);
$stmtCount->execute();
$totalPages = ceil($stmtCount->fetchColumn() / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commentaires - Vie d’ECE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="mb-4 text-center">Commentaires de la Vie d’ECE</h1>

    <!-- Formulaire pour ajouter un commentaire -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form id="commentForm" method="post">
                <input type="hidden" name="vdece_id" value="<?= $vdece_id ?>" />
                <div class="mb-3">
                    <label for="pseudo" class="form-label">Pseudo</label>
                    <input type="text" class="form-control" id="pseudo" name="pseudo" 
                           value="<?= htmlspecialchars($_SESSION['pseudo'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Commentaire</label>
                    <textarea class="form-control" name="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer le commentaire</button>
            </form>
        </div>
    </div>

    <!-- Section des commentaires -->
    <div id="commentsSection">
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-3 shadow-sm" data-comment-id="<?= $comment['id'] ?>">
                <div class="card-body">
                    <h5 class="text-primary"><?= htmlspecialchars($comment['pseudo']) ?></h5>

                    <?php 
                    // Si le commentaire est en mode édition
                    if (isset($_SESSION['edit_comment']) && $_SESSION['edit_comment'] == $comment['id']): ?>
                        <form method="post" action="edit_comment.php">
                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>" />
                            <input type="hidden" name="vdece_id" value="<?= $vdece_id ?>" />
                            <textarea name="new_content" class="form-control mb-2" rows="3"><?= htmlspecialchars($comment['content']) ?></textarea>
                            <button type="submit" class="btn btn-success btn-sm">Enregistrer</button>
                            <a href="show_vdece.php?id=<?= $vdece_id ?>" class="btn btn-secondary btn-sm">Annuler</a>
                        </form>
                    <?php else: ?>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    <?php endif; ?>

                    <p class="text-muted"><?= htmlspecialchars($comment['created_at']) ?></p>

                    <!-- Boutons Modifier / Supprimer -->
                    <?php if (isset($_SESSION['pseudo']) && $_SESSION['pseudo'] === $comment['pseudo']): ?>
                        <form method="post" action="show_vdece.php?id=<?= $vdece_id ?>" class="d-inline">
                            <input type="hidden" name="edit_comment_id" value="<?= $comment['id'] ?>" />
                            <button type="submit" class="btn btn-outline-primary btn-sm">Modifier</button>
                        </form>
                        <button class="btn btn-danger btn-sm delete-comment-btn" data-comment-id="<?= $comment['id'] ?>">Supprimer</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?id=<?= $vdece_id ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<!-- JavaScript pour gérer les actions AJAX -->
<script>
document.querySelectorAll('.delete-comment-btn').forEach(button => {
    button.addEventListener('click', function () {
        if (!confirm("Supprimer ce commentaire ?")) return;

        const commentId = this.getAttribute('data-comment-id');
        const formData = new FormData();
        formData.append('comment_id', commentId);

        fetch('delete_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Supprimer le commentaire du DOM
                document.querySelector(`[data-comment-id="${commentId}"]`).remove();
            } else {
                alert(data.message || "Erreur lors de la suppression.");
            }
        })
        .catch(() => alert("Erreur réseau lors de la suppression."));
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
