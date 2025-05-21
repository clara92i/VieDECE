<?php
// Démarrage de la session pour stocker le pseudo de l'utilisateur
session_start();

/*
  Connexion à la base de données avec gestion des erreurs.
  Si la connexion échoue, on arrête le script avec `die()` et on affiche le message d'erreur.
*/
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ViedECE;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage()); // Affiche l'erreur et stoppe le script
}

/*
  Vérifie si le formulaire a été soumis en méthode POST.
  Cela permet d'éviter que le traitement s'exécute si la page est simplement visitée.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupère et nettoie les champs `pseudo` et `content`
    $pseudo = trim($_POST['pseudo']);  // Enlève les espaces au début et à la fin
    $content = trim($_POST['content']); // Enlève les espaces au début et à la fin

    /*
      Si le pseudo est vide on attribue "Anonyme"
      Sinon on stocke le pseudo dans la session pour le réutiliser plus tard.
    */
    if (empty($pseudo)) {
        $pseudo = 'Anonyme';  // Pseudo par défaut
    } else {
        // Sécurise le pseudo contre les failles XSS et le stocke en session
        $_SESSION['pseudo'] = htmlspecialchars($pseudo); 
    }

    /*
      Si le contenu est vide, on redirige vers le formulaire avec un message d'erreur.
    */
    if (empty($content)) {
        header("Location: add_vdece.php?error=empty_content");
        exit(); // Stoppe le script après la redirection
    }

    /*
      Prépare et exécute la requête d'insertion.
      Les "pseudo" et "content" sont des paramètres protégés contre les injections SQL.
    */
    $stmt = $pdo->prepare("
        INSERT INTO vdece (pseudo, content, created_at) 
        VALUES (:pseudo, :content, NOW())
    ");
    $stmt->execute([
        ':pseudo' => htmlspecialchars($pseudo),  // Sécurise le pseudo
        ':content' => htmlspecialchars($content)  // Sécurise le contenu
    ]);

    // Redirige vers la page d'accueil
    header("Location: viedece.php");
    exit();
}
?>
