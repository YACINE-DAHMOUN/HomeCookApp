<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - HomeCook</title>
    <link href="../../assets/css/login.css" rel="stylesheet">
</head>
<body>
    <h1>HomeCook</h1>
    <h2>Connectez-vous :</h2>

    <!-- Affichage de l'erreur si l'authentification échoue -->
    <?php if (isset($error)): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="form">
        <!-- Formulaire de connexion -->
        <form action="login.php" method="post">
            <div id="mail">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div id="mdp">
                <label for="password">Mot de passe</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>