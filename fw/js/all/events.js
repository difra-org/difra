var events = {}

events.onLoad = []
events.addOnLoad = function( func ) {
	
	events.onLoad[events.onLoad.length] = func;
}
events.runOnLoad = function() {

	for( i = 0; i < events.onLoad.length; i++ ) {
		events.onLoad[i]();
	}
}

events.onResize = []
events.addOnResize = function( func ) {

	events.onResize[events.onResize.length] = func;
}
events.runOnResize = function() {

	for( i = 0; i < events.onResize.length; i++ ) {
		events.onResize[i]();
	}
}

events.addListener = function( elem, type, func ) {

	if( elem.addEventListener ) {
		elem.addEventListener( type, func, false );
	} else if( elem.attachEvent ) {
		elem.attachEvent( 'on' + type, func );
	}
}

events.init = function() {

	events.addListener( window, 'resize', events.runOnResize );
	events.addListener( window, 'load', events.runOnLoad );
}

events.init();

