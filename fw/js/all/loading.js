/**
 * Script for "Loading..." overlay
 * @type {{}}
 */

var loading = {};

loading.show = function () {
    var overlay = $('#loading-overlay');
    if (!overlay.length) {
        overlay = $('<div id="loading-overlay" style="display:none"><div class="loading-logo">01234567</div></div>');
        $('body').append(overlay);
    }
    overlay.css('pointer-events', 'auto').fadeIn();
};

loading.hide = function () {
    var overlay = $('#loading-overlay');
    if (overlay.length) {
        overlay.css('pointer-events', 'none').fadeOut();
    }
};
