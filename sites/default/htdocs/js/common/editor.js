var editor = {};

editor.config = {
	baseHref : '/js/ck',
	entities : false,
	height   : '320px',
	width    : '95%',

	/*
	toolbar  : [
		['Source','-','Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Blockquote'],
		['Link','Image','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
		'/',
		['Styles','Format','Font','FontSize'],
		['TextColor','BGColor'],
		['Maximize', 'ShowBlocks']
	]*/
}	

editor.init = function() {

	if( typeof CKEDITOR == 'undefined' ) {
		return false;
	}

	var textareas = document.getElementsByTagName( 'textarea' );
	if( !textareas ) {
		return false;
	}

	for( i = 0; i < textareas.length; i++ ) {
		var noeditor = textareas[i].getAttribute( 'noeditor' );
		if( !noeditor ) {
			CKEDITOR.replace( textareas[i].id, editor.config );
		}
	}
}

events.addOnLoad( editor.init );

