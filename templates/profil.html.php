<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpCook - Profil</title>
    <link href="../../assets/css/profil.css" rel="stylesheet">
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

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        form {
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 5px;
        }

        input,
        select {
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

    <div id="ingredients-list" style="display: none;">
        <?php if (!empty($ingredients)) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Ingrédient (Français)</th>
                        <!--<th>Ingrédient (Anglais)</th>-->
                        <th>Quantité</th>
                        <th>Unité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ingredients as $ingredient) : ?>
                        <tr>
                            <td><?= htmlspecialchars($ingredient['name_fr']) ?></td>
                            <!--<td><?= htmlspecialchars($ingredient['name_en']) ?></td>-->
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