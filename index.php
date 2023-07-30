<?php
session_start();
require_once("config/db.php");
require_once("models/movie.php");
require_once("models/actor.php");
require_once("models/user.php");
require_once("layout/header.php");
require_once("layout/menu.php");
require_once("components/validator.php");
require_once("components/pagination.php");

if (isset($_GET["action"]) && file_exists("views/" . $_GET['action'] . ".php")) {
    require_once("views/" . $_GET['action'] . ".php");
} elseif (empty($_GET['action'])) {
    if (!isset($_SESSION["Logged"])) {
        require_once("views/login.php");
    } else {
        require_once("views/movies.php");

    }
} else {
    require_once("views/404.php");
}
require_once("layout/footer.php");
?>