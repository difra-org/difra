/**
 * Отправляет ajax-запросы и обрабатывает результаты.
 *
 * Добавляет события:
 * form-submit                — срабатывает перед отправкой данных формы
 */

var ajaxer = {};
ajaxer.id = 1;

ajaxer.setup = {
	async: false,
	cache: false,
	headers: {
		'X-Requested-With': 'XMLHttpRequest'
	}
};

ajaxer.httpRequest = function( url, params, headers ) {

	$.ajaxSetup( ajaxer.setup );
	var data = {};
	if( typeof params === 'undefined' || !params ) {
		data.type = 'GET';
	} else {
		data.type = 'POST';
		data.data = 'json=' + encodeURIComponent( JSON.stringify( params ) );
	}
	if( typeof headers !== 'undefined' ) {
		data.headers = headers;
	}
	return $.ajax( url, data ).responseText;
};

ajaxer.query = function( url, data ) {

	if( typeof debug !== 'undefined' ) {
		debug.addReq( 'Ajaxer request: ' + url );
	}
	ajaxer.process( this.httpRequest( url, data ) );
};

/**
 * Обработка экшенов
 */

ajaxer.process = function( data, form ) {

	this.clean( form );
	ajaxer.statusInit();
	try {
		if( typeof debug !== 'undefined' ) {
			//console.info( 'Server said:', data );
			console.info( 'Got answer, processing.' );
		}
		var dataObj = $.parseJSON( data );
		/** @namespace dataObj.actions */
		if( typeof dataObj === 'undefined' || typeof dataObj.actions === 'undefined' ) {
			//noinspection ExceptionCaughtLocallyJS
			throw "data error";
		}
		for( var key in dataObj.actions ) {
			//noinspection JSUnfilteredForInLoop
			var action = dataObj.actions[key];
			if( typeof debug !== 'undefined' ) {
				debug.addReq( 'Processing ajaxer method: ' + action.action );
			}
			ajaxer.triggerHandler( 'ajaxer-pre-' + action.action );
			switch( action.action ) {
				case 'notify':                // сообщение
					this.notify( action.lang, action.message );
					break;
				case 'require':                // не заполнено обязательное поле формы
					this.require( form, action.name );
					break;
				case 'invalid':                // не правильное значение поля формы
					this.invalid( form, action.name );
					break;
				case 'status':                // текстовый статус для поля
					this.status( form, action.name, action.message, action.classname );
					break;
				case 'redirect':        // перенаправление
					this.redirect( action.url );
					break;
				case 'display':                // показать оверлей с пришедшим html
					this.display( action.html );
					break;
				case 'reload':                // перезагрузить страницу
					this.reload();
					break;
				case 'close':                // закрыть оверлей
					this.close( form );
					break;
				case 'error':                // сообщение об ошибке
					this.error( action.lang, action.message );
					break;
				case 'reset':                // сделать форме reset
					this.reset( form );
					break;
				case 'load':                // заменить содержимое DOM-элемента
					this.load( action.target, action.html );
					break;
				case 'exec':
					this.exec( action.script );
					break;
				default:
					console.warn( 'Ajaxer action "' + action.action + '" not implemented' );
			}
			ajaxer.triggerHandler( 'ajaxer-' + action.action );
		}
	} catch( err ) {
		this.notify( {close: 'OK'}, 'Unknown error.' );
		console.warn( 'Error: ', err.message );
		console.warn( 'Server returned:', data );
		if( debug !== undefined ) {
			debug.addReq( 'Server returned data ajaxer could not parse: ' + data );
		}
	}
	if( typeof debug != 'undefined' ) {
		debug.addReq();
	}
	ajaxer.statusUpdate( form );
};

ajaxer.triggerHandler = function( action ) {
	try {
		$( document ).triggerHandler( action );
	} catch( err ) {
		console.warn( 'Error in "' + action + '" event handler:' + err.message );
	}
};

ajaxer.clean = function( form ) {

	$( form ).find( '.problem' ).removeClass( 'problem' );
};

ajaxer.notify = function( lang, message ) {

	ajaxer.overlayShow( '<p>' + message + '</p>' + '<a href="#" onclick="ajaxer.close(this)" class="button">' + ( lang.close ? lang.close : 'OK' ) + '</a>' );
};

