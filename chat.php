<?php
/**
 * Created by PhpStorm.
 * User: samgu
 * Date: 16/01/2018
 * Time: 09:55
 */

require_once("assets/php/utils.class.php");
utils::issueHeader();
?>

<script src="assets/js/main.js"></script>

<!DOCTYPE html>
<html ng-app="mainApp" ng-controller="mainCont">
<body>
    <div class="container">
        <div class="panel panel-default">
            <div class="panel-heading">Chat Client</div>
            <div class="panel-body">
                <form class="form-horizontal">
                    <div class="form-group col-sm-10"><input type="text" placeholder="Message" class="form-control" ng-model="currentMessage"></div>
                    <div class="col-sm-1"></div>
                    <div class="form-group col-sm-1"><button class="btn btn-medium btn-success form-control" data-ng-click="sendmessageclick()">Send</button></div>
                </form>
            </div>
            <div class="panel-body" style="height:600px;overflow:scroll;">
                <div ng-repeat="msg in messages" style="background-color:#cccccc;border-radius:15px;">
                    <p style="width:98%;word-wrap: break-word; padding-left: 2%; padding-top: 7px; padding-bottom:7px;">{{msg.authorname}}: {{msg.content}}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>