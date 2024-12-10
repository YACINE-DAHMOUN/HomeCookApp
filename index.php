<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCook</title>
</head>
<body>
   <h1>HomeCook</h1>
   <h2>Bienvenue sur HomeCook</h2>
    <p>Vous ne savez pas quoi cuisiner ce soir ?</p>
    <p>HomeCook est une application web qui peut vous simplifier votre quotidien en vous permettans de trouver des recettes de cuisine en fonction des ingrédients que vous avez chez vous.</p>
    <p>Vous n'avez qu'à renseigner les ingrédients que vous avez et HomeCook vous proposera des recettes en fonction de ces ingrédients.</p>
    <p>Vous pouvez aussi partager vos recettes avec la communauté en vous inscrivant sur HomeCook.</p>
    <p>Alors n'attendez plus et inscrivez-vous sur HomeCook pour profiter de toutes ces fonctionnalités.</p>
    <div>
        <p>Vous êtes membre ?</p>
        <form action="php/login.php" method="post">
            <button type="submit">Connectez-vous</button>
        </form>
    </div>
    <div>
        <p>Pas encore ? c'est par ici 👇👇</p>
        <form action="php/register.php" method="get">
            <button type="submit">Inscrivez-vous</button>
        </form>
    </div>
</body>
</html>