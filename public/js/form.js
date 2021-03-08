let countries;
let countriesDatalist;

$('#queryBuilder').submit(function(event) {
    //firefox workaround for executing script before submitting the form
    event.preventDefault();
    sendQuery();
    $(this).unbind('submit').submit();
})

function setCountries(countriesArray) {
    countries = countriesArray;
    countriesDatalist = createCountriesDatalist(countriesArray);
}

$("#graphType").change(function (){
    changeFormType();
});

$(document).on('change', '#count', function() {
    changeCountForm();
});

$(document).on('input', '#category', function() {
    toggleSwitchInput();
});

$(document).on('change', '#category', function() {
    trimWhitespaceInInput($(this));
});

$(document).on('change', '#letter', function() {
    trimWhitespaceInInput($(this));
});

$(document).on('change', '#word', function() {
    trimWhitespaceInInput($(this));
});

$(document).on('change', '.country-input', function() {
    resolveCountryInputs($(this));
});

function trimWhitespaceInInput(input) {
    if (! /\S/.test(input.val())){
        input.val('');
    }
}

function resolveCountryInputs(input) {
    trimWhitespaceInInput(input);

    let notFilledCount = 0;
    $(".countries > input").each(function (){
        if (! $(this).val().length) {
            notFilledCount++;
        }
    });

    if (input.val().length && notFilledCount === 0){
        // if there are zero empty inputs to fill, append new input
        $('.countries').append(createCountryInput());
    }
    else if(notFilledCount === 2) {
        // if there are two empty inputs, remove one
        input.remove();
    }
}

function toggleSwitchInput() {
    let graphType = $('#graphType');
    if (
        graphType.val() === 'popular'
        && $('#count').val() === 'answer'
        && $('#category').val().length
    ) {
        if (!$('.custom-switch').length) {
            $('.percentage-switch').append(createSwitchInput());
        }
    }
    else {
        $(".custom-switch").remove();
    }

    // if (
    //     (
    //         graphType.val() === 'popular' &&
    //         $('#count').val() === 'answer' &&
    //         $('#category').val().length
    //     ) ||
    //     graphType.val() === 'time'
    // )
}

function changeFormType() {
    let element = $("#graphType");

    if (element.val() === 'popular') {
        switchFormFromTimeChart()
        if (! $('#countCategory').length){
            $('#count').append('<option id="countCategory" value="category">category</option>');
        }
    }
    else if (element.val() === 'total') {
        switchFormFromTimeChart()
        $('#countCategory').remove();
    }
    else if (element.val() === 'time') {
        switchFormToTimeChart();
    }

    //removing "category" option makes select switch to "word" option automatically - change of form needed
    changeCountForm();
}

function switchFormToTimeChart() {
    $('.count').remove();
    $('.limit').remove();

    if (! $('.word').length) {
        $("#wordDiv").append(createWordInput())
    }
}

function switchFormFromTimeChart() {
    let wordDiv = $('.word');
    if (wordDiv.length){
        $('#countDiv').append(createCountInput());
        $("#limitDiv").append(createLimitInput());
    }
    wordDiv.remove();
}

function createWordInput() {
    return $('<div class="word">\n' +
        '        <label for="word">Case insensitive word to search in time:</label>\n' +
        '        <select id="operator" name="operator">\n' +
        '            <option value="equals">equals</option>\n' +
        '            <option value="startsWith">starts with</option>\n' +
        '            <option value="contains">contains</option>\n' +
        '        </select>\n' +
        '        <input type="text" id="word" name="word" value="" required>\n' +
        '    </div>'
    );
}

function createCountriesDatalist(countries) {
    let datalist = $('<datalist id="countryList"><datalist>\n');

    countries.forEach(function (country) {
        datalist.append('<option value="' + country.name + '">\n');
    });

    return datalist;
}

function createCountryInput() {
    return $('<input class="country-input" id="country" list="countryList" name="country[]" />');
}

function createFirstCountryInput() {
    let div = $('<div class="countries"> </div>');

    let label = $('<label for="country">From player from country:</label>');
    div.append(label);
    div.append(countriesDatalist);
    div.append(createCountryInput());

    return div;
}

function createCategoryInput() {
    return $('<div class="category">\n' +
        '        <label for="category">Category name:</label>\n' +
        '        <input type="text" id="category" name="category" value=""><br>\n' +
        '    </div>'
    );
}

function createLetterInput() {
    return $('<div class="letter">\n' +
        '        <label for="letter">Starting letter:</label>\n' +
        '        <input type="text" id="letter" name="letter" maxlength="1" value=""><br>\n' +
        '    </div>'
    );
}

function createCountInput() {
    return $('<div class="count">\n' +
        '        <label for="count">Count most selected: </label>\n' +
        '        <select id="count" name="count">\n' +
        '            <option id="countCategory" value="category">category</option>\n' +
        '            <option id="countAnswer" value="answer">answers</option>\n' +
        '        </select><br>\n' +
        '    </div>'
    );
}

function createLimitInput() {
    return $('<div class="limit">\n' +
        '        <label for="limit">Select number of entries</label>\n' +
        '        <input type="number" id="limit" name="limit" min="1" value="5" required><br>\n' +
        '    </div>'
    );
}

function createSwitchInput() {
    return $('<div class="custom-control custom-switch">\n' +
        '        <input type="checkbox" class="custom-control-input" name="percentage" id="percentage" >\n' +
        '        <label class="custom-control-label" for="percentage">Show results in percentage out of all related answers</label>\n' +
        '    </div>'
    );
}

function changeCountForm() {
    let count = $("#count");
    let inputsExist = $(".countries").length || $(".category").length || $(".letter").length ;

    if (!count.length || count.val() === 'answer'){
        if (!inputsExist) {
            let form = $(".form-answer-switch");
            form.append(createFirstCountryInput());
            form.append(createCategoryInput());
            form.append(createLetterInput);
        }
    }
    else if (count.val() === 'category') {
        if (inputsExist) {
            $(".countries").remove();
            $(".category").remove();
            $(".letter").remove();
        }
    }

    toggleSwitchInput();
}
