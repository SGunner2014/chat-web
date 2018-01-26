<?php

/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 00:27
 *
 * Used for general utilities.
 */

require_once("db.class.php");

session_start();

class utils
{
    /**
     * @return True if the current user is logged in, false if not.
     */
    public static function isLoggedIn() {
        if (isset($_SESSION["sgunnerme-userid"])) {
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

    /**
     * Generates a unique salt for a new user
     * @return string The generated salt
     */
    //Generates a new salt for password storage
    public static function generateSalt() {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz01234567890";
        $tbl = str_split($alphabet);
        $salt = "";
        for ($i = 0; $i < 20; $i++) { //Concatenate random characters together to make a random salt
            $salt .= $tbl[rand(0, 61)];
        }
        return $salt;
    }

    /**
     * Checks that the username and password go together
     * @param $username String The username for the account
     * @param $password String The password for the account
     * @return array The dump from the operation
     */
    public static function verifyLogin($username, $password) {
        $db = new db();
        $records = $db->select("users", "username", $username); //get hold of details of user_error
        if (mysqli_num_rows($records) == 1) { //Check that the user exists
            $record = mysqli_fetch_assoc($records);
            $salt = $record["salt"];
            $finalCmtArrayPassword = hash("sha256", $password . $salt); //Hash the password
            if ($finalCmtArrayPassword == $record["passhash"]) { //Check that the passwords match
                $toReturn = Array("succ" => true, "userid" => $record["id"]);
                return $toReturn;
            } else { //The username and password did not match
                $toReturn = Array("succ" => false, "message" => "Either the password or username that was entered is incorrect.");
                return $toReturn;
            }
        } else {
            $toReturn = Array("succ" => false, "message" => "A user with that username was not found.");
            return $toReturn;
        }
    }

    /**
     * @param $username String The username to check for
     * @return bool Whether or not the user exists
     */
    public static function userExists($username) {
        $db = new db();

        if ($db->getOccurences("users", "username", $username) >= 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Registers a new user
     * @return String The token for the newly-logged in user
     */
    public static function registerUser($username, $password) {
        $db = new db();

        if (self::userExists($username)) {
            return false;
        }

        $token = self::generateSalt() . self::generateSalt() . self::generateSalt();
        $salt = self::generateSalt();
        $passhash = hash("sha256", $password . $salt);

        $toInsert = Array(
            "username" => $username,
            "passhash" => $passhash,
            "salt" => $salt
        );

        $id = $db->insert("users", $toInsert);

        $toInsert = Array(
            "userid" => $id,
            "loginkey" => $token,
        );

        if ($db->getOccurences("loginkeys", "userid", $id) >= 1) {
            $db->update("loginkeys", "userid", $id, "loginkey", $token);
        } else {
            $db->insert("loginkeys", $toInsert);
        }

        $details = Array(
            "token" => $token,
            "userid" => $id,
            "username" => $username
        );

        return $details;
    }

    /**
     * Converts a username to a user ID
     * @param $username
     * @return String the userid of the user
     */
    public static function useridFromUsername($username) {
        $db = new db();
        $records = $db->select("users", "username", $username);
        $rec = mysqli_fetch_assoc($records);
        return $rec["id"];
    }

    /**
     * Logs in the user and returns the login token for the current session
     * @param $username String The username of the user
     * @param $password String The password of the user
     * @return string The token for the current session
     */
    public static function login($username, $password) {
        if (self::verifyLogin($username, $password)) {
            $db = new db();

            $newtoken = self::generateSalt() . self::generateSalt() . self::generateSalt();

            $userid = self::useridFromUsername($username);
            $db->update("loginkeys", "userid", $userid, "loginkey", $newtoken);
            $toReturn = Array(
                "username" => $username,
                "userid" => $userid,
                "token" => $newtoken
            );
            return $toReturn;

        } else {
            return null;
        }
    }
}