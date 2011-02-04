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

	var ajax = main.getXHTTPTransport();
	ajax.onReadyStateChange = function() {
		document.body.style.cursor = ( ajax.readyState == 4 ? 'default' : 'wait' );
		if( document.getElementById( 'loading' ) ) {
			document.getElementById( 'loading' ).style.display = ( ajax.readyState == 4 ? 'none' : 'block' );
		}
	}
	var data = null;
	if( typeof params == 'undefined' ) {
		ajax.open( 'GET', url, false );
	} else {
		ajax.open( 'POST', url, false );
		ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		data = 'json=' + JSON.stringify( params );
	}
	ajax.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	ajax.send( data );
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

