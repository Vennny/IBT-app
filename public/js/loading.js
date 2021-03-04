function showLoadingIconAndText() {
    $("#errors").hide();
    $("#loader").css("display", "flex").hide().fadeTo("slow", 0.9);
    $("#loading_text").fadeTo("slow", 0.9);
}

function showLoadingWarning(){
    const seconds = 10;

    setTimeout(function() {
        $("#loading_warning").fadeTo("slow", 0.9);
    }, 1000 * seconds);
}

function sendQuery() {
    showLoadingIconAndText();
    document.forms["query_builder"].submit();
    showLoadingWarning();
}
