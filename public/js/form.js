function changeForm(element) {
    if(element.value === 'word'){
        document.getElementsByClassName('countries_datalist')[0].style.display = 'block';
        document.getElementsByClassName('category')[0].style.display = 'block';
        document.getElementsByClassName('letter')[0].style.display = 'block';
    }
    else {
        document.getElementsByClassName('countries_datalist')[0].style.display = 'none';
        document.getElementsByClassName('category')[0].style.display = 'none';
        document.getElementsByClassName('letter')[0].style.display = 'none';
    }
}
