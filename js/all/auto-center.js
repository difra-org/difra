/**
 * This script will center elements with .auto-center, .auto-center-x, .auto-center-y classes inside container.
 * Used for auto generated modal dialogs. Place new dialog inside of position:fixed overlay and call autocenter.run().
 */

var autocenter = {};

autocenter.enabled = true;

autocenter.run = function () {
    if (!autocenter.enabled) {
        return;
    }
    $('.auto-center').each(function (index, elem) {
        $(elem).css({
            left: ($(window).width() - $(elem).outerWidth(false)) / 2,
            top: ($(window).height() - $(elem).outerHeight(false)) / 2
        });
    });
    $('.auto-center-x').each(function (index, elem) {
        $(elem).css({
            left: ($(window).width() - $(elem).outerWidth(false)) / 2
        });
    });
    $('.auto-center-y').each(function (index, elem) {
        $(elem).css({
            top: ($(window).height() - $(elem).outerHeight(false)) / 2
        });
    });
};

$(window).on('resize', autocenter.run);
