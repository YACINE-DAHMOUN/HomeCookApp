<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recettes - HomeCook</title>
    <link href="../../assets/css/recipes.css" rel="stylesheet">
</head>

<body>
    <!-- Contenu existant de get_recipes.php -->
    <?php if (isset($recipes) && !empty($recipes)): ?>
        <div class="recipes-container">
            <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">
                    <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                    <p><?= htmlspecialchars($recipe['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Aucune recette trouvée.</p>
    <?php endif; ?>
</body>

</html>