<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête pour vérifier l'utilisateur dans la base de données
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    // Vérifier si l'utilisateur existe et si le mot de passe est correct
    if ($user && password_verify($password, $user['password'])) {
        // Démarrer la session et rediriger vers la page d'accueil
        session_start();
        $_SESSION['user_id'] = $user['id'];
        header('Location: home.php');
        exit;
    } else {
        // Si l'authentification échoue, vous pouvez ajouter un message d'erreur
        $error = "Email ou mot de passe incorrect.";
    }
}

// Inclure le template HTML
require 'view/login.html.php';
