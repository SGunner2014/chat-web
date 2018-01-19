var app = angular.module("mainApp", ['ngSanitize']);
app.controller("mainCont", ['$scope', '$sce', function($scope, $sce) {
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

            console.log(data);

            if (data.origin === "SERVER" && data.mode === "MESSAGE") {
                msgObj.content = $sce.trustAsHtml("<div>" + isolateLink(data.content) + "</div>");
                $scope.messages.unshift(msgObj);
                $scope.$apply();

            } else if (data.mode === "AUTHENTICATE_RESPONSE") {
                    if (data.content === "FAIL") {
                        $scope.socket.close();
                        window.location.href = "logout.php?reason=invalidtoken";
                    }
            } else if (data.mode === "SESSION_EXPIRED") {
                $scope.socket.close();
                window.location.href = "logout.php?reason=invalidtoken";
            }
        };

        $scope.socket.onclose = function() {
            console.log("Disconnected from server");
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
                "authorname": $scope.currentUser.username,
                "token": $scope.currentUser.token
            };

            var message = JSON.stringify(obj);
            $scope.socket.send(message);
            //$scope.messages.unshift(obj);
        };

    } else {
    }
}]);

app.directive('compileTemplate', function($compile, $parse){
    return {
        link: function(scope, element, attr){
            var parsed = $parse(attr.ngBindHtml);
            function getStringValue() { return (parsed(scope) || '').toString(); }

            //Recompile if the template changes
            scope.$watch(getStringValue, function() {
                $compile(element, null, -9999)(scope);  //The -9999 makes it skip directives so that we do not recompile ourselves
            });
        }
    }
});