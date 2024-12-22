<?php require 'get_recipes.php'; ?>

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
<div id="recipes-list" ></div>

<script>
// Fonction pour charger les recettes via AJAX
document.getElementById('load-recipes-btn').addEventListener('click', function() {
    fetch('recettes_suggerees.php')  // Créez un fichier séparé
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

