<?php
session_start(); // Démarre une session pour suivre l'utilisateur

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
    // Si la connexion échoue, on arrête le script et on affiche le message d'erreur
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du changement de pseudo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pseudo'])) {
    $newPseudo = trim($_POST['pseudo']); // Supprime les espaces autour du pseudo

    // Si le pseudo est valide, on le stocke en session
    if (!empty($newPseudo) && strlen($newPseudo) <= 50) {
        $_SESSION['pseudo'] = $newPseudo;
    }
}

// Pagination
$limit = 5; // Nombre de posts affichés par page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Page actuelle
$start = ($page - 1) * $limit; // Début des résultats

// Requête pour récupérer les Vies d'ECE
$sql = "SELECT * FROM viedece ORDER BY created_at DESC LIMIT :start, :limit";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$vdeces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre total de Vies d'ECE pour la pagination
$sqlCount = "SELECT COUNT(*) FROM viedece";
$totalVdeces = $pdo->query($sqlCount)->fetchColumn();
$totalPages = ceil($totalVdeces / $limit); // Nombre total de pages
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vie d’ECE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center mb-4 text-primary">Vie d’ECE</h1>

    <!-- Message indiquant le pseudo de l'utilisateur connecté -->
    <?php if (isset($_SESSION['pseudo'])): ?>
        <div class="alert alert-info text-center">
            Connecté en tant que <strong><?= htmlspecialchars($_SESSION['pseudo']) ?></strong>
        </div>
    <?php endif; ?>

    <!-- Formulaire pour changer de pseudo -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center">
            <form method="post" class="row justify-content-center g-2">
                <div class="col-auto">
                    <input type="text" name="pseudo" class="form-control" placeholder="Ton pseudo"
                           value="<?= htmlspecialchars($_SESSION['pseudo'] ?? '') ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Changer de pseudo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bouton pour ajouter une Vie d'ECE -->
    <div class="text-center mb-4">
        <a href="add_vdece.php" class="btn btn-success">Ajouter une Vie d’ECE</a>
    </div>

    <!-- Liste des Vies d'ECE -->
    <?php foreach ($vdeces as $vdece): ?>
        <div class="card mb-4 shadow-sm" id="vdece-<?= $vdece['id'] ?>">
            <div class="card-body">
                <!-- Affichage du pseudo de l'auteur et le contenu du post -->
                <h5 class="text-primary"><?= htmlspecialchars(substr($vdece['pseudo'], 0, 50)) ?></h5>
                <p><?= nl2br(htmlspecialchars($vdece['content'])) ?></p>
                <p class="text-muted">
                    <!-- Date de création du post -->
                    <?= (new DateTime($vdece['created_at']))->format('d/m/Y H:i') ?>
                </p>

                <!-- Lien pour voir les commentaires de ce post -->
                <a href="add_comment.php?id=<?= $vdece['id'] ?>" class="btn btn-info">Voir les commentaires</a>

                <!-- Bouton de suppression (seulement si c'est le post de l'utilisateur connecté) -->
                <?php if (isset($_SESSION['pseudo']) && $_SESSION['pseudo'] === $vdece['pseudo']): ?>
                    <button class="btn btn-danger btn-sm ms-2" onclick="deleteVdece(<?= $vdece['id'] ?>)">Supprimer</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="index.php?page=<?= $page - 1 ?>">Précédent</a>
                </li>
            <?php endif; ?>

            <!-- Liens des pages -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="index.php?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="index.php?page=<?= $page + 1 ?>">Suivant</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<!-- JavaScript pour la suppression AJAX d'une Vie d'ECE -->
<script>
function deleteVdece(id) {
    // Demande de confirmation avant de supprimer
    if (!confirm("Veux-tu vraiment supprimer cette Vie d’ECE ?")) return;

    // Envoie de la requête de suppression
    fetch('delete_vdece.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Si la suppression est réussie, on enlève la carte du DOM
            const card = document.getElementById('vdece-' + id);
            if (card) card.remove();
        } else {
            alert('Erreur : ' + (data.message || 'Impossible de supprimer.'));
        }
    })
    .catch(() => alert('Erreur lors de la requête'));
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
