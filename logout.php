<?php
/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 17/01/2018
 * Time: 09:37
 */

session_start();
session_destroy();
session_unset();

if (isset($_GET["reason"])) {
    if ($_GET["reason"] == "invalidtoken") {
        require_once("assets/php/utils.class.php");
        utils::issueHeader();
        require_once("assets/html/errors/invalidtoken.html");
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}