<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCook</title>
    <link href="../../assets/css/register.css" rel="stylesheet">
</head>
<body>
<h1>HomeCook</h1>
<h2>Inscription :</h2>
<div>
    <form action="register.php" method="post">  <!--formlaire d'inscription-->
    <div>
         <label for="first_name">Nom</label>
         <input type="text" name="first_name" id="first_name" required>
    </div>
    <div>
         <label for="last_name">Prénom</label>
         <input type="text" name="last_name" id="last_name" required>
    </div>
    <div>
         <label for="email">Email</label>
         <input type="email" name="email" id="email" required>
    </div>
    <div>
         <label for="password">Mot de passe</label>
         <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="password_confirm">Confirmer le mot de passe</label>
        <input type="password" name="password_confirm" id="password_confirm" required>
    </div>
         <button type="submit">S'inscrire</button>
    </form>
</div>
</body>
</html>