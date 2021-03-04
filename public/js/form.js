$('#query_builder').submit(function(event) {
    //firefox workaround for executing script before submitting the form
    event.preventDefault();
    sendQuery();
    $(this).unbind('submit').submit();
})

$(document).on('input', '#category', function() {
    toggleSwitchInput();
});

function toggleSwitchInput() {
    let chartType = $('#chart_type');
    if (
        chartType.val() === 'popular' &&
        $('#count').val() === 'answer' &&
        $('#category').val().length
    ) {
        if (!$('.custom-switch').length) {
            $('.percentage-switch').append(createSwitchInput());
        }
    }
    else {
        $(".custom-switch").remove();
    }

}

function changeFormType(countries) {
    let element = $("#chart_type");

    if (element.val() === 'popular') {
        switchFormFromTimeChart()
        if (! $('#count_category').length){
            $('#count').append('<option id="count_category" value="category">category</option>');
        }
    }
    else if (element.val() === 'total') {
        switchFormFromTimeChart()
        $('#count_category').remove();
    }
    else if (element.val() === 'time') {
        switchFormToTimeChart(countries);
    }

    //removing "category" option makes select switch to "word" option automatically - change of form needed
    changeCountForm(countries);
}

function switchFormToTimeChart() {
    $('.count').remove();
    $('.limit').remove();

    if (! $('.word').length) {
        $("#word_div").append(createWordInput())
    }
}

function switchFormFromTimeChart() {
    let wordDiv = $('.word');
    if (wordDiv.length){
        $('#count_div').append(createCountInput());

        $("#limit_div").append(createLimitInput());
    }
    wordDiv.remove();
}

function createWordInput() {
    return $('\n' +
        '                <div class="word">\n' +
        '                    <label for="word">Case insensitive word to search in time:</label>\n' +
        '                    <select id="operator" name="operator">\n' +
        '                        <option value="equals">equals</option>\n' +
        '                        <option value="starts">starts with</option>\n' +
        '                        <option value="both">contains</option>\n' +
        '                    </select>\n' +
        '                    <input type="text" id="word" name="word" value="" required>\n' +
        '                </div>')
}

function createCountryInput(countries) {
    let datalist = $('<div class="countries_datalist">\n' +
        '                    <label for="country">From player from country: </label>\n' +
        '                    <input list="country" name="country" class="datalist-input" />\n');

    let options = $('<datalist id="country"><datalist>\n');

    options.append('<option value="">')
    countries.forEach(function (country) {
        options.append('<option value="' + country.name + '">\n');
    });

    datalist.append(options);
    return datalist;
}

function createCategoryInput() {
    return $('\n' +
        '                <div class="category">\n' +
        '                    <label for="category">Category name:</label>\n' +
        '                    <input type="text" id="category" name="category" value=""><br>\n' +
        '                </div>');
}

function createLetterInput() {
    return $('\n' +
        '                <div class="letter">\n' +
        '                    <label for="letter">Starting letter:</label>\n' +
        '                    <input type="text" id="letter" name="letter" maxlength="1" value=""><br>\n' +
        '                </div>');
}

function createCountInput() {
    return $('\n' +
        '                <div class="count">\n' +
        '                    <label for="count">Count most selected: </label>\n' +
        '                    <select id="count" name="count">\n' +
        '                        <option id="count_category" value="category">category</option>\n' +
        '                        <option id="count_answer" value="answer">answers</option>\n' +
        '                    </select><br>\n' +
        '                </div>');
}

function createLimitInput() {
    return $('\n' +
        '                <div class="limit">\n' +
        '                    <label for="limit">Select number of entries</label>\n' +
        '                    <input type="number" id="limit" name="limit" min="1" value="5" required><br>\n' +
        '                </div>');
}

function createSwitchInput() {
    return $('\n' +
        '                <div class="custom-control custom-switch">\n' +
        '                    <input type="checkbox" class="custom-control-input" name="percentage" id="percentage" >\n' +
        '                    <label class="custom-control-label" for="percentage">Show results in percentage out of all related answers</label>\n' +
        '                </div>');
}

function changeCountForm(countries) {
    let count = $("#count");
    let inputsExist = $(".countries_datalist").length || $(".category").length || $(".letter").length ;

    if (!count.length || count.val() === 'answer'){
        if (!inputsExist) {
            let form = $(".form-answer-switch");
            form.append(createCountryInput(countries));
            form.append(createCategoryInput());
            form.append(createLetterInput());
        }
    }
    else if (count.val() === 'category') {
        if (inputsExist) {
            $(".countries_datalist").remove();
            $(".category").remove();
            $(".letter").remove();
        }
    }

    toggleSwitchInput();
}
