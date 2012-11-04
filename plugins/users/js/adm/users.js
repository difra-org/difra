
function addAdditionalField() {

	$( '#additionalsAddFields' ).show();
	$( '#addedFields' ).append( '<tr><th style="padding-left: 0"><input type = "text" name="additional_name[]" placeholder="Название" /></th>' +
					'<td><input type="text" name="additional_value[]" placeholder="Значение" /></td></tr>' );

};