<?php

// Affichage des erreurs (à retirer en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

$error = "";

// Vérification du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo']);
    $content = trim($_POST['content']);

    // Vérifications simples
    if (strlen($pseudo) > 50) {
        $error = "Le pseudo ne doit pas dépasser 50 caractères.";
    } elseif (empty($pseudo) || empty($content)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO viedece (pseudo, content, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$pseudo, $content]);

            // Stocker le pseudo pour le réutiliser
            $_SESSION['pseudo'] = $pseudo;
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement.";
        }
    }
}
?>

<!DOCTYPE html>
<!-- Déclaration de la langue de la page : français -->
<html lang="fr">
<head>
    
    <meta charset="UTF-8"> <!-- Encodage des caractères pour gérer les accents et caractères spéciaux -->
    
    <!-- Titre de la page qui apparaîtra dans l'onglet du navigateur -->
    <title>Ajouter une Vie d’ECE</title>

    <!-- Lien vers la bibliothèque Bootstrap pour styliser le formulaire (boutons, cartes, etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<!-- Corps de la page, avec un fond clair (bg-light) -->
<body class="bg-light">

<!-- Conteneur Bootstrap pour centrer et structurer le contenu -->
<div class="container mt-5">
    
    <!-- Titre principal, centré et coloré en vert (text-success) -->
    <h1 class="text-center text-success">Ajouter une Vie d’ECE</h1>

    <!-- Si une erreur est présente (par exemple si un champ est vide ou trop long), on l'affiche ici -->
    <?php if ($error): ?>
        <!-- Affichage de l'erreur dans une alerte rouge -->
        <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
    <?php endif; ?>

    <!-- Formulaire pour ajouter une Vie d’ECE -->
    <form method="post" class="bg-white p-4 rounded shadow-sm"> 
        <!-- Le formulaire est de couleur blanche (bg-white), avec des bordures arrondies (rounded) et une ombre légère (shadow-sm) -->
        
        <!-- Champ pour le pseudo -->
        <div class="mb-3">
            <label for="pseudo">Pseudo (max 50 caractères)</label>
            <!-- Champ de texte pour entrer le pseudo -->
            <!-- La valeur par défaut est le pseudo stocké en session ou vide s'il n'y en a pas -->
            <input type="text" class="form-control" name="pseudo" maxlength="50" value="<?= htmlspecialchars($_SESSION['pseudo'] ?? '') ?>" required>
        </div>

        <!-- Champ pour le contenu de la Vie d’ECE -->
        <div class="mb-3">
            <label for="content">Vie d’ECE</label>
            <!-- Zone de texte pour écrire l'anecdote -->
            <textarea class="form-control" name="content" rows="5" required></textarea>
        </div>

        <!-- Bouton pour soumettre le formulaire -->
        <button type="submit" class="btn btn-success">Publier</button>

        <!-- Lien pour annuler et revenir à l'accueil -->
        <a href="index.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

</body>
</html>
