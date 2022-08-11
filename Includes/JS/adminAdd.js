var searching = false;
var gameTitle = "";
var modal = document.getElementById("modal");

$(document).ready(function() {
    $("#popupLoading").hide();
    $("#apiQueryResultFound").hide();
    $("#apiQueryResultNotFound").hide();

    $("#searchButton").click(function(evt) {
        evt.preventDefault();
        console.log($("#gameTitle").val())
        if ($("#gameTitle").val() == "") {
            alert("Please enter a game title");
        } else {
            if (!searching) {
                searching = true;
                modal.style.display = "block";
                $("#popupLoading").show();
                gameTitle = $("#gameTitle").val();
                let formatedTitle = formatName($("#gameTitle").val());
                console.log(`Original: ${$("#gameTitle").val()} \n Modified: ${formatedTitle}`);
                queryAPI(formatedTitle);
            }
        }
    });

    $("#queryNotFoundCloseButton").click(function(evt) {
        evt.preventDefault();
        modal.style.display = "none";
        $("#popupLoading").hide();
        $("#apiQueryResultFound").hide();
        $("#apiQueryResultNotFound").hide();
    })
})

function formatName(gameTitle) {
    if (gameTitle == "")
        return "";
    
    return gameTitle.replace(" ", "%20");
}

function queryAPI(gameTitle) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let temp = JSON.parse(this.responseText);
            console.log(temp);
            processReturn(temp);
        }
    };
    xhttp.open("GET", `https://api.rawg.io/api/games?search=${gameTitle}`, true);
    xhttp.send();
}

function advancedQueryAPI(gameData) {
    $("#popupLoading").show();
    $("#apiQueryResultFound").hide();
    $("#loadingText").text("Please wait while we load the data.");

    let gameID = gameData.id;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let temp = JSON.parse(this.responseText);
            console.log(temp);
            fillData(gameData, temp);
        }
    };
    xhttp.open("GET", `https://api.rawg.io/api/games/${gameID}`, true);
    xhttp.send();
}

function processReturn(returnData) {
    let found = false;

    let results = returnData.results;
    for (let i=0; i < results.length;i++) {
        let cur = results[i];
        if (cur.name == gameTitle) {
            console.log("Game Found!!!!!!");
            //$("#gameDesc").val(cur.description);
            $("#apiQueryResultFound").show();
            $("#queryFoundImage").prop('src',cur.background_image)
            $("#queryFoundGameTitle").text(cur.name);
            found = true;

            $("#queryFoundYesButton").click(function(){
                advancedQueryAPI(cur);
            })

            $("#queryFoundNoButton").click(function(evt){
                evt.preventDefault();
                modal.style.display = "none";
                $("#popupLoading").hide();
                $("#apiQueryResultFound").hide();
                $("#apiQueryResultNotFound").hide();
            })
        }
    }

    if (!found) {
        $("#apiQueryResultNotFound").show();
        console.log("Game not found.");
    }

    $("#popupLoading").hide();
    searching = false;
}

function fillData(gameData, gameData2) {
    $("#gameDesc").val(gameData2.description_raw);
    let images = gameData.short_screenshots;

    console.log(images);

    let imagesText = "";
    for (let i=0;i<images.length;i++) {
        let cur = images[i].image;
        console.log(cur);
        if (i==0) {
            imagesText = cur;
        } else {
            imagesText += `\n${cur}`;
        }
    }
    $("#gameImages").val(imagesText);

    $("#popupLoading").hide();
    modal.style.display = "none";
}