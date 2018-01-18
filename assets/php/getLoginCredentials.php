<?php
/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 07:12
 */

session_start();

if (isset($_SESSION["chat-web-token"])) {
    echo json_encode(Array("succ" => true, "token" => $_SESSION["chat-web-token"], "userid" => $_SESSION["chat-web-userid"], "username" => $_SESSION["chat-web-username"]));
} else {
    echo json_encode(Array("succ" => false));
}