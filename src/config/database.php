<?php
// Paramètres de connexion à la base de données
define ('DB_HOST', 'localhost') ; // L'adresse de ton serveur de base de données
define ('DB_NAME', 'homecook'); // Le nom de ta base de données
define ('DB_USER', 'root'); // Nom d'utilisateur de la base de données (par défaut 'root' en local)
define ('DB_PASS', 'root'); // Mot de passe pour l'utilisateur (vide en local pour XAMPP/MAMP)

try {
    // Créer une nouvelle instance de PDO pour se connecter à la base de données
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Définir les options de PDO pour gérer les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Affichage d'un message de succès (optionnel)
    // echo "Connexion réussie à la base de données.";
    
} catch (PDOException $e) {
    // Si la connexion échoue, afficher l'erreur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Erreur de connexion à la base de données : " . $e->getMessage());

}
?>
