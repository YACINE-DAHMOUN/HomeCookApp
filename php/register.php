<?php
include 'header.php';
include 'footer.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm_password =password_hash($_POST['password_confirm'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (first_name, last_name, email, password, confirm_password) VALUES (:first_name, :last_name, :email, :password, :confirm_password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':email' => $email,
        ':password' => $password,
        ':confirm_password' => $confirm_password
    ]);

    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCook</title>
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