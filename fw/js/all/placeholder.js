// XXX: Тут должен быть универсальный код, который обеспечит работу аттрибута <input placeholder="... в браузерах, которые не поддерживают HTML5

/*
 На заметку: if (!elementSupportsAttribute('textarea', 'placeholder')) { — это для определения поддержки браузером
 
$(document).ready( function() {
	$('[placeholder]').focus(function() {
		var input = $(this);
		if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.removeClass('placeholder');
		}
	}).blur(function() {
		var input = $(this);
		if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		}
	}).blur();

	$('[placeholder]').parents('form').submit(function() {
		$(this).find('[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
				input.val('');
			}
		})
	});
} );
 
 */
