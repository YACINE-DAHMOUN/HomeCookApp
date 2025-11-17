<?php
require_once __DIR__ . '/../includes/navigation.php';
require_once __DIR__ . '/../config/database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: AuthController.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur (y compris prénom et nom)
try {
    $query = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = :user_id");
    $query->execute(['user_id' => $user_id]);
    $user = $query->fetch();

    // Vérifier si l'utilisateur existe
    if (!$user) {
        echo "Utilisateur non trouvé.";
        exit;
    }

    // Récupérer le prénom et le nom de l'utilisateur
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];
} catch (PDOException $e) {
    error_log("Erreur de récupération des informations de l'utilisateur : " . $e->getMessage());
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

    <h1>Bonjour, <?= htmlspecialchars($last_name) ?> !</h1>
    <h2>Bienvenus sur HomeCook</h2>

</body>

</html>