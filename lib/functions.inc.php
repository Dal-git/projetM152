<?php
require "./lib/constantes.inc.php";

$image = $_FILES['mediafile'];
$description = filter_input(INPUT_POST, "description");
$action = filter_input(INPUT_POST, "action");
switch ($action) {
    case 'ajouter':
        createPost($description, $image);
        break;
}

function dbM152()
{
    static $dbc = null;

    // Première visite de la fonction
    if ($dbc == null) {
        // Essaie le code ci-dessous
        try {
            $dbc = new PDO('mysql:host=' . HOST . ';dbname=' . DBNAME, DBUSER, DBPWD, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                PDO::ATTR_PERSISTENT => true
            ));
        }
        // Si une exception est arrivée
        catch (Exception $e) {
            echo 'Erreur : ' . $e->getMessage() . '<br />';
            echo 'N° : ' . $e->getCode();
            // Quitte le script et meurt
            die('Could not connect to MySQL');
        }
    }
    // Pas d'erreur, retourne un connecteur
    return $dbc;
}

function createPost($commentaire, $image)
{
    $image_size_totale = 0;
    static $ps = null;
    $date = new DateTime('Y-m-d H:i:s');
    $sql = "INSERT INTO `m152`.`POST` (`commentaire`, `dateDeCreation`,`dateDeModification`) VALUES (:COM, :DATE,:DATE)";
    if ($ps == null) {
        $ps = dbM152()->prepare($sql);
    }
    try {
        $ps->bindParam(':COM', $commentaire, PDO::PARAM_STR);
        $ps->bindParam(':DATE', $date);
        $ps->execute();
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    for ($i = 0; $i < count($image['type']); $i++) {

        $uniqid = uniqid($image['name'][$i]);

        var_dump(count($image['size']));
        if (explode("/", $image['type'][$i])[0] == "image" && $image['size'][$i] <= 3000000 && $image_size_totale <= 70000000) {
            $image_size_totale += $image['size'][$i];
            $sql = "INSERT INTO `m152`.`MEDIA` (`typeMedia`,`nomMedia`,`dateDeCreation`,`idPost`) VALUES (:TYPEM, :NOMM,:DATE,(select idPost from POST where `commentaire` = :COM and `dateDeCreation` = :DATE))";
            $ps = dbM152()->prepare($sql);
            try {
                $ps->bindParam(':COM', $commentaire, PDO::PARAM_STR);
                $ps->bindParam(':DATE', $date);
                $ps->bindParam(':TYPEM', $image['type'][$i], PDO::PARAM_STR);
                $ps->bindParam(':NOMM', $uniqid, PDO::PARAM_STR);
                $ps->execute();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        } else {
            echo "<script>alert(\"Un des champs est eronné\")</script>";
        }
        if ($image["error"] == UPLOAD_ERR_OK) {
            $tmp_name = $image["tmp_name"][$i];
            // basename() peut empêcher les attaques de système de fichiers;
            // la validation/assainissement supplémentaire du nom de fichier peut être approprié            
            var_dump(move_uploaded_file($tmp_name, "./uploads/$uniqid"));
        }
    }
}
