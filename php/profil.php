<?php
require_once 'db.php';
include 'nav.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
        
        // Insérer la nouvelle traduction
        insertTranslation($name_fr, $translated);
        
        return $translated;
    }

    // Dernier recours : retourner le nom original
    return $name_fr;
}

// Fonction pour insérer la traduction dans ingredient_translations
function insertTranslation($name_fr, $name_en) {
    global $pdo;
    
    // Vérifier si la traduction existe déjà
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

            // Rediriger pour éviter l'ajout multiple après actualisation
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpCook - Profil</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        #ingredients-list {
            display: none;
        }
        .error {
            color: red;
            background-color: #ffeeee;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            color: green;
            background-color: #eeffee;
            padding: 10px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        form {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        #toggle-ingredients {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<h1>HelpCook</h1>
<h2>Bonjour, <?= htmlspecialchars($last_name) ?> !</h2>

<h2>Ajoutez un ingrédient :</h2>

<?php if (!empty($errors)) : ?>
    <div class="error">
        <?php foreach ($errors as $error) : ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="profil.php" method="post">
    <div>
        <label for="name">Nom de l'ingrédient :</label>
        <input type="text" name="name" id="name" required>
    </div>
    <div>
        <label for="quantity">Quantité :</label>
        <input type="text" name="quantity" id="quantity" required>
    </div>
    <div>
        <label for="unit">Unité :</label>
        <select name="unit" id="unit">
            <option value="kg">kg</option>
            <option value="unité">Unité</option>
            <option value="litre">Litre</option>
            <option value="gramme">Gramme</option>
            <option value="millilitre">Millilitre</option>
        </select>
    </div>
    <button type="submit">Ajouter</button>
</form>

<h2>Votre liste d'ingrédients :</h2>

<button id="toggle-ingredients" onclick="toggleIngredients()">Afficher la liste des ingrédients</button>

<div id="ingredients-list">
    <?php if (!empty($ingredients)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Ingrédient (Français)</th>
                    <th>Ingrédient (Anglais)</th>
                    <th>Quantité</th>
                    <th>Unité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient) : ?>
                    <tr>
                        <td><?= htmlspecialchars($ingredient['name_fr']) ?></td>
                        <td><?= htmlspecialchars($ingredient['name_en']) ?></td>
                        <td><?= htmlspecialchars($ingredient['quantity']) ?></td>
                        <td><?= htmlspecialchars($ingredient['unit']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Aucun ingrédient trouvé.</p>
    <?php endif; ?>
</div>

<script>
    function toggleIngredients() {
        const ingredientsList = document.getElementById('ingredients-list');
        const button = document.getElementById('toggle-ingredients');
        
        // Toggle the display of the ingredients list
        if (ingredientsList.style.display === 'none') {
            ingredientsList.style.display = 'block';
            button.textContent = 'Masquer la liste des ingrédients';
        } else {
            ingredientsList.style.display = 'none';
            button.textContent = 'Afficher la liste des ingrédients';
        }
    }
</script>
</body>
</html>