<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/navigation.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: AuthController.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
try {
    $query = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = :user_id");
    $query->execute(['user_id' => $user_id]);
    $user = $query->fetch();

    if (!$user) {
        echo "Utilisateur non trouvé.";
        exit;
    }

    $first_name = $user['first_name'];
    $last_name = $user['last_name'];

} catch (PDOException $e) {
    error_log("Erreur de récupération des informations de l'utilisateur : " . $e->getMessage());
    exit;
}

// Fonction de traduction améliorée
function translateToEnglish($name_fr) {
    global $pdo;

    // 1. Vérifier d'abord dans la base de données existante
    $query = $pdo->prepare("SELECT name_en FROM ingredient_translations WHERE name_fr = :name_fr");
    $query->execute(['name_fr' => $name_fr]);
    $existing = $query->fetch();
    if ($existing) {
        return $existing['name_en'];
    }

    // 2. Dictionnaire local complet
    $translations = [
        // Fruits
        "tomate" => "tomato",
        "pomme" => "apple", 
        "banane" => "banana",
        "orange" => "orange",
        "fraise" => "strawberry",
        "raisin" => "grape",
        "citron" => "lemon",
        "pamplemousse" => "grapefruit",
        "kiwi" => "kiwi",
        
        // Légumes
        "carotte" => "carrot",
        "oignon" => "onion", 
        "poireau" => "leek",
        "haricot" => "bean",
        "petit pois" => "pea",
        "courgette" => "zucchini",
        "aubergine" => "eggplant",
        "épinard" => "spinach",
        "poivron" => "bell pepper",
        
        // Protéines
        "poulet" => "chicken",
        "bœuf" => "beef", 
        "porc" => "pork",
        "saumon" => "salmon",
        "thon" => "tuna",
        "œuf" => "egg",
        
        // Produits laitiers
        "lait" => "milk",
        "beurre" => "butter",
        "fromage" => "cheese",
        "yaourt" => "yogurt",
        
        // Céréales et féculents
        "pain" => "bread",
        "riz" => "rice",
        "pâtes" => "pasta",
        "pomme de terre" => "potato",
        "farine" => "flour",
        
        // Assaisonnements et condiments
        "sel" => "salt",
        "poivre" => "pepper",
        "sucre" => "sugar",
        "huile" => "oil",
        "eau" => "water",
        "vinaigre" => "vinegar",
        "sauce" => "sauce",
        
        // Herbes et épices
        "persil" => "parsley",
        "basilic" => "basil",
        "thym" => "thyme",
        "romarin" => "rosemary"
    ];

    $name_lower = mb_strtolower($name_fr, 'UTF-8');
    
    // Correspondance exacte dans le dictionnaire
    if (isset($translations[$name_lower])) {
        $translated = $translations[$name_lower];
        insertTranslation($name_fr, $translated);
        return $translated;
    }

    // Correspondance partielle dans le dictionnaire
    foreach ($translations as $french => $english) {
        if (strpos($name_lower, $french) !== false) {
            insertTranslation($name_fr, $english);
            return $english;
        }
    }

    // 3. Traduction via LibreTranslate pour ce qui n'est pas dans le dictionnaire
    $url = 'https://libretranslate.de/translate';
    $data = [
        'q' => $name_fr,
        'source' => 'fr',
        'target' => 'en'
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response) {
        $result = json_decode($response, true);
        $translated = $result['translatedText'] ?? $name_fr;
        
        
        insertTranslation($name_fr, $translated);
        
        return $translated;
    }

    // Dernier recours : retourner le nom original
    return $name_fr;
}

// Fonction pour insérer la traduction dans ingredient_translations
function insertTranslation($name_fr, $name_en) {
    global $pdo;
    
    
    $query = $pdo->prepare("SELECT id FROM ingredient_translations WHERE name_fr = :name_fr AND name_en = :name_en");
    $query->execute(['name_fr' => $name_fr, 'name_en' => $name_en]);
    $translation = $query->fetch();
    
    // Si la traduction n'existe pas, l'insérer
    if (!$translation) {
        $query = $pdo->prepare("INSERT INTO ingredient_translations (name_fr, name_en) VALUES (:name_fr, :name_en)");
        $query->execute(['name_fr' => $name_fr, 'name_en' => $name_en]);
        return $pdo->lastInsertId();
    }
    
    return $translation['id'];
}

// Traitement de l'ajout d'ingrédient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : "";
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : "";
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : "";

    $errors = [];
    if (empty($name)) {
        $errors[] = "Le nom de l'ingrédient est obligatoire";
    }

    if (empty($quantity)) {
        $errors[] = "La quantité est obligatoire";
    }

    if (empty($unit)) {
        $errors[] = "L'unité est obligatoire";
    }

    if (empty($errors)) {
        try {
            // Traduire le nom de l'ingrédient en anglais
            $translated_name = translateToEnglish($name);
            
            // Insérer l'ingrédient dans la table ingredients
            $query = $pdo->prepare("INSERT INTO ingredients (name_fr, name_en, quantity, unit, user_id, created_at) 
                                    VALUES (:name_fr, :name_en, :quantity, :unit, :user_id, NOW())");
            $query->execute([
                'name_fr' => $name,
                'name_en' => $translated_name,
                'quantity' => $quantity,
                'unit' => $unit,
                'user_id' => $user_id
            ]);

            
            header('Location: profil.php');
            exit;

        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de l'ingrédient : " . $e->getMessage());
            $errors[] = "Erreur lors de l'ajout de l'ingrédient";
        }
    }
}

// Récupérer la liste des ingrédients de l'utilisateur connecté
try {
    $query = $pdo->prepare("SELECT * FROM ingredients WHERE user_id = :user_id ORDER BY created_at DESC");
    $query->execute(['user_id' => $user_id]);
    $ingredients = $query->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur de récupération des ingrédients : " . $e->getMessage());
    $ingredients = [];
}

// Inclure le template HTML
require __DIR__ . '/../../templates/profil.html.php';
?>

