$.ajaxSetup( {
	async : false,
	cache :	false,
	headers : {
		'X-Requested-With' : 'XMLHttpRequest'
	},
	beforeSend : function() {
		$( '#loading' ).css( 'display', 'block' );

	},
	complete : function() {
		$( '#loading' ).css( 'display', 'none' );
	}
} );

var main = {
	context: this
};

main.httpRequest = function( url, params ) {

	var data = {};
	if( typeof params == 'undefined' ) {
		data.type = 'GET';
	} else {
		data.type = 'POST';
		data.data = 'json=' + JSON.stringify( params );
	}
	return $.ajax( url, data ).responseText;
};

