
var portfolio = [];

portfolio.view = function( event, id, imgFormat, number ) {

	var view = $( '.portfolioView' );
	if( !view.length ) {
		view = $( '<div class="overlay portfolioView" style="display:none"></div>' ).appendTo( 'body' );
		view.fadeIn();
	} else {
		var oldArea = view.find( '.overlay-container' );
	}
	var viewArea = $(
		'<div class="overlay-container auto-center" style="max-width:1200px;opacity:0.0001">' +
			'<div class="overlay-inner">' +
			'<div class="overlay-portfolio">' +
			'<img src="/portimages/' + id + '-full.' + imgFormat + '"/>' +
			'</div>' +
			'<a href="#"><div class="close">╳</div></a>' +
			'<a href="#"><div class="prev">◀</div></a>' +
			'<a href="#"><div class="next">▶</div></a>' +
			'</div>' +
			'</div>'
	).appendTo( view );
	viewArea.find( 'img' ).bind( 'load', function () {
		viewArea.resize().css( 'display', 'none' ).css( 'opacity', '1' );
		viewArea.fadeIn( function () {
			if( typeof oldArea == 'object' ) {
				oldArea.remove();
			}
		} );
	} );

	// ищем предыдущий элемент
	var prev = $( 'a#portfolioThumb_' + number ).parent().parent().find( 'a#portfolioThumb_' + (number-1) );
	//var prev = $( 'a#portfolioThumb_' + id ).prev( 'a.portfolio' );

	var prevObj = viewArea.find( '.prev' );
	if( prev.length ) {
		prevObj.bind( 'click', function () {
			prev.click();
		} );
	} else {
		prevObj.addClass( 'disabled' );
	}

	// ищем следующий элемент
	var next = $( 'a#portfolioThumb_' + number ).parent().parent().find( 'a#portfolioThumb_' + (number+1) );

	var nextObj = viewArea.find( '.next' );
	if( next.length ) {
		nextObj.bind( 'click', function () {
			next.click();
		} );
	} else {
		nextObj.addClass( 'disabled' );
	}
	viewArea.find( '.close' ).bind( 'click', function () {
		$( '.portfolioView' ).fadeOut( function () {
			$( '.portfolioView' ).remove()
		} );
	} );
	event.preventDefault();
};
