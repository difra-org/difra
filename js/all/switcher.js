/**
 * Switcher.js allows native-like page switching with ajax.
 *
 * Server identifies Switcher.js requests by "X-Requested-With: SwitchPage" HTTP header.
 * When this header is detected, you can return only updated elements. Usually there is no point
 * to track which elements was updated, but you can return very simplified page with container elements only,
 * with all layout missing. To identify which elements to replace, this script uses id attribute. All container
 * elements you want to update on page switch should contain .switcher class.
 *
 * If script fails to switch link, it uses http redirect to change page.
 *
 * This script adds following events:
 * construct        fires after page change
 * destruct        fires before page change
 * switch        fires after destruct when script understands that page switch will be successful
 */

var switcher = {};

switcher.noPush = false;
switcher.url = false;
switcher.referrer = false;

switcher.ajaxConfig = {
    async: true, cache: false, headers: {'X-Requested-With': 'SwitchPage'}, type: 'GET', beforeSend: function () {
        loading.show();
    }, success: function (data, status, xhr) {
        try {
            var newdata = $(data);
        } catch (ignore) {
            switcher.fallback();
            return;
        }
        var a = newdata.filter('.switcher').add(newdata.find('.switcher'));
        if (!a.length) {
            switcher.fallback();
            return;
        }
        $(document).triggerHandler('destruct');
        if (!switcher.noPush) {
            history.pushState({url: switcher.url}, '', switcher.url);
        }
        $(document).triggerHandler('switch');

        a.each(function (k, v) {
            try {
                $('#' + $(v).attr('id')).replaceWith(v).remove();
            } catch (ignore) {
            }
        });
        $(window).scrollTop(0);

        var title = newdata.filter('title').text();
        if (title.length) {
            document.title = title;
        } else {
            title = newdata.find('title').text();
            if (title.length) {
                document.title = title;
            }
        }
        $(document).triggerHandler('construct');
        loading.hide();
    }, error: function (xhr) {
        switcher.fallback();
    }
};

/**
 * Page switch fall back
 */
switcher.fallback = function () {
    $(document).triggerHandler('destruct');
    loading.hide();
    document.location = switcher.url;
};

/**
 * Switch page
 * @param url
 * @param noPush
 * @param data
 */
switcher.page = function (url, noPush, data) {
    // cut protocol://host part if it matches current host
    var host = window.location.protocol + "//" + window.location.host + "/";
    if (host == url.substring(0, host.length)) {
        switcher.page(url.substring(host.length - 1));
        return;
    }
    if (typeof debug != 'undefined') {
        debug.addReq('Switching page: ' + url);
    }
    switcher.noPush = !!noPush;
    switcher.referrer = switcher.url;
    switcher.url = url;
    if (typeof data == 'undefined') {
        if ($('.switcher:not(#debug)').length) {
            $.ajax(url, switcher.ajaxConfig);
        } else {
            $(document).triggerHandler('destruct');
            loading.hide();
            window.location = switcher.url;
        }
    } else {
        var conf = switcher.ajaxConfig;
        conf.type = 'POST';
        conf.data = data;
        $.ajax(url, conf);
    }
};

switcher.bind = function () {
    $(document).on('click dblclick touchend', 'a', function (event) {

        // skip .ajaxer and .no-switcher links
        // warning: .noAjaxer is deprecated
        if ($(this).hasClass('ajaxer') || $(this).hasClass('noAjaxer') || $(this).hasClass('no-switcher')) {
            return;
        }

        var href = $(this).attr('href');

        // skip empty links, anchors and javascript
        if (href == '' || href == '#') {
            event.preventDefault();
            return;
        }
        if (href.substr(0, 11) == 'javascript:' || href.substr(0, 1) == '#') {
            return;
        }

        event.preventDefault();
        switcher.page(href);
    });
    $(window).on('popstate', switcher.onpopstate);
};

/**
 * Support "Back" and "Forward" browser buttons
 */
switcher.onpopstate = function () {
    if (switcher.url && switcher.url != decodeURI(document.location.pathname)) {
        switcher.page(document.location.href, true);
    }
};

switcher.init = function () {
    // if there are no switcher elements, don't init
    if (!$('.switcher:not(#debug)').length) {
        return;
    }
    // remember current URL on first page load
    if (!switcher.url) {
        switcher.url = decodeURI(document.location.pathname);
    }
    // binds
    switcher.bind();
};

$(document).ready(switcher.init);
