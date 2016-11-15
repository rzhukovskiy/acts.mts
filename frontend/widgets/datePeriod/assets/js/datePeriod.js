$('body').off('click', '.date-send');
$('body').off('change', '.select-period');

$('body').one('click', '.date-send', function (e) {
    var startDate = new Date();
    var endDate = new Date();
    switch ($('.select-period').val()) {
        case '1':
            startDate = new Date($('#year option:selected').text(), $('#month').val(), 1);
            if ($('#month').val() == 11) {
                endDate = new Date(parseInt($('#year option:selected').text()) + 1, 0, 1);
            } else {
                endDate = new Date($('#year option:selected').text(), parseInt($('#month').val()) + 1, 1);
            }
            break;
        case '2':
            startDate = new Date($('#year option:selected').text(), $('#quarter').val() * 3, 1);
            if ($('#quarter').val() == 3) {
                endDate = new Date(parseInt($('#year option:selected').text()) + 1, 0, 1);
            } else {
                endDate = new Date($('#year option:selected').text(), parseInt($('#quarter').val()) * 3 + 3, 1);
            }
            break;
        case '3':
            startDate = new Date($('#year option:selected').text(), $('#half').val() * 6, 1);
            if ($('#half').val() == 1) {
                endDate = new Date(parseInt($('#year option:selected').text()) + 1, 0, 1);
            } else {
                endDate = new Date($('#year option:selected').text(), parseInt($('#half').val()) * 6 + 6, 1);
            }
            break;
        case '4':
            startDate = new Date($('#year option:selected').text(), 0, 1);
            endDate = new Date(parseInt($('#year option:selected').text()) + 1, 0, 1);
            break;
        default:
            $('.date-from').remove();
            $('.date-to').remove();
            break;
        //return true;
    }
    $('.date-from').val(startDate.toISOString());
    $('.date-to').val(endDate.toISOString());
    $('.date-to').change();
    $('.date-to').focusout();
    $('.autoinput').remove();
});

$('body').on('change','.select-period', function(e) {
    switch ($(this).val()) {
        case '1':
            $('#year').fadeIn();
            $('#month').fadeIn();
            $('#half').fadeOut();
            $('#quarter').fadeOut();
            break;
        case '2':
            $('#year').fadeIn();
            $('#quarter').fadeIn();
            $('#month').fadeOut();
            $('#half').fadeOut();
            break;
        case '3':
            $('#year').fadeIn();
            $('#half').fadeIn();
            $('#month').fadeOut();
            $('#quarter').fadeOut();
            break;
        case '4':
            $('#year').fadeIn();
            $('#month').fadeOut();
            $('#quarter').fadeOut();
            $('#half').fadeOut();
            break;
        default:
            $('.autoinput').not('.select-period').fadeOut();
    }
});