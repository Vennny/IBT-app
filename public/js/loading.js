function showLoadingIcon() {
    $("#errors").hide();
    $("#loader").css("display", "flex").hide().fadeTo("slow", 0.9);
}

function showLoadingText(){
    const seconds = 10;

    setTimeout(function() {
        $(".loader_text").css("opacity", "1");
    }, 1000 * seconds);
}

function sendQuery() {
    showLoadingIcon();
    document.forms["query_builder"].submit();
    showLoadingText();
}

