/**
 * Sends ajax-requests and processes results. Read wiki documentation for more info.
 *
 * Adds events:
 * form-submit                fires before sending form data
 */

var ajaxer = {};
ajaxer.id = 1;

ajaxer.setup = {
    async: false, cache: false, headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
};

/**
 * Ajax request
 * @param url                URL
 * @param params        POST data
 * @param headers        additional headers
 * @returns {string}
 */
ajaxer.httpRequest = function (url, params, headers) {

    $.ajaxSetup(ajaxer.setup);
    var data = {};
    if (typeof params === 'undefined' || !params) {
        data.type = 'GET';
    } else {
        data.type = 'POST';
        data.data = 'json=' + encodeURIComponent(JSON.stringify(params));
    }
    if (typeof headers !== 'undefined') {
        data.headers = headers;
    }
    return $.ajax(url, data).responseText;
};

/**
 * Send request from Ajaxer.js to Ajaxer.php
 * @param url        URL
 * @param data        Data
 */
ajaxer.query = function (url, data) {

    if (typeof debug !== 'undefined') {
        debug.addReq('Ajaxer request: ' + url);
    }
    ajaxer.process(this.httpRequest(url, data));
};

/**
 * Process Ajaxer.php data and perform required Ajaxer.js actions
 *
 * @param data        Data from Ajaxer.php
 * @param form        Form object (if ajax form submit was performed)
 */
ajaxer.process = function (data, form) {

    this.clean(form);
    ajaxer.statusInit();
    try {
        if (typeof debug !== 'undefined') {
            //console.info('Server said:', data);
            console.info('Got answer, processing.');
        }
        var dataObj = $.parseJSON(data);
        /** @namespace dataObj.actions */
        if (typeof dataObj === 'undefined' || typeof dataObj.actions === 'undefined') {
            //noinspection ExceptionCaughtLocallyJS
            throw "data error";
        }
        for (var key in dataObj.actions) {
            //noinspection JSUnfilteredForInLoop
            var action = dataObj.actions[key];
            if (typeof debug !== 'undefined') {
                debug.addReq('Processing ajaxer method: ' + action.action);
            }
            ajaxer.triggerHandler('ajaxer-pre-' + action.action);
            switch (action.action) {
                case 'notify':		// pop-up message
                    this.notify(action.lang, action.message);
                    break;
                case 'require':		// required form element is not filled
                    this.require(form, action.name);
                    break;
                case 'invalid':		// form element filled incorrectly
                    this.invalid(form, action.name);
                    break;
                case 'status':		// custom form element status
                    this.status(form, action.name, action.message, action.classname);
                    break;
                case 'redirect':	// http redirect
                    this.redirect(action.url);
                    break;
                case 'display':		// display some HTML in overlay
                    this.display(action.html);
                    break;
                case 'reload':		// refresh page
                    this.reload();
                    break;
                case 'close':		// close overlay
                    this.close(form);
                    break;
                case 'error':		// display error text
                    this.error(action.lang, action.message);
                    break;
                case 'reset':		// reset form data
                    this.reset(form);
                    break;
                case 'load':		// update (replace) some DOM element
                    this.load(action.target, action.html);
                    break;
                case 'exec':		// never use this if unsure, it's here only for very special cases
                    // this is possible security flow if you use it
                    this.exec(action.script);
                    break;
                default:
                    console.warn('Ajaxer action "' + action.action + '" not implemented');
            }
            ajaxer.triggerHandler('ajaxer-' + action.action);
        }
    } catch (err) {
        this.notify({close: 'OK'}, 'Unknown error.');
        console.warn('Error: ', err.message);
        console.warn('Server returned:', data);
        if (debug !== undefined) {
            debug.addReq('Server returned data ajaxer could not parse: ' + data);
        }
    }
    if (typeof debug != 'undefined') {
        debug.addReq();
    }
    ajaxer.statusUpdate(form);
};

ajaxer.triggerHandler = function (action) {
    try {
        $(document).triggerHandler(action);
    } catch (err) {
        console.warn('Error in "' + action + '" event handler:' + err.message);
    }
};

/**
 * clean form element problem flags
 * @param form        DOM element
 */
ajaxer.clean = function (form) {

    $(form).find('.problem').removeClass('problem');
    ajaxer.topScroll = -1;
};

/**
 * Display text message
 * @param lang                Locale data array
 * @param message        Message text
 */
ajaxer.notify = function (lang, message) {

    ajaxer.overlayShow('<p>' + message + '</p>' + '<a href="#" onclick="ajaxer.close(this)" class="button">' +
        ( lang.close ? lang.close : 'OK' ) + '</a>');
};

/**
 * Display error message text
 * @param lang                Locale data array
 * @param message        Error message text
 */
ajaxer.error = function (lang, message) {

    ajaxer.notify(lang, message);
};