ajaxer.error = function( lang, message ) {

	ajaxer.notify( lang, message );
};

// поиск элемента формы
ajaxer.smartFind = function( container, name ) {

	var el = $( container ).find( '[name="' + name.replace( /"/g, "&quot;" ) + '"]' );
	if( !el.length ) {
		var nameChop = /^(.*)\[(\d+)]$/.exec( name );
		if( nameChop !== null && nameChop.length == 3 ) {
			var els = $( container ).find( '[name="' + (nameChop[1] + '[]').replace( /"/g, "&quot;" ) + '"]' );
			if( typeof els[nameChop[2]] != 'undefined' ) {
				el = $( els[nameChop[2]] );
			}
		}
	}
	return el;
};

ajaxer.require = function( form, name ) {

	var el = ajaxer.smartFind( form, name );
	if( !el.length || el.attr( 'type' ) == 'hidden' ) {
		ajaxer.error( {}, 'Field "' + name + '" is required.' );
		return;
	}
	var cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		el.addClass( 'problem' );
	}
};

ajaxer.invalid = function( form, name, message ) {

	var el = ajaxer.smartFind( form, name );
	if( !el.length || el.attr( 'type' ) == 'hidden' ) {
		ajaxer.error( {}, 'Invalid value for field "' + name + '".' );
		return;
	}
	var cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		el.addClass( 'problem' );
	}
};

ajaxer.redirect = function( url ) {

	if( typeof(switcher) == 'undefined' ) {
		document.location( url );
	} else {
		switcher.page( url );
	}
};

ajaxer.reload = function() {

	window.location.reload();
};

ajaxer.display = function( html ) {

	ajaxer.overlayShow( html );
};

ajaxer.close = function( obj ) {

	ajaxer.overlayHide( obj );
};

ajaxer.reset = function( form ) {

	$( form ).get( 0 ).reset();
};

ajaxer.load = function( target, html ) {

	var cut = $( html ).filter( target );
	if( cut.length ) {
		$( target ).replaceWith( cut );
	} else {
		$( target ).html( html );
	}
	$( window ).resize();
};

ajaxer.exec = function( script ) {
	eval( script );
};

/**
 * Статусы элементов формы
 */

ajaxer.statuses = {};

// инициализация статусов
ajaxer.statusInit = function() {

	ajaxer.statuses = {};
};

