var catalog = {};

catalog.switchCategory = function( obj ) {

	ajaxer.redirect( '/adm/catalog/items/category/' + $( obj ).val() );
};

catalog.warnings = {
	change12: '',
	change21: '',
	change20: '',
	change10: ''
};
catalog.extWarning = function( oldVal, newVal ) {

	if( oldVal == 1 && newVal == 2 ) {
		$( '#extWarning' ).html( catalog.warnings.change12 ).fadeIn();
	} else if( oldVal == 2 && newVal == 1 ) {
		$( '#extWarning' ).html( catalog.warnings.change21 ).fadeIn();
	} else if( oldVal == 2 && newVal == 0 ) {
		$( '#extWarning' ).html( catalog.warnings.change20 ).fadeIn();
	} else if( oldVal == 1 && newVal == 0 ) {
		$( '#extWarning' ).html( catalog.warnings.change10 ).fadeIn();
	} else {
		$( '#extWarning' ).fadeOut();
	}
};