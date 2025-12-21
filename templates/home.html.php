<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - HomeCook</title>
    <link href="../../assets/css/home.css" rel="stylesheet">
    <link rel="icon" href="HomeCookApp\assets\images\logo.png" type="image/png">
</head>



<body>
    <header class="header"></header>

    
    <h1>Bonjour <?= htmlspecialchars($last_name) ?> !</h1>
    <h2>Bienvenus sur HomeCook</h2>

    <div id="description">
        <p id="slogan">Vous ne savez pas quoi cuisiner ce soir ?</p>
        <p>HomeCook est une application web qui peut vous simplifier votre quotidien en vous permettans de trouver des recettes de cuisine en fonction des ingrédients que vous avez chez vous.</p>
        <p>Vous n'avez qu'à renseigner les ingrédients que vous avez et HomeCook vous proposera des recettes en fonction de ces ingrédients.</p>
        <p>Vous pouvez aussi partager vos recettes avec la communauté en vous inscrivant sur HomeCook.</p>
        <p>Alors n'attendez plus et inscrivez-vous sur HomeCook pour profiter de toutes ces fonctionnalités.</p>
        <div>
        <p>Vous êtes membre ?</p>
        <form action="src/controllers/AuthController.php" method="post">
            <button type="submit">Connectez-vous</button>
        </form>
    </div>
    <div>
        <p>Pas encore ? c'est par ici 👇👇</p>
        <form action="src/controllers/RegisterController.php" method="get">
            <button type="submit">Inscrivez-vous</button>
        </form>
    </div>
</body>

</html>