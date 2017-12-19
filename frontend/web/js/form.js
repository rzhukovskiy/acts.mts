$(document).ready(function() {
    $('body').off('click', '.date-send');
    $('body').off('change', '.select-period');

    navigationButton();

    addHeaders({
        tableSelector: "#act-grid"
    });

    scopeIndex = 0;
    $('#act-form').on('click', '.addButton', function(e)
    {
        scopeIndex++;
        e.preventDefault();

        var currentEntry = $(this).parents('.form-group:last'),
            newEntry = $(currentEntry.clone()).insertAfter(currentEntry);
        newEntry.find('input').each(function() {
            $(this).attr('name', $(this).attr('name').replace(/[0-9]+/g, scopeIndex));
        });
        newEntry.find('select').each(function() {
            $(this).attr('name', $(this).attr('name').replace(/[0-9]+/g, scopeIndex));
        });

        newEntry.find('input').val('');
        newEntry.find('input.not-null').val(1);
        currentEntry.find('.glyphicon-plus').removeClass('glyphicon-plus').addClass('glyphicon-minus');
        currentEntry.find('.addButton').removeClass('addButton').addClass('removeButton');
    }).on('click', '.removeButton', function(e)
    {
        $(this).parents('.form-group:first').remove();

        e.preventDefault();
        return false;
    });

    imagePreview();

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

    $('body').on('click','.date-send', function(e) {
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
                startDate = new Date(2010, 0, 1);
                endDate = new Date(2030, 0, 1);
        }
        //я не помню, зачем так сделал, но отправка формы запускается событием фокусаут на поле с датой
        $('.date-from').val(startDate.toISOString());
        $('.date-to').val(endDate.toISOString());
        $('.date-to').change();
        $('.date-to').focusout();
        $('.autoinput').remove();
    });

    $('.main-number').focusout(function() {
        if ($(this).val().length >= 8) {
            $.ajax({
                type: "GET",
                url: '/car/check-extra?number=' + $(this).val(),
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.res == 1) {
                        $('.extra-number').show();
                    } else {
                        $('.extra-number').hide();
                    }
                }
            });
        } else {
            $('.extra-number').hide();
        }
    });

    $(".reset").change(function() {
        $("input.resetable").val('');
    });
});

this.imagePreview = function() {
    /* CONFIG */
    xOffset = 400;
    yOffset = 400;

    // these 2 variable determine popup's distance from the cursor
    // you might want to adjust to get the right result

    /* END CONFIG */
    $("a.preview").hover(function(e) {
            this.t = this.title;
            this.title = "";
            var c = (this.t != "") ? "<br/>" + this.t : "";
            $("body").append("<p id='preview'><img style='width: 200px' src='" + this.href + "' alt='Image preview' />" + c + "</p>");
            $("#preview")
                .css("top", "100px")
                .css("left", "10px")
                .css("position","fixed")
                .fadeIn("fast");
        },
        function() {
            this.title = this.t;
            $("#preview").remove();
        });
    $("a.preview").mousemove(function(e) {
        $("#preview")
            .css("top", "100px")
            .css("left", "10px");
    });
};

this.addHeaders = function(options) {
    var tableSelector = options.tableSelector;
    var defaultHeaderClass = 'kv-group-header';
    var defaultFooterClass = 'kv-group-footer';
    var defaultGroupClass = '.grouped';

    var total = [];
    var totalCount = [];
    var trs = $(tableSelector).find('tbody tr').not('.' + defaultHeaderClass).not('.' + defaultFooterClass);
    trs.each(function(tr_id, row) {
        var previous = trs.eq(tr_id - 1);
        var next = trs.eq(tr_id + 1);

        $(row).find('td' + defaultGroupClass).each(function(td_id, td) {
            var col = $(td).attr('data-col-seq');
            var pos = $(row).find('td').not('.hidden').index($(row).find('td.sum'));
            var posCount = $(row).find('td').not('.hidden').index($(row).find('td.count'));
            var tag = 'td[data-col-seq=' + col + ']';

            var currentValue = $(td).text();
            var nextValue = next.find(tag).text();
            var previousValue = previous.find(tag).text();

            if (!total[col]) {
                total[col] = 0;
            }
            if (!totalCount[col]) {
                totalCount[col] = 0;
            }
            total[col] += parseFloat($(row).find('td.sum').text());
            totalCount[col]++;
            if (currentValue != nextValue) {
                var footerTr = $('<tr>').addClass(defaultFooterClass);
                if ($(td).attr('data-parent')) {
                    footerTr.addClass('child');
                }
                for (var i = 0; i < $(row).find('td').not('.hidden').length; i++) {
                    if (i == pos) {

                        var intSum = parseInt(total[col]);

                        if(total[col] > intSum) {
                            var footerTd = $('<td>').text(total[col].toFixed(4));
                            footerTr.append(footerTd);
                        } else {
                            var footerTd = $('<td>').text(total[col]);
                            footerTr.append(footerTd);
                        }

                    } else if (i == posCount) {
                        var footerTd = $('<td>').text(totalCount[col]);
                        footerTr.append(footerTd);
                    } else if (i == 0) {
                        var footerTd = $('<td>').text($(td).attr('data-footer')).attr("colspan", posCount);
                        footerTr.append(footerTd);
                    } else if (i > pos) {
                        var footerTd = $('<td>');
                        footerTr.append(footerTd);
                    }
                }

                $(row).after(footerTr);

                total[col] = 0;
                totalCount[col] = 0;
            }

            if (tr_id == 0 || previousValue != currentValue) {
                var headerTd = $('<td>').text($(td).attr('data-header')).attr("colspan", $(row).find('td').length);
                var headerTr = $('<tr>').addClass(defaultHeaderClass).append(headerTd);
                if ($(td).attr('data-parent')) {
                    headerTr.addClass('child');
                }
                $(row).before(headerTr);
            }
        });
    });
};
//Навигационные кнопки
function navigationButton(){
    $(function() {
        var $elem = $('body');

        $('#nav_up').fadeIn('slow');
        $('#nav_down').fadeIn('slow');

        $(window).bind('scrollstart', function(){
            $('#nav_up,#nav_down').stop();
        });
        $(window).bind('scrollstop', function(){
            $('#nav_up,#nav_down').stop();
        });

        $('#nav_down').click(
            function (e) {
                $('html, body').animate({scrollTop: $elem.height()}, 800);
            }
        );
        $('#nav_up').click(
            function (e) {
                $('html, body').animate({scrollTop: '0px'}, 800);
            }
        );
    });
}