// эта функция устанавливает статус
ajaxer.status = function( form, name, message, classname ) {

	ajaxer.statuses[name] = { message: message, classname: classname, used: 0 };
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

// эта функция обновляет поля статусов в соответствии со значениями, установленными через ajaxer.status()
ajaxer.statusUpdate = function( form ) {

	$( form ).find( '.status' ).each( function( i, obj1 ) {
		var obj = $( obj1 );
		// получаем имя элемента, к которому относится это поле статуса
		var container = obj.closest( '.container' );
		if( typeof container == 'undefined' ) {
			return;
		}
		var formElement = container.find( 'input, textarea' );
		if( typeof formElement == 'undefined' ) {
			return;
		}
		var name = formElement.attr( 'name' );
		if( !name ) {
			return;
		}
		// сохраняем оригинальный текст
		if( typeof obj.attr( 'original-text' ) == "undefined" ) {
			obj.attr( 'original-text', obj.html() );
		}
		if( name in ajaxer.statuses ) {
			// похоже, обновился статус или стиль
			obj.animate( { opacity: 0 }, 'fast', function() {
				if( obj.attr( 'status-class' ) ) {
					if( obj.attr( 'status-class' ) != ajaxer.statuses[name].classname ) {
						obj.removeClass( obj.attr( 'status-class' ) );
						obj.removeAttr( 'status-class' );
						obj.attr( 'status-class', ajaxer.statuses[name].classname );
						obj.addClass( ajaxer.statuses[name].classname );
					}
				} else {
					obj.attr( 'status-class', ajaxer.statuses[name].classname );
					obj.addClass( ajaxer.statuses[name].classname );
				}
				obj.html( ajaxer.statuses[name].message );
				obj.animate( { opacity: 1 }, 'fast' );
			} );
			ajaxer.statuses[name].used = 1;
		} else if( obj.attr( 'status-class' ) ) {
			// статус был изменен, но теперь нет
			obj.animate( { opacity: 0 }, 'fast', function() {
				obj.removeClass( obj.attr( 'status-class' ) );
				obj.removeAttr( 'status-class' );
				obj.html( obj.attr( 'original-text' ) );
				obj.animate( { opacity: 1 }, 'fast' );
			} );
		}
	} );
	// для этих элементов статус применить не удалось
	for( var i in ajaxer.statuses ) {
		//noinspection JSUnfilteredForInLoop
		if( !ajaxer.statuses[i].used ) {
			//noinspection JSUnfilteredForInLoop
			console.warn( 'Status for ' + ajaxer.statuses[i].classname + ': ' + ajaxer.statuses[i].message );
			// TODO: показывать нотифай вместо записи в лог
			//ajaxer.notify( {}, ajaxer.statuses[i].message );
		}
	}
};

/**
 * Работа с оверлеем
 */

ajaxer.overlayShow = function( content ) {

	$( 'body' ).append(
		'<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
		'<div class="overlay-container auto-center">' +
		'<div class="overlay-inner" style="display:none">' +
		'<div class="close-button action close" onclick="ajaxer.close(this)"></div>' +
		content +
		'</div>' +
		'</div>' +
		'</div>'
	);
	$( 'html' ).css( 'overflow', 'hidden' );
	$( '#ajaxer-' + ajaxer.id ).find( '.overlay-inner' ).fadeIn( 'fast' );
	$( window ).resize();
	ajaxer.id++;
};

ajaxer.overlayHide = function( obj ) {

	var el = $( obj ).parents( '.overlay' );
	if( !el.length ) {
		el = $( '.overlay' );
	}
	$( 'html' ).css( 'overflow', '' );
	el.fadeOut( 'fast', function() {
		$( this ).remove();
	} );
};

/**
 * Обработка сабмита форм
 */

ajaxer.sendForm = function( form, event ) {

	var data = {
		form: $( form ).serializeArray()
	};
	ajaxer.process( this.httpRequest( $( form ).attr( 'action' ), data ), form );
};

ajaxer.submitting = false;
$( document ).on( 'submit', 'form.ajaxer', function( event ) {

	if( ajaxer.submitting ) {
		return;
	}
	var form = $( this );
	$( document ).triggerHandler( 'form-submit' );
	event.preventDefault();
	ajaxer.clean( form );
	if( !form.find( 'input[type=file]' ).length ) {
		// serialize method
		ajaxer.sendForm( this, event );
		return;
	}
	// iframe method
	if( $( '#ajaxerFrame' ).length ) {
		return;
	}
	// генерируем uuid для прогрессбара
	var uuid = '';
	for( var i = 0; i < 32; i++ ) {
		uuid += Math.floor( Math.random() * 16 ).toString( 16 );
	}
	// модифицируем форму для отправки через iframe
	form.attr( 'method', 'post' );
	form.attr( 'enctype', 'multipart/form-data' );
	var originalAction = form.attr( 'action' );
	form.attr( 'originalAction', originalAction );
	form.attr( 'action',
		   form.attr( 'action' ) + ( originalAction.indexOf( '?' ) == -1 ? '?' : '&' ) + 'X-Progress-ID=' + uuid );
	form.attr( 'target', 'ajaxerFrame' );
	form.attr( 'uuid', uuid );
	form.append( '<input type="hidden" name="_method" value="iframe"/>' );
	// добавляем на страницу iframe
	var frame = $( '<iframe id="ajaxerFrame" name="ajaxerFrame" style="display:none" src="/iframe"></iframe>' );
	frame.one( 'load', function( event ) {
		ajaxer.initIframe( form, event )
	} );
	$( 'body' ).append( frame );
	loading.show();
} );

/**
 * Отправка файлов через iframe
 */

ajaxer.initIframe = function( form, event ) {
	var interval;
	var frame = $( 'iframe#ajaxerFrame' );
	event.stopPropagation();
	// цепляем новую функцию onload на iframe
	frame.off( 'load' );
	frame.one( 'load', function() {
		window.clearInterval( interval );
		// получаем данные из iframe
		var rawframe = frame.get( 0 );
		if( rawframe.contentDocument ) {
			var val = rawframe.contentDocument.body.innerHTML;
		} else if( rawframe.contentWindow ) {
			val = rawframe.contentWindow.document.body.innerHTML;
		} else if( rawframe.document ) {
			val = rawframe.document.body.innerHTML;
		}
		//noinspection EmptyCatchBlockJS
		try {
			var fc = $( val ).text();
			if( fc ) {
				val = fc;
			}
		} catch( ignore ) {
		}
		// восстанавливаем форму в первоначальный вид и подчищаем документ
		form.attr( 'action', form.attr( 'originalAction' ) );
		form.removeAttr( 'originalAction' );
		form.removeAttr( 'uuid' );
		form.find( 'input[name=_method]' ).remove();
		$( 'iframe#ajaxerFrame' ).remove();
		//noinspection JSJQueryEfficiency
		var upprog = $( '#upprog' );
		if( upprog.length ) {
			loading.find( 'td1' ).css( 'width', Math.ceil( $( '#upprog' ).width() - 20 ) + 'px' );
		}
		loading.hide();
		ajaxer.process( val, form );
	} );
	// сабмиттим форму
	ajaxer.submitting = true;
	form.submit();
	ajaxer.submitting = false;
	// получаем статус закачки и обновляем прогрессбар раз в секунду
	var uuid = form.attr( 'uuid' );
	ajaxer.fetchProgress( uuid );
	interval = window.setInterval(
		function() {
			ajaxer.fetchProgress( uuid );
		},
		1000
	);
};

ajaxer.fetchProgress = function( uuid ) {

	var res = ajaxer.httpRequest( '/progress', null, { 'X-Progress-ID': uuid } );
	res = $.parseJSON( res );
	if( res.state == 'uploading' ) {
		//noinspection JSJQueryEfficiency
		var progressbar = $( '#upprog' );
		if( !progressbar.length ) {
			//noinspection JSUnusedAssignment
			loading.hide();
			//noinspection JSJQueryEfficiency
			var loading = $( '#loading' );
			if( !loading.length ) {
				$( 'body' ).append( loading = $( '<div id="loading"></div>' ) );
			}
			loading.fadeIn();

			//noinspection JSJQueryEfficiency
			$( '#loading' )
				.css( 'background-image', 'none' )
				.append( '<div id="upprog" class="auto-center"><div class="td1"></div></div>' );
			autocenter();
			progressbar = $( '#upprog' );
		}
		/** @namespace res.received */
		progressbar.find( '.td1' )
			.css( 'width', Math.ceil( ( progressbar.width() - 20 ) * res.received / res.size ) + 'px' );
	}
};

/**
 * Обработка событий страницы
 */

$( document ).on( 'click dblclick', 'a.ajaxer', function( e ) {
	var href = $( this ).attr( 'href' );
	if( href && href != '#' ) {
		ajaxer.query( href );
	}
	e.preventDefault();
} );

$( document ).on( 'keypress', '.ajaxer input', function( e ) {
	if( e.which == 13 ) {
		$( this ).parents( 'form' ).submit();
		e.preventDefault();
	}
} );

$( document ).on( 'click dblclick', '.submit', function( e ) {
	$( this ).parents( 'form' ).submit();
	e.preventDefault();
} );

$( document ).on( 'click dblclick', '.reset', function( e ) {
	ajaxer.reset( $( this ).parents( 'form' ) );
	e.preventDefault();
} );

ajaxer.watcher = function() {

	var mc = $.cookie( 'query' );
	if( mc ) {
		mc = $.parseJSON( mc );
		ajaxer.query( mc.url );
		$.cookie( 'query', null, { path: "/", domain: config.mainhost ? '.' + config.mainhost : false } );
	}
	mc = $.cookie( 'notify' );
	if( mc ) {
		mc = $.parseJSON( mc );
		if( mc.type == 'error' ) {
			ajaxer.error( mc.lang, mc.message );
		} else {
			ajaxer.notify( mc.lang, mc.message );
		}
		/** @namespace config.mainhost */
		if( typeof config.mainhost === 'undefined' ) {
			$.cookie( 'notify', null, { path: "/", domain: '.' + window.location.host } );
		} else {
			$.cookie( 'notify', null, { path: "/", domain: '.' + config.mainhost } );
		}
	}
};

$( document ).ready( ajaxer.watcher );
$( document ).bind( 'construct', ajaxer.watcher );
