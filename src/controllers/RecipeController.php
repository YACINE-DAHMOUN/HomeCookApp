<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $error_message = "Veuillez vous connecter pour accéder à vos recettes.";
    $recipes = [];
    $recipes_for_current_page = [];
    return;
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

// Si aucun ingrédient n'est trouvé
if (empty($user_ingredients)) {
    $error_message = "Vous n'avez pas encore d'ingrédients. Ajoutez des ingrédients pour découvrir des recettes !";
    $recipes = [];
    $recipes_for_current_page = [];
    return;
}

// Étape 2 : Construire la requête SQL pour récupérer les recettes
$sql = "SELECT DISTINCT r.id, r.name, r.ingredients, r.instructions, r.image_url, r.cooking_time, r.servings
        FROM recipes r 
        WHERE " . implode(' OR ', array_fill(0, count($user_ingredients), "JSON_SEARCH(LOWER(r.ingredients), 'one', ?) IS NOT NULL"));

$query = $pdo->prepare($sql);
$query->execute($user_ingredients);
$recipes = $query->fetchAll(PDO::FETCH_ASSOC);

// Supprimer les doublons potentiels en utilisant l'ID comme clé unique
$unique_recipes = [];
foreach ($recipes as $recipe) {
    $unique_recipes[$recipe['id']] = $recipe;
}
$recipes = array_values($unique_recipes);

// Fonction de calcul de correspondance
function calculate_ingredient_match($recipe_ingredients, $user_ingredients)
{
    $recipe_ingredients_array = json_decode($recipe_ingredients, true);
    if (!is_array($recipe_ingredients_array)) {
        return 0;
    }
    
    $recipe_ingredients = array_map(
        'strtolower',
        array_column($recipe_ingredients_array, 'name')
    );

    $match_count = count(array_intersect($recipe_ingredients, $user_ingredients));
    $total_ingredients = count($recipe_ingredients);
    
    return $total_ingredients > 0 ? $match_count / $total_ingredients : 0;
}

// Trier les recettes par correspondance d'ingrédients
usort($recipes, function ($a, $b) use ($user_ingredients) {
    $match_a = calculate_ingredient_match($a['ingredients'], $user_ingredients);
    $match_b = calculate_ingredient_match($b['ingredients'], $user_ingredients);
    return $match_b <=> $match_a;
});

// Étape 3 : Pagination
$recipes_per_page = 6;
$current_page = isset($_GET['recipe_page']) && is_numeric($_GET['recipe_page']) ? (int)$_GET['recipe_page'] : 1;
$offset = ($current_page - 1) * $recipes_per_page;

$total_recipes = count($recipes);
$total_pages = ceil($total_recipes / $recipes_per_page);
$recipes_for_current_page = array_slice($recipes, $offset, $recipes_per_page);