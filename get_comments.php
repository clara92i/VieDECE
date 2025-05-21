<?php

// Inclusion du fichier de configuration pour la connexion à la base de données
require 'config.php';

// Récupération de l'ID de la VdECE depuis le paramètre GET
// On s'assure que c'est bien un entier pour éviter les erreurs
$vde_id = isset($_GET['vde_id']) ? (int)$_GET['vde_id'] : 0;

// Si aucun ID n'est fourni, on arrête le script
if ($vde_id === 0) {
    echo "Aucune VdECE sélectionnée.";
    exit;
}

// Requête SQL pour récupérer les commentaires associés à cette VdECE
$sql = "SELECT * FROM comment WHERE vde_id = ? ORDER BY created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$vde_id]);

// Récupération des commentaires sous forme de tableau
$comments = $stmt->fetchAll();

// Vérification de l'existence de commentaires
if (count($comments) === 0) {
    echo "Aucun commentaire pour cette VdECE.";
} else {
    // Parcours des commentaires
    foreach ($comments as $comment) {
        // Affichage de chaque commentaire
        echo "<div class='border p-2 mb-2'>";
        echo "<strong>" . htmlspecialchars($comment['pseudo']) . "</strong><br>";
        echo nl2br(htmlspecialchars($comment['comment'])) . "<br>";
        echo "<small class='text-muted'>" . $comment['created_at'] . "</small>";
        echo "</div>";
    }
}
