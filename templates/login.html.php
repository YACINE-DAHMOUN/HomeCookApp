<style>
    @import url('../../assets/css/login.css');
</style>

<h1>HomeCook</h1>
<h2>Connectez-vous :</h2>

<!-- Affichage de l'erreur si l'authentification échoue -->
<?php if (isset($error)): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<div class="form">
    <!-- Formulaire de connexion -->
    <form action="src/controllers/AuthController.php" method="post">
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