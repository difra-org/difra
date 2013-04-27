var loading = {};

loading.show = function() {
	var overlay = $( '#loading-overlay' );
	if( !overlay.length ) {
		overlay = $( '<div id="loading-overlay" style="display:none"><div class="loading-logo">01234567</div></div>' );
		$( 'body' ).append( overlay );
	}
	overlay.fadeIn();
};

loading.hide = function() {

	var overlay = $( '#loading-overlay' );
	if( overlay.length ) {
		overlay.fadeOut();
	}
};