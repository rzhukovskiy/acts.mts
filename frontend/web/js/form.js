$(document).ready(function() {
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
        console.log($(this).val());
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