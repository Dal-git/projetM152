<?php
require "./lib/constantes.inc.php";
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

function createPost($image, $commentaire)
{
    static $ps = null;
    $date = date('d.m.y');
    $sql = "INSERT INTO `m152`.`POST` (`commentaire`, `dateDeCreation`) ";
    $sql .= "VALUES (:COM, :DATE)";
    if ($ps == null) {
        $ps = dbM152()->prepare($sql);
    }
    try {
        $ps->bindParam(':COM', $commentaire, PDO::PARAM_STR);
        $ps->bindParam(':DATE', $date);
        $ps->execute();
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    $sql = "INSERT INTO `m152`.`MEDIA` (`typeMedia`,`nomMedia`,`idPost`) ";
    $sql .= "VALUES (:TYPEM, :NOMM,(select idPost from POST where commentaire = :COM and dateDeCreation = :DATE))";
    $ps = dbM152()->prepare($sql);
    try {
        $ps->bindParam(':COM', $commentaire, PDO::PARAM_STR);
        $ps->bindParam(':DATE', $date);
        for ($i = 0; $i < count($image['name']); $i++) {
            $ps->bindParam(':TYPEM', $image['type'][$i], PDO::PARAM_STR);
            $ps->bindParam(':NOMM', $image['name'][$i], PDO::PARAM_STR);
            $ps->execute();
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
