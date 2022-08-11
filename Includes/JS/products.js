$(document).ready(function () {
    $("#loginFormWrapper").toggle();

    $("#loginButton").click(function (evt) {
        evt.preventDefault();
        $("#loginFormWrapper").slideToggle(300);
    });


    $("#RPG").click(function(){
        $(".RPG").show();
        $(".FPS").hide();
        $(".MMO").hide();
        $(".SPORTS").hide();
        $(".PLATFORMER").hide();
        $(".SANDBOX").hide();
    });
    $("#FPS").click(function(){
        $(".RPG").hide();
        $(".FPS").show();
        $(".MMO").hide();
        $(".SPORTS").hide();
        $(".PLATFORMER").hide();
        $(".SANDBOX").hide();
    });
    $("#MMO").click(function(){
        $(".RPG").hide();
        $(".FPS").hide();
        $(".MMO").show();
        $(".SPORTS").hide();
        $(".PLATFORMER").hide();
        $(".SANDBOX").hide();
        
    });
    $("#SPORTS").click(function(){
        $(".RPG").hide();
        $(".FPS").hide();
        $(".MMO").hide();
        $(".SPORTS").show();
        $(".PLATFORMER").hide();
        $(".SANDBOX").hide();
    });
    $("#SANDBOX").click(function(){
        $(".RPG").hide();
        $(".FPS").hide();
        $(".MMO").hide();
        $(".SPORTS").hide();
        $(".PLATFORMER").hide();
        $(".SANDBOX").show();
    });
    $("#PLATFORMER").click(function(){
        $(".RPG").hide();
        $(".FPS").hide();
        $(".MMO").hide();
        $(".SPORTS").hide();
        $(".PLATFORMER").show();
        $(".SANDBOX").hide();
    });
})