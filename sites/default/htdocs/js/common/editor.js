var editor = {};

editor.config = {
	baseHref : '/js/ck',
	entities : false,
	height   : '320px',
	width    : '95%',

	toolbar  : [
		['Source'],
		['Maximize', 'ShowBlocks'],
		['Cut','Copy','Paste','PasteText','PasteFromWord'],
		['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
		['Link','Unlink'],
		['Image','Flash','Table','HorizontalRule','SpecialChar'],
		'/',
		['Bold','Italic','Underline','Strike'],
		[/*'Styles',*/'Format','Font','FontSize'],
		/*['Subscript','Superscript'],*/
		['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'/*,'CreateDiv'*/],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['TextColor','BGColor'],
	]
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

