/**
 * This script will fix some counters to be used with Switcher.js.
 */

$(document).on('construct', function () {

    // Google Analytics (ga.js, legacy)

    //noinspection JSUnresolvedVariable
    if (typeof _gaq == 'object' && typeof _gaq.push == 'function') {
        //noinspection JSUnresolvedVariable
        _gaq.push(['_trackPageview', switcher.url]);
    }
    
    // Google Analytics (analytics.js)
    
    //noinspection JSUnresolvedVariable
    if (typeof ga == 'function') {
        //noinspection JSUnresolvedVariable
        ga('set', 'page', switcher.url);
        ga('send', 'pageview');
    }

    // Yandex Metrika

    for (var y in window) {
        if (typeof( window[y] ) != 'object' || !y.match(/^yaCounter[0-9]*$/)) {
            continue;
        }
        window[y].hit(switcher.url, document.title, document.referrer);
    }
});
