<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../vendor/composer/autoload_classmap.php';
require_once __DIR__ . '/../includes/navigation.php';
session_start(); // Démarrer la session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<p>Veuillez vous connecter pour accéder à vos recettes.</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Étape 1 : Récupérer les ingrédients de l'utilisateur
$query = $pdo->prepare(
    "SELECT LOWER(name_en) AS ingredient 
    FROM ingredients 
    WHERE user_id = :user_id"
);
$query->execute(['user_id' => $user_id]);
$user_ingredients = $query->fetchAll(PDO::FETCH_COLUMN);

// Si aucun ingrédient n'est trouvé, afficher un message et sortir
if (empty($user_ingredients)) {
    echo "<p>Vous n'avez pas encore d'ingrédients. Ajoutez des ingrédients pour découvrir des recettes !</p>";
    exit;
}

// Étape 2 : Construire la requête SQL pour récupérer les recettes
// Créer des placeholders pour chaque ingrédient
$placeholders = implode(',', array_fill(0, count($user_ingredients), '?'));

// Construire la requête SQL avec plusieurs conditions OR pour chaque ingrédient
// Recherche des recettes contenant au moins un des ingrédients de l'utilisateur
$sql = "SELECT DISTINCT r.* 
        FROM recipes r 
        WHERE " . implode(' OR ', array_fill(0, count($user_ingredients), "JSON_SEARCH(LOWER(r.ingredients), 'one', ?) IS NOT NULL"));

// Préparer et exécuter la requête avec les ingrédients de l'utilisateur
$query = $pdo->prepare($sql);
$query->execute($user_ingredients);
$recipes = $query->fetchAll(PDO::FETCH_ASSOC);

// Calculer le score de correspondance des ingrédients
function calculate_ingredient_match($recipe_ingredients, $user_ingredients) {
    $recipe_ingredients = array_map('strtolower', 
        array_column(json_decode($recipe_ingredients, true), 'name')
    );
    
    $match_count = count(array_intersect($recipe_ingredients, $user_ingredients));
    return $match_count / count($recipe_ingredients);
}

// Trier les recettes par correspondance d'ingrédients
usort($recipes, function($a, $b) use ($user_ingredients) {
    $match_a = calculate_ingredient_match($a['ingredients'], $user_ingredients);
    $match_b = calculate_ingredient_match($b['ingredients'], $user_ingredients);
    return $match_b <=> $match_a; // Tri décroissant
});

// Étape 3 : Pagination
$recipes_per_page = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recipes_per_page;

$total_recipes = count($recipes);
$recipes_for_current_page = array_slice($recipes, $offset, $recipes_per_page);

// Étape 4 : Affichage des recettes
if ($recipes_for_current_page) {
    echo "<div class='recipe-container'>";
    foreach ($recipes_for_current_page as $recipe) {
        $ingredients = json_decode($recipe['ingredients'], true);
        
        // Calculer le pourcentage de correspondance
        $match_percentage = calculate_ingredient_match($recipe['ingredients'], $user_ingredients) * 100;

        echo "<div class='recipe'>";
        echo "<h2>" . htmlspecialchars($recipe['name']) . "</h2>";
        echo "<p class='ingredient-match'>Correspond à " . number_format($match_percentage, 0) . "% de vos ingrédients</p>";

        // Affichage de l'image si elle existe
        if (!empty($recipe['image_url'])) {
            echo "<img src='" . htmlspecialchars($recipe['image_url']) . "' alt='" . htmlspecialchars($recipe['name']) . "' style='max-width: 300px; margin-bottom: 15px;'>";
        }

        // Affichage des instructions
       
        // Modification de la partie affichage des instructions
echo "<h3>Instructions</h3>";
$instructions = $recipe['instructions'];

// Vérifier si les instructions contiennent déjà des balises HTML
if (strpos($instructions, '<') === false) {
    // Si pas de balises HTML, convertir le texte en liste
    $instruction_steps = explode('.', $instructions);
    $instructions = "<ol>";
    foreach ($instruction_steps as $step) {
        $step = trim($step);
        if (!empty($step)) {
            $instructions .= "<li>" . htmlspecialchars($step) . "</li>";
        }
    }
    $instructions .= "</ol>";
} else {
    // Si déjà du HTML, s'assurer que c'est sécurisé
    $instructions = strip_tags($instructions,'<ol><ul><li>' );
}

echo "<div class='instructions'>";
echo $instructions;
echo "</div>";
        $instructions = htmlspecialchars($recipe['instructions']);
        echo "<div class='instructions'>";
       
        echo "</div>";

        // Affichage des ingrédients
        echo "<h3>Ingrédients</h3>";
        echo "<ul>";
        if (is_array($ingredients)) {
            foreach ($ingredients as $ingredient) {
                $ingredient_name = htmlspecialchars($ingredient['name'] ?? 'Ingrédient non nommé');
                $in_user_ingredients = in_array(strtolower($ingredient_name), array_map('strtolower', $user_ingredients));
                $class = $in_user_ingredients ? 'ingredient-available' : 'ingredient-missing';
                
                echo "<li class='$class'>" . $ingredient_name . 
                     ($in_user_ingredients ? " (disponible)" : " (à acheter)") . 
                     "</li>";
            }
        }
        echo "</ul>";

        // Affichage des informations supplémentaires
        if (!empty($recipe['cooking_time'])) {
            echo "<p><strong>Temps de préparation :</strong> " . htmlspecialchars($recipe['cooking_time']) . " minutes</p>";
        }

        if (!empty($recipe['servings'])) {
            echo "<p><strong>Portions :</strong> " . htmlspecialchars($recipe['servings']) . "</p>";
        }

        echo "</div>";
    }
    echo "</div>";

    // Étape 5 : Affichage de la pagination
    $total_pages = ceil($total_recipes / $recipes_per_page);

    echo "<div class='pagination'>";
    if ($page > 1) {
        echo "<a href='?page=" . ($page - 1) . "'>&laquo; Page précédente</a>";
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='?page=$i'" . ($i === $page ? " class='active'" : "") . ">$i</a>";
    }

    if ($page < $total_pages) {
        echo "<a href='?page=" . ($page + 1) . "'>Page suivante &raquo;</a>";
    }
    echo "</div>";
} else {
    echo "<p>Aucune recette correspondante trouvée.</p>";
}

// Inclure le template HTML
require __DIR__ . '/../../templates/recipes.html.php';
?>