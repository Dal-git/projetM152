<?php
require "./lib/constantes.inc.php";

$image = $_FILES['mediafile'];
$description = filter_input(INPUT_POST, "description");
$action = filter_input(INPUT_POST, "action");
switch ($action) {
    case 'ajouter':
        createPost($description, $image);
        //header('Location: home.php');
        break;
}
//var_dump(RecupererImage(93));
/**
 * Undocumented function
 *
 * @return void
 */
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
/**
 * Crée un post
 *
 * @param string $commentaire Commentaire de l'utilisateur
 * @param string $image Image nécessaire à la création du post
 * @return void
 */
function createPost($commentaire, $image)
{
    $image_size_totale = 0;
    static $ps = null;
    //$date = date('Y-m-d');
    $dateTime = new DateTime('NOW');
    $date = $dateTime->format('Y-m-d H:i:s');

    if ($commentaire != "" && $commentaire != null) {
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
    } else {
        echo "<script>alert(\"Commentaire est vide et c'est pas bien rempli le champ commentaire stp :D\")</script>";
    }

    var_dump($_FILES);
    for ($i = 0; $i < count($image['type']); $i++) {

        $uniqid = uniqid($image['name'][$i]);

        var_dump($image);
        var_dump(explode("/", $image['type'][$i])[0]);
        $tmp_name = $image["tmp_name"][$i];
        $tmp_name = str_replace(' ', '', $tmp_name);
        var_dump(move_uploaded_file($tmp_name, "./uploads/$uniqid"));
        if (file_exists("./uploads/$uniqid")) {
            if (((explode("/", $image['type'][$i])[0] == "image" && $image['size'][$i] <= 3000000) || (explode("/", $image['type'][$i])[0] == "video")) || (explode("/", $image['type'][$i])[0] == "ad") && $image_size_totale <= 70000000) {
                var_dump("ok");
                $image_size_totale += $image['size'][$i];
                $sql = "INSERT INTO `m152`.`MEDIA` (`typeMedia`,`nomMedia`,`dateDeCreation`,`idPost`) VALUES (:TYPEM, :NOMM,:DATE,(select idPost from POST where `commentaire` = :COM and `dateDeCreation` = :DATE))";
                $ps = dbM152()->prepare($sql);
                try {
                    $ps->bindParam(':COM', $commentaire, PDO::PARAM_STR);
                    var_dump($commentaire);
                    $ps->bindParam(':DATE', $date);
                    var_dump($date);
                    $ps->bindParam(':TYPEM', $image['type'][$i], PDO::PARAM_STR);
                    var_dump($image['type'][$i]);
                    $ps->bindParam(':NOMM', $uniqid, PDO::PARAM_STR);
                    var_dump($uniqid);
                    $ps->execute();
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
            } else {
                echo "<script>alert(\"Un des champs est eronné\")</script>";
            }
        } else {
            echo "<script>alert(\"Une erreur est survenu\")</script>";
        }
    }
}

function RecupererTable()
{
    $table = "";
    static $ps = null;
    $sql = "SELECT * FROM `m152`.`POST`";
    if ($ps == null) {
        $ps = dbM152()->prepare($sql);
    }
    try {
        if ($ps->execute()) {
            $table = $ps->fetchall(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $table;
}

function RecupererImage($idPost)
{
    $table = "";
    static $ps = null;
    $sql = "SELECT * FROM `m152`.`MEDIA` WHERE `idPost` LIKE :idPost";
    if ($ps == null) {
        $ps = dbM152()->prepare($sql);
    }
    try {
        $ps->bindParam(":idPost", $idPost);
        if ($ps->execute()) {
            $table = $ps->fetchall(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $table;
}

function Afficher()
{
    $tableauDePost = RecupererTable();
    foreach ($tableauDePost as $post) {
        echo "<div class=\"card\" style=\"width: 18rem;\">";
        switch (RecupererImage($post['idPost'])[0]['typeMedia']) {
            case "image/jpeg":
                echo "<img class=\"card-img-top\" src=\"./uploads/";
                echo RecupererImage($post['idPost'])[0]['nomMedia'];
                echo "\"alt=\"Card cap\">";
                break;
            case "video/mp4":
                echo "<video width=\"320\" height=\"240\" autoplay controls loop>";
                echo "<source src=\"./uploads/" . RecupererImage($post['idPost'])[0]['nomMedia'] . "\"" . " type=\"video/mp4\">";
                echo "</video>";
                break;
            case "audio/mp3":
                echo "<audio controls>";
                echo "<source src=\"./uploads/" . RecupererImage($post['idPost'])[0]['nomMedia'] . "\"" . " type=\"video/mp4\">";
                echo "</audio>";
                break;
        }
        echo "<div class=\"card-body\">
              <p class=\"card-text\">";
        echo $post['commentaire'];
        echo "</div>
          </div>";
    }
}
