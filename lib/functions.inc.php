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
    case 'supprimer':
        
        break;
}
//var_dump(RecupererImage(93));
/**
 * Undocumented function
 *
 * @return PDO
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
 * @return bool
 */
function createPost($commentaire, $image)
{
    try {
        dbM152()->beginTransaction();
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
            $tmp_name = $image["tmp_name"][$i];
            $tmp_name = str_replace(' ', '', $tmp_name);
            var_dump(move_uploaded_file($tmp_name, "./uploads/$uniqid"));
            if (file_exists("./uploads/$uniqid")) {
                if (((explode("/", $image['type'][$i])[0] == "image" && $image['size'][$i] <= 3000000) || explode("/", $image['type'][$i])[0] == "video") || (explode("/", $image['type'][$i])[0] == "audio") && $image_size_totale <= 70000000) {
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
                    unlink("./uploads/$uniqid");
                }
            } else {
                echo "<script>alert(\"Une erreur est survenu\")</script>";
            }
        }
        dbM152()->commit();
        return true;
    } catch (Exception $e) {
        dbM152()->rollBack();
        return false;
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
        switch (explode("/", RecupererImage($post['idPost'])[0]['typeMedia'])[0]) {
            case "image":
                echo "<img class=\"card-img-top\" src=\"./uploads/";
                echo RecupererImage($post['idPost'])[0]['nomMedia'];
                echo "\"alt=\"Card cap\">";
                break;
            case "video":
                echo "<video width=\"286\" height=\"200\" autoplay controls loop>";
                echo "<source src=\"./uploads/" . RecupererImage($post['idPost'])[0]['nomMedia'] . "\"" . " type=\"video/mp4\">";
                echo "</video>";
                break;
            case "audio":
                echo "<audio controls>";
                echo "<source src=\"./uploads/" . RecupererImage($post['idPost'])[0]['nomMedia'] . "\"" . " type=\"video/mp4\">";
                echo "</audio>";
                break;
        }
        echo "<div class=\"card-body\">
              <p class=\"card-text\">";
        echo $post['commentaire'];
        echo "</div>
        <input type=\"image\" src=\"./img/trash-alt-regular.svg\" style=\"width:20px;\" name=\"action\" value=\"supprimer\" id=\"" . $post['idPost'] . " />
        <input type=\"image\" src=\"./img/edit-regular.svg\" style=\"width:20px;\" name=\"action\" value=\"modifier\"/>
          </div>";
    }
}
