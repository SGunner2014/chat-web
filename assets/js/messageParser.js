function isolateLink(message) {
    message = " " + message + " ";
    console.log("." + message + ".");
    var regex1 = /https?:[//][a-zA-Z0-9]+([.][a-zA-Z]+)+([/].[/])*[.](png|jpg|gif)/gi;
    var regex = /(https?:\/\/.*\.(?:png|jpg))/gi;
    var links = message.match(regex);

    if (links !== null) {
        for (var i = 0; i < links.length; i++) {
            message = message.replace(links[i], "<img src='" + links[i] + "'/>");
        }
    }

    return message;
}