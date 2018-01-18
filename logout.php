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
header("Location: index.php");