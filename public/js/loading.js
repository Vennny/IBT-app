/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * author: Václav Trampeška
 */

function showLoadingIconAndText() {
    $("#errors").hide();
    $("#loader").css("display", "flex").hide().fadeTo("slow", 0.9);
    $("#loadingText").fadeTo("slow", 0.9);
}

function showLoadingWarning(){
    let seconds = 10;

    setTimeout(function() {
        $("#loadingWarning").fadeTo("slow", 0.9);
    }, 1000 * seconds);
}

function sendQuery() {
    showLoadingIconAndText();
    showLoadingWarning();
}
