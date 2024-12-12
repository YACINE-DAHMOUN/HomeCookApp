<?php
require_once 'db.php';
require_once 'C:\Users\Admin\Desktop\HelpCook\HomeCookApp\vendor\composer\autoload_classmap.php';
include 'nav.php';
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
    echo "<p>Aucun ingrédient disponible.</p>";
    exit;
}

// Étape 2 : Construire la requête SQL pour récupérer les recettes
$user_ingredients = ['tomato', 'potato', 'oil']; // Liste des ingrédients de l'utilisateur

// Créer des placeholders pour chaque ingrédient
$placeholders = implode(',', array_fill(0, count($user_ingredients), '?'));

// Construire la requête SQL avec plusieurs conditions OR pour chaque ingrédient
$sql = "SELECT DISTINCT r.* 
        FROM recipes r 
        WHERE " . implode(' OR ', array_fill(0, count($user_ingredients), "JSON_SEARCH(LOWER(r.ingredients), 'one', ?) IS NOT NULL"));

// Préparer et exécuter la requête avec les ingrédients de l'utilisateur
$query = $pdo->prepare($sql);
$query->execute($user_ingredients);
$recipes = $query->fetchAll(PDO::FETCH_ASSOC);



// Préparer et exécuter la requête
$query = $pdo->prepare($sql);
$query->execute($user_ingredients);
$recipes = $query->fetchAll(PDO::FETCH_ASSOC);

// Étape 3 : Pagination
$recipes_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recipes_per_page;

// Supprimer les doublons et calculer le total des recettes
$recipes = array_unique($recipes, SORT_REGULAR);
$total_recipes = count($recipes);
$recipes_for_current_page = array_slice($recipes, $offset, $recipes_per_page);

// Étape 4 : Affichage des recettes
if ($recipes_for_current_page) {
    foreach ($recipes_for_current_page as $recipe) {
        $ingredients = json_decode($recipe['ingredients'], true);

        echo "<div class='recipe'>";
        echo "<h2>" . htmlspecialchars($recipe['name']) . "</h2>";

        // Affichage de l'image si elle existe
        if (!empty($recipe['image_url'])) {
            echo "<img src='" . htmlspecialchars($recipe['image_url']) . "' alt='" . htmlspecialchars($recipe['name']) . "' style='max-width: 300px; margin-bottom: 15px;'>";
        }

        // Affichage des instructions
        echo "<h3>Instructions</h3>";
        $instructions = htmlspecialchars($recipe['instructions']);
        echo "<div class='instructions'>";
        echo "<p>$instructions</p>";
        echo "</div>";

        // Affichage des ingrédients
        echo "<h3>Ingrédients</h3>";
        echo "<ul>";
        if (is_array($ingredients)) {
            foreach ($ingredients as $ingredient) {
                echo "<li>" . htmlspecialchars($ingredient['name'] ?? 'Ingrédient non nommé') . "</li>";
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
?>
