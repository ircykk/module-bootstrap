// Autocmplete
$(document).ready(function () {
    $('.ac_input_class').autocomplete(
        currentIndex+'&token='+token+'&ajax=1&action=getAutocompleteProducts', {
            minChars: 2,
            max: 50
        }
    ).result(function (event, data, selected){
        if (data[1] !== undefined) {
            $(event.target).val(data[1]);
        }
    })
})