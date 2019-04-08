$(document).on('click dblclick', '#menu_adm > ul > li', function () {
    $(this).addClass('clicked');
    $('#menu_adm').find('> ul > li').each(function () {
        if (!$(this).hasClass('clicked')) {
            $(this).children('ul').slideUp('fast');
        }
    });
    $(this).removeClass('clicked');
    var child = $(this).children('ul');
    if (child.css('display') == 'none') {
        child.slideDown('fast');
    } else {
        child.slideUp('fast');
    }
});

// $(document).on('click dblclick', '#menu_adm > ul > li > ul', function () {
    // return false;
// });
