function showLoadingIcon() {
    $("#errors").hide();
    $("#loader").css("display", "flex").hide().fadeIn("slow");
}

function showLoadingText(seconds){
    setTimeout(function() {
        $(".loader_text").css("opacity", "1");
    }, 1000 * seconds);
}

function sendQuery() {
    showLoadingIcon();
    document.forms["query_builder"].submit();
    showLoadingText(10);
}

