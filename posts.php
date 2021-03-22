<?php
require './lib/functions.inc.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a69fb5a135.js" crossorigin="anonymous"></script>
    <title>Posts</title>
</head>

<body>
    <!-- Nav -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand">
                <img src="./img/logo-cfpt-site.png" alt="" width="40" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <form class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link " href="./home.php"><i class="fas fa-home"></i> Home</a>

                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="./posts.php"><i class="fas fa-plus"></i> Posts</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <form method="POST" action="#" enctype="multipart/form-data">
        <div class="form-group">
            <div>
                <input type="text" placeholder="Description" class="form-control" name="description">
                <label for="file">Sélectionner le fichier à envoyer</label>
                <input type="file" id="file" name="mediafile[]" accept="audio/*,video/*,image/*" multiple>
            </div>
            <button name="action" class="btn btn-warning" value="cancel">Annuler</button>
            <button type="submit" class="btn btn-success col-md-1 offset-md-10" name="action" value="ajouter">Ajouter</button>
        </div>
    </form>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</body>

</html>