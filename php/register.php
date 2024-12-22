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