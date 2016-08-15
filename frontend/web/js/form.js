$(document).ready(function() {
    scopeIndex = 0;
    oldScopeIndex = 0;
    $('#act-form').on('click', '.addButton', function(e)
    {
        scopeIndex++;
        e.preventDefault();

        var currentEntry = $(this).parents('.form-group:last'),
            newEntry = $(currentEntry.clone()).insertAfter(currentEntry);
        newEntry.find('input').each(function() {
            $(this).attr('name', $(this).attr('name').replace(oldScopeIndex, scopeIndex));
        });

        newEntry.find('input').val('');
        currentEntry.find('.glyphicon-plus').removeClass('glyphicon-plus').addClass('glyphicon-minus');
        currentEntry.find('.addButton').removeClass('addButton').addClass('removeButton');
        oldScopeIndex++;
    }).on('click', '.removeButton', function(e)
    {
        $(this).parents('.form-group:first').remove();

        e.preventDefault();
        return false;
    });
});