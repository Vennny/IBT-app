
$(document).on('input', '#category', function()
{
    toggleSwitchInput();
});

function toggleSwitchInput()
{
    if (
        $("#chart_type").val() === 'popular' &&
        $("#count").val() === 'answer' &&
        Boolean($("#category").val())
    ) {
        if (!$('.custom-switch').length) {
            $('.percentage-switch').append(createSwitchInput());
        }
    } else {
        $(".custom-switch").remove();
    }

}

function changeFormType(countries)
{
    let element = $("#chart_type");

    if (element.val() === 'popular') {
        $('#count').append('<option id="count_category" value="category">category</option>');
    } else if (element.val() === 'total') {
        $('#count_category').remove();
    }

    //removing "category" option makes select switch to "word" option automatically - change of form needed
    changeCountForm(countries);
}

function createAnswerFormInputs(countries)
{
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

function createCategoryInput()
{
    return $('\n' +
        '                <div class="category">\n' +
        '                    <label for="category">Category name:</label>\n' +
        '                    <input type="text" id="category" name="category" value=""><br>\n' +
        '                </div>');
}

function createLetterInput()
{
    return $('\n' +
        '                <div class="letter">\n' +
        '                    <label for="letter">Starting letter:</label>\n' +
        '                    <input type="text" id="letter" name="letter" maxlength="1" value=""><br>\n' +
        '                </div>');
}

function createSwitchInput()
{
    return $('\n' +
        '                <div class="custom-control custom-switch">\n' +
        '                    <input type="checkbox" class="custom-control-input" name="percentage" id="percentage" >\n' +
        '                    <label class="custom-control-label" for="percentage">Show results in percentage out of all related answers</label>\n' +
        '                </div>');
}

function changeCountForm(countries)
{
    let count = $("#count");
    let inputsExist = $(".countries_datalist").length || $(".category").length || $(".letter").length ;

    if (count.val() === 'answer'){
        if (!inputsExist) {
            let div = $(".form-answer-switch");
            div.append(createAnswerFormInputs(countries));
            div.append(createCategoryInput());
            div.append(createLetterInput());
        }
    } else if (count.val() === 'category') {
        if (inputsExist) {
            $(".countries_datalist").remove();
            $(".category").remove();
            $(".letter").remove();
        }
    }

    toggleSwitchInput();
}

