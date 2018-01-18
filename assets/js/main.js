var app = angular.module("mainApp", []);
app.controller("mainCont", function($scope) {
    $scope.interact = function() {
        var http = new XMLHttpRequest();
        var url = "//localhost/chat-web/assets/php/getLoginCredentials.php";

        http.open("POST", url, false); //Open a synchronous request to the URL and make it a POST request
        http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"); //Set the request header
        http.send(); //Send request to server, along with data.

        var returnedData = http.responseText; //Get the response text
        console.log(returnedData);

        return JSON.parse(returnedData);//Parse the response text
    };
    $scope.messages = [];
    $scope.currentMessage = "test";

    var results = $scope.interact();
    if (results.succ) {
        $scope.currentUser = results;
        $scope.socket = new WebSocket("ws://localhost:8025/chat");

        $scope.openMessage = function() {
            var userobj = {
                "token": results.token,
                "mode": "AUTHENTICATE",
                "username": $scope.currentUser.username,
                "userid": $scope.currentUser.userid
            };
            console.log("test");
            $scope.socket.send(JSON.stringify(userobj));
        };

        $scope.socket.onopen = function() {
            $scope.openMessage();
        };

        $scope.socket.onmessage = function(evt) {
            var msg = evt.data;
            var data = JSON.parse(msg);

            var msgObj = {
                "content": data.content,
                "authorid": data.authorid,
                "authorname": data.authorname
            };

            if (data.authorid !== $scope.currentUser.userid && data.authorid !== null && data.userid !== $scope.currentUser.userid) {
                $scope.messages.unshift(msgObj);
                $scope.$apply();
            }
        };

        $scope.socket.onclose = function() {
            alert("Lost connection to the server")
        };

        $scope.sendmessageclick = function() {
            if ($scope.currentMessage.trim().length > 0) {
                $scope.sendMessage($scope.currentMessage);
                $scope.currentMessage = null;
            }
        };

        $scope.sendMessage = function(text) {
            var obj = {
                "content": text,
                "mode": "MESSAGE",
                "authorid": $scope.currentUser.userid,
                "authorname": $scope.currentUser.username
            };

            var message = JSON.stringify(obj);
            $scope.socket.send(message);
            $scope.messages.unshift(obj);
        };

    } else {
    }
});