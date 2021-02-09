function showLoadingIcon() {
    $("#errors").hide();
    $(".icon").fadeIn("slow");
}

function showLoadingText(seconds){
    setTimeout(function() {
        $(".loader_text").fadeIn("slow");
    }, 1000 * seconds);
}

function sendQuery() {
    showLoadingIcon();
    document.forms["query_builder"].submit();
    showLoadingText(10);
}

