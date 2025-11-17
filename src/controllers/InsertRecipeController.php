<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../vendor/composer/autoload_classmap.php';  // Inclure l'autoloader de Composer

$api_key = 'e332d0e53d5d40f6862c3f3b8d330775';

function fetchRecipesFromApi($api_key)
{
    $url = 'https://api.spoonacular.com/recipes/random?apiKey=' . $api_key . '&number=300'; // Notez le 'apiKey' en minuscules

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Ajoutez ces deux lignes pour contourner les problèmes de certificat SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $response = curl_exec($ch);

    // Vérifier les erreurs cURL
    if ($response === false) {
        $error = curl_error($ch);
        echo "Erreur cURL : " . $error . "\n";
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "Code de statut HTTP : " . $httpCode . "\n";

    curl_close($ch);

    return json_decode($response, true);
}

// Récupérer les recettes depuis l'API
$recipes = fetchRecipesFromApi($api_key);

// Vérification des données retournées par l'API
if (isset($recipes['recipes']) && is_array($recipes['recipes'])) {
    // Préparer la requête d'insertion
    $query = $pdo->prepare("INSERT INTO recipes (name, instructions, ingredients, image_url, servings, cooking_time) VALUES (:name, :instructions, :ingredients, :image_url, :servings, :cooking_time)");

    // Parcourir les recettes récupérées et les insérer dans la base de données
    foreach ($recipes['recipes'] as $recipe) {
        // Extraction des données avec vérification
        $name = $recipe['title'] ?? 'Recette sans titre';
        $instructions = $recipe['instructions'] ?? 'Aucune instruction disponible';
        $ingredients = isset($recipe['extendedIngredients']) ? json_encode($recipe['extendedIngredients']) : json_encode([]);
        $image_url = $recipe['image'] ?? null;
        $servings = $recipe['servings'] ?? null;
        $cooking_time = $recipe['readyInMinutes'] ?? null;

        try {
            // Exécuter la requête d'insertion
            $query->execute([
                'name' => $name,
                'instructions' => $instructions,
                'ingredients' => $ingredients,
                'image_url' => $image_url,
                'servings' => $servings,
                'cooking_time' => $cooking_time,
            ]);
        } catch (PDOException $e) {
            echo 'Erreur lors de l\'insertion : ' . $e->getMessage() . "\n";
        }
    }

    echo 'Recettes insérées avec succès.';
} else {
    echo 'Aucune recette récupérée depuis l\'API.';
}
