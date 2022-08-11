$(document).ready(function () {
    $("#loginFormWrapper").toggle();

    $("#loginButton").click(function (evt) {
        evt.preventDefault();
        $("#loginFormWrapper").slideToggle(300);
    });
})
