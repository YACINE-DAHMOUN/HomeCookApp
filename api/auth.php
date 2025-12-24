<?php
require_once __DIR__ . '/../config/database.php';
// api/auth.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:4200'); // Pour Angular en dev
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Pour gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées par Angular
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'login':
            login($pdo, $input);
            break;
        case 'logout':
            logout();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
}

function login($pdo, $data) {
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    // Validation basique
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ]);
        return;
    }
    
    // Requête pour vérifier l'utilisateur
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérifier si l'utilisateur existe et si le mot de passe est correct
    if ($user && password_verify($password, $user['password'])) {
        // Démarrer la session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        
        // Générer un token simple (pour une vraie app, utilisez JWT)
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;
        
        // Retourner les données utilisateur (sans le mot de passe !)
        unset($user['password']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
    }
}

function logout() {
    session_start();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
}
?>