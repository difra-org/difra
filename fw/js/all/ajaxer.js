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

ajaxer.sendForm = function( form ) {

	var jForm = $( form );
	ajaxer.process( this.httpRequest( jForm.attr( 'action' ), { form: jForm.serializeArray() } ) );
};

ajaxer.process = function( data ) {

	try {
		data = eval( '(' + data + ')' );
		if( !data.action ) {
			return data;
		}
		switch( data.action ) {
		case 'notify':	// сообщение
			this.showNotify( data.lang, data.message );
			break;
		case 'error':	// сообщение об ошибке
		case 'redirect':// перенаправление
		case 'display':	// показать окно с пришедшим html
		case 'required':// не заполнено обязательное поле формы
		case 'invalid':	// не правильное значение поля формы
		case 'reload': // перезагрузить страницу
		default:
			alert( 'Ajaxer action ' + data.action + ' not implemented' );
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

ajaxer.close = function( obj ) {

	$( obj ).parents( '.overlay' ).remove();
};

var main = {};
main.httpRequest = ajaxer.httpRequest;

$( document ).delegate( 'form.ajaxer', 'submit', function( event ) {
	event.preventDefault();
	ajaxer.sendForm( this );
} );