/**
 * Find form element by name.
 * @param container
 * @param name
 * @returns {*|jQuery}
 */
ajaxer.smartFind = function (container, name) {

    var el = $(container).find('[name="' + name.replace(/"/g, "&quot;") + '"]:enabled');
    if (!el.length) {
        var nameChop = /^(.*)\[(\d+)]$/.exec(name);
        if (nameChop !== null && nameChop.length == 3) {
            var els = $(container).find('[name="' + ( nameChop[1] + '[]' ).replace(/"/g, "&quot;") + '"]:enabled');
            if (typeof els[nameChop[2]] != 'undefined') {
                el = $(els[nameChop[2]]);
            }
        }
    }
    return el;
};

ajaxer.topScroll = -1;
/**
 * Scroll page up to certain element.
 * Used to scroll page if some form element requires attention.
 * @param element
 */
ajaxer.scroll = function (element) {
    var top = element.offset().top - 32;
    if (top < 0) {
        top = 0;
    }
    if ($(window).scrollTop() > top) {
        if (ajaxer.topScroll == -1 || ajaxer.topScroll > top) {
            ajaxer.topScroll = top;
            $('body').animate({scrollTop: top}, 400);
        }
    }
};

/**
 * Add "required element is not filled" problem flag
 * @param form
 * @param name
 */
ajaxer.require = function (form, name) {

    var el = ajaxer.smartFind(form, name);
    if (!el.length || el.attr('type') == 'hidden') {
        ajaxer.error({}, 'Field "' + name + '" is required.');
        return;
    }
    var cke = $(form).find('#cke_' + name);
    if (cke.length) {
        cke.addClass('problem');
        ajaxer.scroll(cke);
    } else {
        el.addClass('problem');
        ajaxer.scroll(el);
    }
};

/**
 * Add "element is filled not correctly" problem flag
 * @param form
 * @param name
 * @param message
 */
ajaxer.invalid = function (form, name, message) {

    var el = ajaxer.smartFind(form, name);
    if (!el.length || el.attr('type') == 'hidden') {
        ajaxer.error({}, 'Invalid value for field "' + name + '".');
        return;
    }
    var cke = $(form).find('#cke_' + name);
    if (cke.length) {
        cke.addClass('problem');
        ajaxer.scroll(cke);
    } else {
        el.addClass('problem');
        ajaxer.scroll(el);
    }
};

/**
 * HTTP-like redirect
 * @param url
 */
ajaxer.redirect = function (url) {

    if (typeof(switcher) == 'undefined') {
        document.location(url);
    } else {
        switcher.page(url);
    }
};

/**
 * Reload current page
 */
ajaxer.reload = function () {

    window.location.reload();
};

/**
 * Display overlay with some HTML
 * @param html
 */
ajaxer.display = function (html) {

    ajaxer.overlayShow(html);
};

/**
 * Close overlay
 * @param obj
 */
ajaxer.close = function (obj) {

    ajaxer.overlayHide(obj);
};

/**
 * Reset form
 * @param form
 */
ajaxer.reset = function (form) {

    $(form).get(0).reset();
};

/**
 * Update (replace) DOM element
 * @param target
 * @param html
 */
ajaxer.load = function (target, html) {

    var cut = $(html).filter(target);
    if (cut.length) {
        $(target).replaceWith(cut);
    } else {
        $(target).html(html);
    }
    $(window).resize();
};

/**
 * Exec java script
 * NEVER use this. It is designed to make few special things possible and should not used in any way.
 * @param script
 */
ajaxer.exec = function (script) {
    eval(script);
};

/**
 * Form elements statuses
 */
ajaxer.statuses = {};

/**
 * Init form statuses
 */
ajaxer.statusInit = function () {

    ajaxer.statuses = {};
};

/**
 * Set form element status
 * @param form
 * @param name
 * @param message
 * @param classname
 */
ajaxer.status = function (form, name, message, classname) {

    ajaxer.statuses[name] = {message: message, classname: classname, used: 0};
    /*
     // старый код функции ajaxer.status()
     var status = $( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.status' );
     if( status ) {
     status.fadeIn( 'fast' );
     status.attr( 'class', 'status ' + classname );
     status.html( message );
     }
     */
};

/**
 * Update all form elements to match their statuses
 * @param form
 */
ajaxer.statusUpdate = function (form) {

    $(form).find('.status').each(function (i, obj1) {
        var obj = $(obj1);
        // получаем имя элемента, к которому относится это поле статуса
        var container = obj.closest('.container');
        if (typeof container == 'undefined') {
            return;
        }
        var formElement = container.find('input, textarea');
        if (typeof formElement == 'undefined') {
            return;
        }
        var name = formElement.attr('name');
        if (!name) {
            return;
        }
        // remember original text
        if (typeof obj.attr('original-text') == "undefined") {
            obj.attr('original-text', obj.html());
        }
        if (name in
            ajaxer.statuses) {
            // it looks like status text or status style has updated
            obj.animate({opacity: 0}, 'fast', function () {
                if (obj.attr('status-class')) {
                    if (obj.attr('status-class') != ajaxer.statuses[name].classname) {
                        obj.removeClass(obj.attr('status-class'));
                        obj.removeAttr('status-class');
                        obj.attr('status-class', ajaxer.statuses[name].classname);
                        obj.addClass(ajaxer.statuses[name].classname);
                    }
                } else {
                    obj.attr('status-class', ajaxer.statuses[name].classname);
                    obj.addClass(ajaxer.statuses[name].classname);
                }
                obj.html(ajaxer.statuses[name].message);
                obj.animate({opacity: 1}, 'fast');
            });
            ajaxer.statuses[name].used = 1;
        } else if (obj.attr('status-class')) {
            // element has no special status anymore, restore it
            obj.animate({opacity: 0}, 'fast', function () {
                obj.removeClass(obj.attr('status-class'));
                obj.removeAttr('status-class');
                obj.html(obj.attr('original-text'));
                obj.animate({opacity: 1}, 'fast');
            });
        }
    });
    // warning for elements ajaxer could not find
    for (var i in ajaxer.statuses) {
        //noinspection JSUnfilteredForInLoop
        if (!ajaxer.statuses[i].used) {
            //noinspection JSUnfilteredForInLoop
            console.warn('Status for ' + ajaxer.statuses[i].classname + ': ' + ajaxer.statuses[i].message);
            //ajaxer.notify( {}, ajaxer.statuses[i].message );
        }
    }
};

/**
 * Display overlay
 * @param content
 */
ajaxer.overlayShow = function (content) {

    $('body').append('<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
        '<div class="overlay-container auto-center">' + '<div class="overlay-inner" style="display:none">' +
        '<div class="close-button action close" onclick="ajaxer.close(this)"></div>' + content + '</div>' + '</div>' +
        '</div>');
    $('html').css('overflow', 'hidden');
    $('#ajaxer-' + ajaxer.id).find('.overlay-inner').fadeIn('fast');
    $(window).resize();
    ajaxer.id++;
};

/**
 * Hide overlay
 * @param obj
 */
ajaxer.overlayHide = function (obj) {

    var el = $(obj).parents('.overlay');
    if (!el.length) {
        el = $('.overlay');
    }
    $('html').css('overflow', '');
    el.fadeOut('fast', function () {
        $(this).remove();
    });
};

/**
 * Initiate ajax form submit
 * @param form
 * @param event
 */
ajaxer.sendForm = function (form, event) {

    var data = {
        form: $(form).serializeArray()
    };
    ajaxer.process(this.httpRequest($(form).attr('action'), data), form);
};

ajaxer.submitting = false;
/**
 * Submit forms with .ajaxer class with ajax.
 */
$(document).on('submit', 'form.ajaxer', function (event) {

    if (ajaxer.submitting) {
        return;
    }
    var form = $(this);
    $(document).triggerHandler('form-submit');
    event.preventDefault();
    ajaxer.clean(form);
    if (!form.find('input[type=file]').length) {
        // serialize method
        ajaxer.sendForm(this, event);
        return;
    }
    if ($('#ajaxerFrame').length) {
        return; // some progress bar overlay already exists?!
    }
    // generate UUID for progress bar
    var uuid = '';
    for (var i = 0; i < 32; i++) {
        uuid += Math.floor(Math.random() * 16).toString(16);
    }
    // modify form to be sent to iframe
    form.attr('method', 'post');
    form.attr('enctype', 'multipart/form-data');
    var originalAction = form.attr('action');
    form.attr('originalAction', originalAction);
    form.attr('action',
        form.attr('action') + ( originalAction.indexOf('?') == -1 ? '?' : '&' ) + 'X-Progress-ID=' + uuid);
    form.attr('target', 'ajaxerFrame');
    form.attr('uuid', uuid);
    form.append('<input type="hidden" name="_method" value="iframe"/>');
    // add iframe for form target
    //noinspection HtmlUnknownTarget
    var frame = $('<iframe id="ajaxerFrame" name="ajaxerFrame" style="display:none" src="/iframe"></iframe>');
    frame.one('load', function (event) {
        ajaxer.initIframe(form, event)
    });
    $('body').append(frame);
    loading.show();
});

/**
 * Bind everything for iframe and progress bar overlay update and restore.
 * @param form
 * @param event
 */
ajaxer.initIframe = function (form, event) {
    var interval;
    var frame = $('iframe#ajaxerFrame');
    event.stopPropagation();
    // bind new onLoad for iframe
    frame.off('load');
    frame.one('load', function () {
        window.clearInterval(interval);
        // get data from iframe
        var rawframe = frame.get(0);
        if (rawframe.contentDocument) {
            var val = rawframe.contentDocument.body.innerHTML;
        } else if (rawframe.contentWindow) {
            val = rawframe.contentWindow.document.body.innerHTML;
        } else if (rawframe.document) {
            val = rawframe.document.body.innerHTML;
        }
        //noinspection EmptyCatchBlockJS
        try {
            var fc = $(val).text();
            if (fc) {
                val = fc;
            }
        } catch (ignore) {
        }
        // restore form and clean up
        form.attr('action', form.attr('originalAction'));
        form.removeAttr('originalAction');
        form.removeAttr('uuid');
        form.find('input[name=_method]').remove();
        $('iframe#ajaxerFrame').remove();
        //noinspection JSJQueryEfficiency
        var upprog = $('#upprog');
        if (upprog.length) {
            loading.find('td1').css('width', Math.ceil($('#upprog').width() - 20) + 'px');
        }
        loading.hide();
        ajaxer.process(val, form);
    });
    // submit form
    ajaxer.submitting = true;
    form.submit();
    ajaxer.submitting = false;
    // get status and update progress bar once a second
    var uuid = form.attr('uuid');
    ajaxer.fetchProgress(uuid);
    interval = window.setInterval(function () {
        ajaxer.fetchProgress(uuid);
    }, 1000);
};

/**
 * Fetch iframe upload status and update progress bar
 * @param uuid
 */
ajaxer.fetchProgress = function (uuid) {

    var res = ajaxer.httpRequest('/progress', null, {'X-Progress-ID': uuid});
    res = $.parseJSON(res);
    if (res.state == 'uploading') {
        //noinspection JSJQueryEfficiency
        var progressbar = $('#upprog');
        if (!progressbar.length) {
            //noinspection JSUnusedAssignment
            loading.hide();
            //noinspection JSJQueryEfficiency
            var loading = $('#loading');
            if (!loading.length) {
                $('body').append(loading = $('<div id="loading"></div>'));
            }
            loading.fadeIn();

            //noinspection JSJQueryEfficiency
            $('#loading').css('background-image',
                'none').append('<div id="upprog" class="auto-center"><div class="td1"></div></div>');
            autocenter();
            progressbar = $('#upprog');
        }
        /** @namespace res.received */
        progressbar.find('.td1').css('width', Math.ceil(( progressbar.width() - 20 ) * res.received / res.size) + 'px');
    }
};

/**
 * Page events
 */

/**
 * Links with .ajaxer class will call ajaxer.query() instead of page change.
 * That's easy way to "ping" Ajaxer.php, for example:
 * <a href="/edit/page/75">Edit</a>
 * will lead to Ajaxer.js request to Ajaxer.php by url /edit/page/75 and process actions as requested by Ajaxer.php.
 */
$(document).on('click dblclick', 'a.ajaxer', function (e) {
    var href = $(this).attr('href');
    if (href && href != '#') {
        ajaxer.query(href);
    }
    e.preventDefault();
});

/**
 * Let Enter key submit .ajaxer forms. It's common behavior for web applications.
 */
$(document).on('keypress', '.ajaxer input', function (e) {
    if (e.which == 13) {
        $(this).parents('form').submit();
        e.preventDefault();
    }
});

/**
 * Let element with .submit class submit form.
 */
$(document).on('click dblclick', '.submit', function (e) {
    $(this).parents('form').submit();
    e.preventDefault();
});

/**
 * Let element with .reset class reset form.
 */
$(document).on('click dblclick', '.reset', function (e) {
    ajaxer.reset($(this).parents('form'));
    e.preventDefault();
});

/**
 * Let Ajaxer.php pass actions to Ajaxer.js via cookie.
 */
ajaxer.watcher = function () {

    var mc = $.cookie('query');
    if (mc) {
        mc = $.parseJSON(mc);
        ajaxer.query(mc.url);
        $.cookie('query', '', {
            path: "/", domain: config.mainhost ? '.' + config.mainhost : false, expires: -1
        });
    }
    mc = $.cookie('notify');
    if (mc) {
        mc = $.parseJSON(mc);
        if (typeof mc.type != 'undefined' && mc.type == 'error') {
            ajaxer.error(mc.lang, mc.message);
        } else {
            ajaxer.notify(mc.lang, mc.message);
        }
        /** @namespace config.mainhost */
        if (typeof config.mainhost === 'undefined') {
            $.cookie('notify', '', {
                path: "/", domain: '.' + window.location.host, expires: -1
            });
        } else {
            $.cookie('notify', '', {
                path: "/", domain: '.' + config.mainhost, expires: -1
            });
        }
    }
};
$(document).ready(ajaxer.watcher);
$(document).on('construct', ajaxer.watcher);
