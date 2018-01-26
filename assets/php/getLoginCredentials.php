<?php
/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 07:12
 */

session_start();

if (isset($_SESSION["sgunnerme-token"])) {
    echo json_encode(Array("succ" => true, "token" => $_SESSION["sgunnerme-token"], "userid" => $_SESSION["sgunnerme-userid"], "username" => $_SESSION["sgunnerme-username"]));
} else {
    echo json_encode(Array("succ" => false));
}