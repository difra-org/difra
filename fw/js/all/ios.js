/**
 * This scripts supports specific behavior for some elements on iOS devices.
 * Very useful for position: fixed elements layout.
 *
 * .scrollable                Element will be scrollable.
 * .unscrollable        Element will not be scrollable and react on "rubber" pull actions.
 */

// Fix page rubber scroll on iOS devices for elements with .scrollable class
$(document).on('touchstart', '.scrollable', function (event) {
    if ($(event.target).closest('.scrollable,.unscrollable').hasClass('.unscrollable')) {
        return;
    }
    var startY = event.touches[0].pageY;
    var startTopScroll = this.scrollTop;

    if (startTopScroll <= 0) {
        this.scrollTop = 1;
    }

    if (startTopScroll + this.offsetHeight >= this.scrollHeight) {
        this.scrollTop = this.scrollHeight - this.offsetHeight - 1;
    }
});

// Disable page scrolling on iOS devices for elements with .unscrollable class
$(document).on('touchmove', '.unscrollable', function (event) {
    if ($(event.target).parent().closest('.scrollable,.unscrollable').hasClass('.unscrollable')) {
        event.preventDefault();
    }
});
