<?php
// On démarre la session pour pouvoir stocker le pseudo de l'utilisateur
session_start();

/*
  Vérifie si le pseudo a été envoyé via le formulaire.
  On utilise `!empty()` pour s'assurer que le champ n'est pas vide.
*/
if (!empty($_POST['pseudo'])) {

    // On supprime les espaces en début et fin du pseudo avec `trim()`
    $pseudo = trim($_POST['pseudo']);

    // On utilise `htmlspecialchars()` pour éviter les problèmes de sécurité (injection HTML)
    $pseudoSecurise = htmlspecialchars($pseudo);

    // On enregistre le pseudo dans la session
    $_SESSION['pseudo'] = $pseudoSecurise;
}

// Redirection vers la page d'accueil
header('Location: index.php');
exit; // On arrête le script ici pour s'assurer que le reste du code n'est pas exécuté
