<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['vde_id'], $_POST['pseudo'], $_POST['comment'])) {
        $vde_id = intval($_POST['vde_id']);
        $pseudo = trim($_POST['pseudo']);
        $comment = trim($_POST['comment']);

        if ($pseudo === '') {
            header("Location: add_comment.php?id=$vde_id&error=empty_pseudo");
            exit;
        }
        if ($comment === '') {
            header("Location: add_comment.php?id=$vde_id&error=empty_comment");
            exit;
        }

        $_SESSION['pseudo'] = $pseudo;

        $stmt = $pdo->prepare("INSERT INTO comment (vde_id, pseudo, comment, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$vde_id, $pseudo, $comment])) {
            header("Location: add_comment.php?id=$vde_id");
            exit;
        } else {
            die("Erreur lors de l'insertion du commentaire.");
        }
    } else {
        die("Données du formulaire manquantes.");
    }
}

// Protection d'ID

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID invalide.");
}

// Récupérer la vie d’ECE
$stmt = $pdo->prepare("SELECT * FROM viedece WHERE id = ?");
$stmt->execute([$id]);
$vdece = $stmt->fetch();
if (!$vdece) {
    die("Vie d’ECE non trouvée.");
}

// Récupérer les commentaires pour cette vie
$stmt = $pdo->prepare("SELECT * FROM comment WHERE vde_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

// Récupérer les autres commentaires de ce pseudo sur d'autres vies
$pseudo_session = $_SESSION['pseudo'] ?? '';
if ($pseudo_session !== '') {
    $stmt = $pdo->prepare("SELECT * FROM comment WHERE pseudo = ? AND vde_id != ? ORDER BY created_at DESC");
    $stmt->execute([$pseudo_session, $id]);
    $other_comments = $stmt->fetchAll();
} else {
    $other_comments = [];
}

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Déclaration de l'encodage des caractères pour s'assurer que les caractères spéciaux s'affichent -->
    <meta charset="UTF-8" />
    
    <!-- Titre de la page qui s'affichera dans l'onglet du navigateur -->
    <title>Commentaires - Vie d’ECE</title>
    
    <!-- Inclusion du fichier CSS Bootstrap pour le style et la mise en page -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<!-- Début du corps de la page, avec un fond clair -->
<body class="bg-light">

<!-- Conteneur Bootstrap avec une marge supérieure pour centrer le contenu -->
<div class="container mt-5">

    <!-- Lien pour retourner à la page d'accueil -->
    <a href="index.php" class="btn btn-secondary mb-4">← Retour</a>

    <!-- Carte Bootstrap affichant la Vie d’ECE sélectionnée -->
    <div class="card mb-4">
        <div class="card-header">
            <!-- Affichage du pseudo de l'auteur et de la date de création de la Vie d’ECE -->
            Posté par <?= htmlspecialchars($vdece['pseudo']) ?> le <?= htmlspecialchars($vdece['created_at']) ?>
        </div>
        <div class="card-body">
            <!-- Affichage du contenu de la Vie d’ECE avec les sauts de ligne convertis en <br> -->
            <p class="card-text"><?= nl2br(htmlspecialchars($vdece['content'])) ?></p>
        </div>
    </div>

    <!-- Titre pour la section des commentaires -->
    <h4>Commentaires sur cette vie</h4>

    <!-- Si aucun commentaire n'a été posté -->
    <?php if (empty($comments)): ?>
        <p class="text-muted">Aucun commentaire pour le moment.</p>
    <?php else: ?>
        <!-- Boucle pour afficher chaque commentaire -->
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-2" id="comment-<?= $comment['id'] ?>">
                <div class="card-header d-flex justify-content-between">
                    <!-- Affichage du pseudo et de la date du commentaire -->
                    <span><?= htmlspecialchars($comment['pseudo']) ?> — <?= htmlspecialchars($comment['created_at']) ?></span>
                    
                    <!-- Bouton de suppression uniquement pour l'auteur du commentaire -->
                    <?php if ($pseudo_session === $comment['pseudo']): ?>
                        <button class="btn btn-sm btn-danger btn-delete-comment" data-comment-id="<?= $comment['id'] ?>">Supprimer</button>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <!-- Affichage du contenu du commentaire -->
                    <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Formulaire pour ajouter un nouveau commentaire -->
    <h5 class="mt-5 mb-3">Ajouter un commentaire</h5>

    <!-- Affichage des erreurs si le pseudo ou le commentaire est vide -->
    <?php if ($error === 'empty_comment'): ?>
        <div class="alert alert-danger">Le commentaire ne peut pas être vide.</div>
    <?php elseif ($error === 'empty_pseudo'): ?>
        <div class="alert alert-danger">Le pseudo ne peut pas être vide.</div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de commentaire avec Bootstrap -->
    <form action="add_comment.php" method="POST" class="bg-white p-4 rounded shadow-sm mb-5">
        <!-- Champ caché pour envoyer l'ID de la Vie d’ECE associée au commentaire -->
        <input type="hidden" name="vde_id" value="<?= $vdece['id'] ?>" />

        <!-- Champ pour entrer le pseudo de l'utilisateur -->
        <div class="mb-3">
            <label for="pseudo" class="form-label">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo" class="form-control" required
                   value="<?= htmlspecialchars($pseudo_session) ?>" />
        </div>

        <!-- Champ pour entrer le contenu du commentaire -->
        <div class="mb-3">
            <label for="comment" class="form-label">Commentaire</label>
            <textarea id="comment" name="comment" rows="3" class="form-control" required></textarea>
        </div>

        <!-- Bouton pour envoyer le formulaire -->
        <button type="submit" class="btn btn-success">Envoyer</button>
    </form>

    <!-- Section des autres commentaires de l'utilisateur -->
    <h5>Autres commentaires de <?= htmlspecialchars($pseudo_session ?: 'cet utilisateur') ?></h5>

    <!-- Si l'utilisateur n'a pas d'autres commentaires -->
    <?php if (empty($other_comments)): ?>
        <p class="text-muted">Cet utilisateur n’a pas commenté d’autres vies d’ECE.</p>
    <?php else: ?>
        <!-- Boucle pour afficher chaque commentaire sur d'autres vies d’ECE -->
        <?php foreach ($other_comments as $comment): ?>
            <div class="card mb-2">
                <div class="card-header">
                    <!-- Affichage de la vie d’ECE associée et la date du commentaire -->
                    Sur la vie n°<?= $comment['vde_id'] ?> — <?= htmlspecialchars($comment['created_at']) ?>
                </div>
                <div class="card-body">
                    <!-- Affichage du contenu du commentaire -->
                    <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- JavaScript pour gérer la suppression des commentaires sans recharger la page -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sélectionne tous les boutons de suppression
    document.querySelectorAll('.btn-delete-comment').forEach(button => {
        // Ajoute un événement au clic pour chaque bouton
        button.addEventListener('click', () => {
            // Demande de confirmation avant de supprimer
            if (!confirm('Supprimer ce commentaire ?')) return;

            // Récupère l'ID du commentaire à supprimer
            const commentId = button.getAttribute('data-comment-id');

            // Effectue une requête POST pour supprimer le commentaire
            fetch('delete.comment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'comment_id=' + encodeURIComponent(commentId)
            })
            .then(res => res.json()) // Convertit la réponse en JSON
            .then(data => {
                if (data.success) {
                    // Si la suppression est réussie, retire le commentaire du DOM
                    const el = document.getElementById('comment-' + commentId);
                    if (el) el.remove();
                } else {
                    // Affiche une alerte en cas d'erreur
                    alert(data.message || 'Erreur lors de la suppression');
                }
            })
            .catch(() => alert('Erreur réseau'));
        });
    });
});
</script>

</body>
</html>
