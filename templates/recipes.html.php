<?php
// Inclure le controller pour préparer les données
require_once __DIR__ . '/../src/controllers/RecipeController.php';
?>

<link href="assets/css/recipes.css" rel="stylesheet">

<style>
    .recipe-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .recipe {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .recipe h2 {
        color: #333;
        margin-top: 0;
    }

    .ingredient-match {
        color: #4CAF50;
        font-weight: bold;
        margin: 10px 0;
    }

    .instructions {
        margin: 15px 0;
    }

    .instructions ol {
        padding-left: 20px;
    }

    .instructions li {
        margin: 5px 0;
    }

    .ingredient-available {
        color: #4CAF50;
        font-weight: bold;
    }

    .ingredient-missing {
        color: #f44336;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 30px 0;
    }

    .pagination a {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }

    .pagination a.active {
        background-color: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .pagination a:hover:not(.active) {
        background-color: #f0f0f0;
    }

    .error {
        color: #f44336;
        background-color: #ffebee;
        padding: 15px;
        border-radius: 5px;
        margin: 20px;
    }

    h1 {
        text-align: center;
        color: #333;
        margin: 20px 0;
    }
</style>

<h1>Vos Recettes Suggérées</h1>

<!-- Bouton pour afficher/masquer les recettes -->
<div style="text-align: center; margin: 20px 0;">
    <button id="toggle-recipes-btn" onclick="toggleRecipes()" style="
        background-color: #4CAF50;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    ">
        Afficher les recettes
    </button>
</div>

<div id="recipes-content" style="display: none;">

<?php if (isset($error_message)): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <p style="text-align: center;">
        <a href="index.php?page=profil" style="color: #4CAF50; text-decoration: underline;">
            Ajoutez des ingrédients dans votre profil
        </a>
    </p>
<?php elseif (!empty($recipes_for_current_page)): ?>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">
        <?= $total_recipes ?> recette<?= $total_recipes > 1 ? 's' : '' ?> trouvée<?= $total_recipes > 1 ? 's' : '' ?> 
        (Page <?= $current_page ?> sur <?= $total_pages ?>)
    </p>
    
    <div class="recipe-container">
        <?php foreach ($recipes_for_current_page as $recipe): ?>
            <?php
            $ingredients = json_decode($recipe['ingredients'], true);
            $match_percentage = calculate_ingredient_match($recipe['ingredients'], $user_ingredients) * 100;
            ?>
            
            <div class="recipe">
                <h2><?= htmlspecialchars($recipe['name']) ?></h2>
                <p class="ingredient-match">
                    Correspond à <?= number_format($match_percentage, 0) ?>% de vos ingrédients
                </p>

                <?php if (!empty($recipe['image_url'])): ?>
                    <img src="<?= htmlspecialchars($recipe['image_url']) ?>" 
                         alt="<?= htmlspecialchars($recipe['name']) ?>" 
                         style="max-width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;">
                <?php endif; ?>

                <h3>Instructions</h3>
                <div class="instructions">
                    <?php
                    $instructions = $recipe['instructions'];
                    if (strpos($instructions, '<') === false) {
                        $instruction_steps = array_filter(array_map('trim', explode('.', $instructions)));
                        if (!empty($instruction_steps)) {
                            echo "<ol>";
                            foreach ($instruction_steps as $step) {
                                if (!empty($step)) {
                                    echo "<li>" . htmlspecialchars($step) . "</li>";
                                }
                            }
                            echo "</ol>";
                        }
                    } else {
                        echo strip_tags($instructions, '<ol><ul><li>');
                    }
                    ?>
                </div>

                <h3>Ingrédients</h3>
                <ul>
                    <?php if (is_array($ingredients)): ?>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <?php
                            $ingredient_name = $ingredient['name'] ?? 'Ingrédient non nommé';
                            $in_user_ingredients = in_array(strtolower($ingredient_name), array_map('strtolower', $user_ingredients));
                            $class = $in_user_ingredients ? 'ingredient-available' : 'ingredient-missing';
                            ?>
                            <li class="<?= $class ?>">
                                <?= htmlspecialchars($ingredient_name) ?>
                                <?= $in_user_ingredients ? ' ✓ (disponible)' : ' ✗ (à acheter)' ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($recipe['cooking_time'])): ?>
                    <p><strong>⏱️ Temps de préparation :</strong> <?= htmlspecialchars($recipe['cooking_time']) ?> minutes</p>
                <?php endif; ?>

                <?php if (!empty($recipe['servings'])): ?>
                    <p><strong>🍽️ Portions :</strong> <?= htmlspecialchars($recipe['servings']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
                <a href="index.php?page=recipes&recipe_page=1" title="Première page">⏮️ Première</a>
                <a href="index.php?page=recipes&recipe_page=<?= $current_page - 1 ?>">&laquo; Précédente</a>
            <?php endif; ?>

            <?php 
            // Afficher maximum 5 numéros de page
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <a href="index.php?page=recipes&recipe_page=<?= $i ?>" 
                   <?= $i === $current_page ? 'class="active"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="index.php?page=recipes&recipe_page=<?= $current_page + 1 ?>">Suivante &raquo;</a>
                <a href="index.php?page=recipes&recipe_page=<?= $total_pages ?>" title="Dernière page">Dernière ⏭️</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p style="text-align: center; margin: 40px; color: #666;">
        Aucune recette correspondante trouvée avec vos ingrédients actuels.
    </p>
<?php endif; ?>

</div>

<script>
    function toggleRecipes() {
        const content = document.getElementById('recipes-content');
        const button = document.getElementById('toggle-recipes-btn');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            button.textContent = 'Masquer les recettes';
            button.style.backgroundColor = '#f44336';
        } else {
            content.style.display = 'none';
            button.textContent = 'Afficher les recettes';
            button.style.backgroundColor = '#4CAF50';
        }
    }
    
    // Auto-afficher si on arrive avec un numéro de page spécifique
    <?php if (isset($_GET['recipe_page']) && $_GET['recipe_page'] > 1): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleRecipes();
        });
    <?php endif; ?>
</script>