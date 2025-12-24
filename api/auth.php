<?php
// api/auth.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://homecook.wip'); // Angular via Caddy
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/config/database.php';

// Démarrer la session
session_start();

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
        case 'check':
            checkAuth();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action non reconnue'
            ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Vérifier si l'utilisateur est connecté
    checkAuth();
}

function login($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    // Validation basique
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ]);
        return;
    }
    
    try {
        // Requête pour vérifier l'utilisateur
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Stocker les informations dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            
            // Générer un token simple
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
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données'
        ]);
    }
}

function logout() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
}

function checkAuth() {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user_id' => $_SESSION['user_id'],
            'user_email' => $_SESSION['user_email']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
}
?>