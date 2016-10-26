$(document).ready(function () {
    var addCount = function (event) {
        event.preventDefault();
        fieldName = $(this).attr('data-field');
        type = $(this).attr('data-type');
        var input = $(this).parent().parent().find("input[name='" + fieldName + "']");
        var currentVal = parseInt(input.val());

        if (!isNaN(currentVal)) {
            if (type == 'minus') {

                if (currentVal > input.attr('min')) {
                    input.val(currentVal - 1).change();
                }
                if (parseInt(input.val()) == input.attr('min')) {
                    $(this).attr('disabled', true);
                }
            } else if (type == 'plus') {

                if (currentVal < input.attr('max')) {
                    input.val(currentVal + 1).change();
                }
                if (parseInt(input.val()) == input.attr('max')) {
                    $(this).attr('disabled', true);
                }

            }
        } else {
            input.val(0);
        }
    };

    $('.input-number').change(function () {
        minValue = parseInt($(this).attr('min'));
        maxValue = parseInt($(this).attr('max'));
        valueCurrent = parseInt($(this).val());

        name = $(this).attr('name');
        if (valueCurrent >= minValue) {
            $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the minimum value was reached');
            $(this).val($(this).data('oldValue'));
        }
        if (valueCurrent <= maxValue) {
            $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
        } else {
            alert('Sorry, the maximum value was reached');
            $(this).val($(this).data('oldValue'));
        }
    });


    var addFormGroup = function (event) {
        event.preventDefault();

        var $formGroup = $(this).parent().parent().parent().find('.multiple-form-group.example'); // .form-group
        var $formGroupClone = $formGroup.clone();

        $formGroupClone.show();
        $formGroupClone.removeClass("example");
        $formGroupClone.appendTo($(this).parent().parent().parent().find('.items'));

    };

    var removeFormGroup = function (event) {
        event.preventDefault();

        if ($(this).parent().parent().parent().find('.multiple-form-group').size() > 2) {
            var $lastFormGroupLast = $(this).parent().parent().parent().find('.multiple-form-group:not(.example):last');

            $lastFormGroupLast.remove();
        }
    };

    var selectFormGroup = function (event) {
        event.preventDefault();

        var $selectGroup = $(this).closest('.input-group-select');
        var param = $(this).attr("href").replace("#", "");
        var concept = $(this).text();

        $selectGroup.find('.concept').text(concept);
        $selectGroup.find('.input-group-select-val').val(param);

    };

    var countFormGroup = function ($form) {
        return $form.find('.form-group').length;
    };

    var setActive = function (event) {
        event.preventDefault();
        $(this).addClass("active");
    };

    var resetActive = function (event) {
        event.preventDefault();
        $(".btn-ts-modal").removeClass("active");
    };

    var setActiveValue = function (event) {
        event.preventDefault();
        var activeBut = $(this).find('h6').text();
        var carId = $(this).find('h6').data('id');
        var input = $(".active").parent().parent().parent().find("input");
        input.eq(0).val(carId);
        input.eq(1).val(activeBut);
        $('.close').click();
    };


    $(document).on('click', '.btn-number', addCount);
    $(document).on('click', '.btn-add', addFormGroup);
    $(document).on('click', '.btn-remove', removeFormGroup);
    $(document).on('click', '.btn-ts-modal', setActive);
    $(document).on('click', '.close', resetActive);
    $(document).on('click', '.btn-ts-select', setActiveValue);
});

