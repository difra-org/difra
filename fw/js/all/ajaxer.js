/**
 * Отправляет ajax-запросы и обрабатывает результаты.
 *
 * Добавляет события:
 * form-submit		— срабатывает перед отправкой данных формы
 */
$.ajaxSetup( {
	async : false,
	cache :	false,
	headers : {
		'X-Requested-With' : 'XMLHttpRequest'
	}
} );

var ajaxer = {};

ajaxer.httpRequest = function( url, params, headers ) {

	var data = {};
	if( typeof params == 'undefined' ) {
		data.type = 'GET';
	} else {
		data.type = 'POST';
		data.data = 'json=' + JSON.stringify( params );
	}
	if( typeof headers != 'undefined' ) {
		data.headers = headers;
	}
	return $.ajax( url, data ).responseText;
};

ajaxer.sendForm = function( form, event ) {

	var jForm = $( form );
	// var data = jForm.serializeArray();
	var data = $( event.target ).serialize();
	ajaxer.process( this.httpRequest( jForm.attr( 'action' ), data ), form );
};

ajaxer.query = function( url, data ) {
	
	ajaxer.process( this.httpRequest( url, data ) );
};

ajaxer.process = function( data, form ) {

	try {
		data = eval( '(' + data + ')' );
		if( !data.actions ) {
			return data;
		}
		for( key in data.actions ) {
			var action = data.actions[key];
			switch( action.action ) {
			case 'notify':	// сообщение
				this.showNotify( action.lang, action.message );
				break;
			case 'require':// не заполнено обязательное поле формы
				this.showRequire( form, action.lang, action.name );
				break;
			case 'invalid':	// не правильное значение поля формы
			case 'error':	// сообщение об ошибке
			case 'redirect':// перенаправление
			case 'display':	// показать окно с пришедшим html
			case 'reload': // перезагрузить страницу
			default:
				console.warn( 'Ajaxer action "' + action.action + '" not implemented' );
			}
		}
	} catch( ex ) {
		// TODO: notify about fail
	}
};

ajaxer.showNotify = function( lang, message ) {

	$( 'body' ).append(
		'<div class="overlay">' +
			'<div class="overlay-container notify auto-center">' +
			'<div class="overlay-inner">' +
			'<div class="close-button" onclick="ajaxer.close(this)"></div>' +
			'<p>' + message + '</p>' +
			'<a href="#" onclick="ajaxer.close(this)" class="popup-button center">' + lang.close + '</a>' +
			'</div>' +
			'</div>' +
			'</div>'
	);
	$( window ).resize();
};

ajaxer.showRequire = function( form, lang, name ) {

	$( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.required' ).css( 'display', 'block' );
};

ajaxer.close = function( obj ) {

	$( obj ).parents( '.overlay' ).remove();
};

var main = {};
main.httpRequest = ajaxer.httpRequest;

$( document ).delegate( 'form.ajaxer', 'submit', function( event ) {
	$( document ).triggerHandler( 'form-submit' );
	event.preventDefault();
	ajaxer.sendForm( this, event );
} );

