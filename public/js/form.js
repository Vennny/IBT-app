let countriesDatalist;

$('#queryBuilder').submit(function(event) {
    //firefox workaround for executing script before submitting the form
    event.preventDefault();
    sendQuery();
    $(this).unbind('submit').submit();
})

function setCountries(countriesArray) {
    countriesDatalist = createCountriesDatalist(countriesArray);
}

$("#graphType").change(function (){
    changeFormType($(this));
});

$(document).on('change', '#countTable', function() {
    changeCountTableForm();
});

$(document).on('change', '.category-input', function() {
    resolveAddingInputs($(this));
    toggleSwitchInput();
});

$(document).on('change', '#letter', function() {
    trimWhitespaceInInput($(this));
});

$(document).on('change', '#word', function() {
    trimWhitespaceInInput($(this));
});

$(document).on('change', '#language', function() {
    switchWarning($(this));
});

$(document).on('change', '.country-input', function() {
    resolveAddingInputs($(this));
});

$(document).on('change', '.word-input', function() {
    resolveAddingInputs($(this));
});

function switchWarning(element) {
    if (element.val() === 'all') {
        if (! element.parent().children('warning').length){
            element.parent().append('<p class="warning">Warning: This query setting may take a long time to execute!</p>')
        }
    }
    else{
        element.parent().children('.warning').remove();
    }

}

function toggleSwitchInput() {
    let graphType = $('#graphType');

    if ((graphType.val() === 'popular'
        && $('#countTable').val() === 'answer'
        && $('.category-input').val().length
        ) ||
        graphType.val() === 'time'
    ) {
        if (!$('.custom-switch').length) {
            $('.percentage-switch').append(createSwitchInput());
        }
    }
    else {
        $(".custom-switch").remove();
    }

}

function changeFormType(element) {
    if (element.val() === 'popular') {
        switchFormFromTimeChart()
        if (! $('#countCategory').length){
            $('#countTable').append('<option id="countCategory" value="category">category</option>');
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
    changeCountTableForm();
}

function switchFormToTimeChart() {
    $('.count-table').remove();
    $('.limit').remove();

    if (! $('.word').length) {
        $("#wordDiv").append(createFirstWordInput())
    }
}

function switchFormFromTimeChart() {
    let wordDiv = $('.word');

    if (wordDiv.length){
        $('#countTableDiv').append(createCountTableInput());
        $("#limitDiv").append(createLimitInput());
    }

    wordDiv.remove();
}

function trimWhitespaceInInput(input) {
    if (! /\S/.test(input.val())){
        input.val('');
    }
}

function resolveAddingInputs(input) {
    trimWhitespaceInInput(input);

    let inputParent = input.parent();

    let notFilledCount = 0;

    inputParent.children(':input').each(function (){
        if (! $(this).val().length) {
            notFilledCount++;
        }
    });

    if (input.val().length && notFilledCount === 0){
        // if there are zero empty inputs to fill, append new input
        if (inputParent.hasClass('word')){
            inputParent.append(createWordInput());
        } else if (inputParent.hasClass('countries')){
            inputParent.append(createCountryInput());
        } else if (inputParent.hasClass('categories')){
            inputParent.append(createCategoryInput());
        }
    }
    else if(notFilledCount === 2) {
        // if there are two empty inputs, remove one
        if (inputParent.hasClass('word')){
            input.prev($('.operator')).remove();
        }

        input.remove();
    }

    //word is required, make sure the first input always has it
    if (inputParent.hasClass('word') ) {
        console.log(inputParent.children(':input').length);
        if (inputParent.children(':input').length === 2) {
            inputParent.children(':input').prop('required', true);
        }
    }
}

function createWordInput() {
    return $('<select class="operator" name="operator[]">\n' +
             '    <option value="equals">equals</option>\n' +
             '    <option value="startsWith">starts with</option>\n' +
             '    <option value="endsWith">ends with</option>\n' +
             '    <option value="contains">contains</option>\n' +
             '</select>\n' +
             '<input type="text" class="word-input" name="word[]" value="">\n');
}

function createFirstWordInput() {
    let div = $('<div class="word"></div>');
    let label = $('<label for="word">Case insensitive word to search in time:</label>')

    div.append(label);
    div.append(createWordInput());
    div.children(':input').prop('required', true);

    return div;
}

function createCategoryInput() {
    return $('<input type="text" class="category-input" name="category[]" value="">');
}

function createFirstCategoryInput() {
    let div= $('<div class="categories"></div>');
    let label = $('<label for="category">Category name:</label>');

    div.append(label);
    div.append(createCategoryInput());

    return div;
}

function createCountriesDatalist(countries) {
    let datalist = $('<datalist id="countryList"><datalist>\n');

    countries.forEach(function (country) {
        datalist.append('<option value="' + country.name + '">\n');
    });

    return datalist;
}

function createCountryInput() {
    return $('<input class="country-input" list="countryList" name="country[]" />');
}

function createFirstCountryInput() {
    let div = $('<div class="countries"> </div>');
    let label = $('<label for="country">From player from country:</label>');

    div.append(label);
    div.append(countriesDatalist);
    div.append(createCountryInput());

    return div;
}

function createLetterInput() {
    return $('<div class="letter">\n' +
        '        <label for="letter">Starting letter:</label>\n' +
        '        <input type="text" id="letter" name="letter" maxlength="1" value=""><br>\n' +
        '    </div>'
    );
}

function createCountTableInput() {
    return $('<div class="count-table">\n' +
        '        <label for="count">Count most selected: </label>\n' +
        '        <select id="countTable" name="countTable">\n' +
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

function changeCountTableForm() {
    let countTable = $("#countTable");
    let inputsExist = $(".countries").length || $(".categories").length || $(".letter").length ;

    if (!countTable.length || countTable.val() === 'answer'){
        if (!inputsExist) {
            let form = $(".form-answer-switch");
            form.append(createFirstCountryInput());
            form.append(createFirstCategoryInput());
            form.append(createLetterInput);
        }
    }
    else if (countTable.val() === 'category') {
        if (inputsExist) {
            $(".countries").remove();
            $(".categories").remove();
            $(".letter").remove();
        }
    }

    toggleSwitchInput();
}
