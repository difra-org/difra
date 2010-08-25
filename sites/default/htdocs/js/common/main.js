var main = {
	context: this
}

main.getXHTTPTransport = function() {

	var result = false;
	var actions = [
		function() { return new XMLHttpRequest() },
		function() { return new ActiveXObject( 'Msxml2.XMLHTTP' ) },
		function() { return new ActiveXObject( 'Microsoft.XMLHTTP' ) }
	];
	for(var i = 0; i < actions.length; i++) {
		try{
			result = actions[i]();
			break;
		} catch (e) {}
	}
	return result;
}

main.httpRequest = function( url, params ) {

	if( typeof params == 'undefined' ) {
		params = false;
	}
	var ajax = main.getXHTTPTransport();
	ajax.onReadyStateChange = function() {
		document.body.style.cursor = ( ajax.readyState == 4 ? 'default' : 'wait' );
		if( document.getElementById( 'loading' ) ) {
			document.getElementById( 'loading' ).style.display = ( ajax.readyState == 4 ? 'none' : 'block' );
		}
	}
	ajax.open( params ? 'POST' : 'GET', url, params );
	ajax.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	ajax.send( null );
	return ajax.responseText;
}

main.loadedModules = []

main.include = function( path ) {

	if( main.loadedModules[path] ) {
		return false;
	}
	main.loadedModules[path] = true;
	var transport = main.getXHTTPTransport();
	transport.open( 'GET', path, false );
	transport.send( null );
	
	var code = transport.responseText;
	(typeof execScript != 'undefined') ? execScript(code) : 
		(main.context.eval ? main.context.eval( code ) : eval( code ));
	return true;
}

main.include( '/js/common/events.js' );
main.include( '/js/common/editor.js' );
main.include( '/js/includes.js' );

