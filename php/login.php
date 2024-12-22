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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HomeCook</title>
</head>
<body>
<h1>HomeCook</h1>
<h2>Connectez-vous :</h2>

<!-- Affichage de l'erreur si l'authentification échoue -->
<?php if (isset($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<div class=" form">
    <!-- Formulaire de connexion -->
    <form action="login.php" method="post">
        <div id="mail">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div><br>
        <div id="password">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>
        </div><br>
        
        <div id="button-submit">
            <button type="submit">Se connecter</button>
        </div>
        
    </form>
</div>
<p>Vous n'êtes pas encore membre ? <div id ="button-register"><a href="register.html.php"><button type="submit">Inscrivez-Vous</button> </a></p></div>

</body>
</html>
