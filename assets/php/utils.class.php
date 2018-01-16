<?php

/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 00:27
 *
 * Used for general utilities.
 */
class utils
{
    /**
     * @return True if the current user is logged in, false if not.
     */
    public static function isLoggedIn() {
        if (isset($_SESSION["chat-web-userid"])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Issues the correct header
     */
    public static function issueHeader() {
        if (!self::isLoggedIn()) {
            require_once("html-deps/logout-header.html");
        } else {
            require_once("html-deps/login-header.html");
        }
    }
}