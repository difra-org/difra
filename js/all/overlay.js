$(document).on('click', '.overlay', function(event) {
    if ($(event.target).closest('.overlay-inner').length) {
        return;
    }
    ajaxer.close(event.target);
});
