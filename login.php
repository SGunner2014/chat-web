<?php
/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 07:22
 */

require_once("assets/php/Utilities.php");


if (isset($_POST["username"], $_POST["password"])) {
    $tokenDetails = Utilities::login($_POST["username"], $_POST["password"]);
    $_SESSION["sgunnerme-token"] = $tokenDetails["token"];
    $_SESSION["sgunnerme-userid"] = $tokenDetails["userid"];
    $_SESSION["sgunnerme-username"] = $tokenDetails["username"];
    header("Location: chat.php");
}

Utilities::issueHeader();


if (isset($_GET["reason"])) {
    $reason = $_GET["reason"];
    if ($reason == "loggedout") {
        require_once("assets/html/errors/loggedouterror.html");
    }
}
?>

<!DOCTYPE html>
<html>
<body>
<div class="container">
    <form class="form-horizontal" method="post" action>
        <div class="form-group">
            <label class="col-sm-2" for="username">Username:</label>
            <div class="col-sm-10">
                <input class="form-control" maxlength="20" type="text" placeholder="Username" name="username"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2" for="password">Password:</label>
            <div class="col-sm-10">
                <input class="form-control" maxlength="50" type="password" placeholder="Password" name="password"/>
            </div>
        </div>
        <button class="btn btn-large btn-success form-control" type="submit">Submit</button>
    </form>
</div>
</body>
</html>
