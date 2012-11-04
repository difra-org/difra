var gallery = [];

gallery.view = function( event, id, imgFormat ) {

	var view = $( '.galleryView' );
	if( !view.length ) {
		view = $( '<div class="overlay galleryView" style="display:none"></div>' ).appendTo( 'body' );
		view.fadeIn();
	} else {
		var oldArea = view.find( '.overlay-container' );
	}
	var viewArea = $(
		'<div class="overlay-container auto-center" style="max-width:900px;opacity:0.0001">' +
			'<div class="overlay-inner">' +
			'<div class="overlay-gallery">' +
			'<img src="/gallery/' + id + 'l.' + imgFormat + '"/>' +
			'</div>' +
			'<a href="#"><div class="close">╳</div></a>' +
			'<a href="#"><div class="prev">◀</div></a>' +
			'<a href="#"><div class="next">▶</div></a>' +
			'</div>' +
			'</div>'
	).appendTo( view );
	viewArea.find( 'img' ).bind( 'load', function() {
		viewArea.resize().css( 'display', 'none' ).css( 'opacity', '1' );
		viewArea.fadeIn( function() {
			if( typeof oldArea == 'object' ) {
				oldArea.remove();
			}
		} );
	} );
	var prev = $( 'a#galleryThumb_' + id ).prev( 'a.gallery' );
	var prevObj = viewArea.find( '.prev' );
	if( prev.length ) {
		prevObj.bind( 'click', function() {
			prev.click();
		} );
	} else {
		prevObj.addClass( 'disabled' );
	}
	var next = $( 'a#galleryThumb_' + id ).next( 'a.gallery' );
	var nextObj = viewArea.find( '.next' );
	if( next.length ) {
		nextObj.bind( 'click', function() {
			next.click();
		} );
	} else {
		nextObj.addClass( 'disabled' );
	}
	viewArea.find( '.close' ).bind( 'click', function() {
		$( '.galleryView' ).fadeOut( function() {
			$( '.galleryView' ).remove()
		} );
	} );
	event.preventDefault();
};