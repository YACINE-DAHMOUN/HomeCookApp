<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCook</title>
</head>

<body>
    
    <?php 
    $page = $_GET['page'] ?? 'home';
    $pages_autorisees = ['home', 'login', 'register', 'profil', 'recipes'];
    if (!in_array($page, $pages_autorisees)) {
        $page = 'home';
    }
    include "src/includes/navigation.php";
    include "templates/{$page}.html.php";
    ?>
    
</body>

</html>