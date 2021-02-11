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

function createWordFormInputs(countries)
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


function changeCountForm(countries)
{
    let element = $("#count");
    let inputsExist = $(".countries_datalist").length;

    if (element.val() === 'word'){
        if (!inputsExist) {
            let div = $(".form-word-switch");
            div.append(createWordFormInputs(countries));
            div.append(createCategoryInput());
            div.append(createLetterInput());
        }
    } else if (element.val() === 'category') {
        if (inputsExist) {
            $(".countries_datalist").remove();
            $(".category").remove();
            $(".letter").remove();
        }
    }
}
