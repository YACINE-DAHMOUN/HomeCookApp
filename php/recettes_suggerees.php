<?php
/*require_once 'db.php';

$user_id = $_SESSION['user_id'];

// Récupérer les ingrédients de l'utilisateur
$query = $pdo->prepare("SELECT name_fr FROM ingredients WHERE user_id = :user_id");
$query->execute(['user_id' => $user_id]);
$user_ingredients = $query->fetchAll(PDO::FETCH_COLUMN);

// Requête plus sophistiquée pour filtrer les recettes
$query = $pdo->prepare(
    "SELECT r.* FROM recipes r
    WHERE JSON_CONTAINS(
        (SELECT JSON_ARRAYAGG(name_fr) 
         FROM JSON_TABLE(r.ingredients, '$[*]' 
         COLUMNS (name_fr VARCHAR(255) PATH '$.name_fr')) AS ingredient_list
        ), 
        :user_ingredients
    )"
);

$query->execute(['user_ingredients' => json_encode($user_ingredients)]);
$filter_recipes = $query->fetchAll(PDO::FETCH_ASSOC);

// Affichage des recettes
if ($filter_recipes) {
    foreach ($filter_recipes as $recipe) {
        $ingredients = json_decode($recipe['ingredients'], true);
        $matching_ingredients = array_filter($ingredients, 
            fn($ing) => in_array($ing['name_fr'], $user_ingredients)
        );
        
        // N'afficher que les recettes où tous les ingrédients correspondent
        if (count($matching_ingredients) == count($ingredients)) {
            echo "<div class='recipe'>";
            echo "<h2>" . htmlspecialchars($recipe['name']) . "</h2>";
            echo "<p>" . htmlspecialchars($recipe['instructions']) . "</p>";
            echo "<h3>Ingrédients</h3>";
            echo "<ul>";
            foreach ($ingredients as $ingredient) {
                echo "<li>" . htmlspecialchars($ingredient['name_fr']) . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
} else {
    echo "<p>Aucune recette correspondante trouvée.</p>";
}
*/?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recettes</title>
</head>
<body>

<h1>Recettes suggérées</h1>

<!-- Bouton pour récupérer les recettes -->
<button id="load-recipes-btn">Afficher les recettes</button>

<!-- Section pour afficher les recettes -->
<div id="recipes-list"></div>

<script>
// Fonction pour charger les recettes via AJAX
document.getElementById('load-recipes-btn').addEventListener('click', function() {
    fetch('get_recipes.php')  // Créez un fichier séparé
        .then(response => response.text())
        .then(data => {
            document.getElementById('recipes-list').innerHTML = data;
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
});

</script>

</body>
</html>

