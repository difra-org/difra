/**
 * При обнаружении textarea с аттрибутом editor загружает CKEditor для этого элемента.
 */
var editor = {};

var CKEDITOR_BASEPATH = '/ck/';

editor.config = {
	entities: false,
	removePlugins: 'elementspath',
	customConfig: '',
	contentsCss: ['/css/main.css', '/css/editor.css'],
	fullPage: false,
	filebrowserUploadUrl: '/up',

	toolbar: 'Default',
	toolbar_Default: [
		['Source'],
		['Maximize', 'ShowBlocks'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'],
		['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
		['Link', 'Unlink'],
		['Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar'],
		'/',
		['Bold', 'Italic', 'Underline', 'Strike'],
		['Format', 'Font', 'FontSize'],
		['Subscript', 'Superscript'],
		['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv'],
		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		['TextColor', 'BGColor']
	],

	toolbar_Medium: [
		{ name: 'styles', items: ['Styles', 'Format'] },
		{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
		{ name: 'clipboard', items: ['PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
		{ name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
		{ name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
		{ name: 'insert', items: ['Image', /*'Flash',*/ 'Table', 'HorizontalRule', 'SpecialChar'] }
	],

	toolbar_Full: [
		{ name: 'styles', items: ['Styles', 'Format'] },
		{ name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
		{ name: 'clipboard', items: ['PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
		{ name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
		{ name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
		{ name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar'] },
		{ name: 'tools', items: ['Maximize', 'ShowBlocks', 'Source'] }
	]

	/*
	 * Full toolbar
	 { name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
	 { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
	 { name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
	 { name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
	 '/',
	 { name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	 { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
	 { name: 'links', items : [ 'Link','Unlink','Anchor' ] },
	 { name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
	 '/',
	 { name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
	 { name: 'colors', items : [ 'TextColor','BGColor' ] },
	 { name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	 */
};

editor.inited = false;
editor.init = function() {

	if( editor.inited ) {
		return;
	}
	editor.inited = true;
	$( '<script src="/ck/ckeditor.js"></script>' ).appendTo( 'head' );
	CKEDITOR.basePath = '/ck/';
	CKEDITOR.plugins.basePath = '/ck/plugins/';

	CKEDITOR.on( 'dialogDefinition', function( ev ) {
		switch( ev.data.name ) {
		case 'link':
			ev.data.definition.removeContents( 'advanced' );
			ev.data.definition.removeContents( 'target' );
			break;
		case 'image':
			ev.data.definition.removeContents( 'advanced' );
			ev.data.definition.removeContents( 'Link' );
			break;
		case 'flash':
			ev.data.definition.removeContents( 'advanced' );
			ev.data.definition.removeContents( 'Upload' );
		}
	} );

};

editor.inject = function() {

	$( 'textarea' ).each( function() {
		if( $( this ).attr( 'editor' ) ) {
			editor.init();
			editor.config.bodyClass = $( this ).attr( 'bodyClass' );
			editor.config.toolbar = $( this ).attr( 'editor' );

			/* TODO: в блогах переделать fileupload на обычную загрузку через Editor */
			if( $( this ).attr( 'fileupload' ) ) {
				editor.config['filebrowserUploadUrl'] = '/uploadimage/';
			}

			CKEDITOR.replace( this, editor.config );
		}
	} );
};

editor.clean = function() {

	if( typeof CKEDITOR !== 'undefined' && typeof CKEDITOR.instances !== 'undefined' ) {
		for( var instance in CKEDITOR.instances ) {
			try {
				CKEDITOR.instances[instance].destroy( true );
			} catch( err ) {
				console.warn( 'Error: ', err.message );
			}
		}
	}
};

editor.flush = function() {

	if( typeof CKEDITOR !== 'undefined' && typeof CKEDITOR.instances !== 'undefined' ) {
		for( var instance in CKEDITOR.instances ) {
			CKEDITOR.instances[instance].updateElement();
		}
	}

};

$( document ).ready( editor.inject );

// поддержка switcher
$( document ).bind( 'construct', editor.inject );
$( document ).bind( 'destruct', editor.clean );

// поддержка ajaxer
$( document ).bind( 'form-submit', editor.flush );
