$(document).on('change', '.plugins-toggle', function () {
	ajaxer.query('/adm/development/plugins/' +
		( this.checked ? 'enable' : 'disable' ) +
		'/' +
		this.name);
});
