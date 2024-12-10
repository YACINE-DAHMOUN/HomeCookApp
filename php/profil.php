<?php
require_once 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur (y compris prénom et nom)
try {
    $query = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = :user_id");
    $query->execute(['user_id' => $user_id]);
    $user = $query->fetch();

    // Vérifier si l'utilisateur existe
    if (!$user) {
        echo "Utilisateur non trouvé.";
        exit;
    }

    // Récupérer le prénom et le nom de l'utilisateur
    $first_name = $user['first_name'];
    $last_name = $user['last_name'];

} catch (PDOException $e) {
    error_log("Erreur de récupération des informations de l'utilisateur : " . $e->getMessage());
    exit;
}

// Ajouter un ingrédient si le formulaire a été soumis
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
            // Insérer l'ingrédient dans la base de données en liant l'utilisateur actuel
            $query = $pdo->prepare("INSERT INTO ingredients (name, quantity, unit, user_id, created_at) VALUES (:name, :quantity, :unit, :user_id, NOW())");
            $query->execute([
                'name' => $name,
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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpCook - Profil</title>
    <style>
        #ingredients-list {
            display: none;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
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

<?php if (!empty($successMessage)) : ?>
    <div class="success">
        <p><?= htmlspecialchars($successMessage) ?></p>
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
            <!-- Ajouter d'autres unités si nécessaire -->
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
                    <th>Ingrédient</th>
                    <th>Quantité</th>
                    <th>Unité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ingredient) : ?>
                    <tr>
                        <td><?= htmlspecialchars($ingredient['name']) ?></td>
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
