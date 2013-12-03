function changePassEnabler() {

	var dis = $('#changePw').is(':checked');
	if( dis === true ) {
		$( '#newPw' ).removeAttr( 'disabled' );
	} else {
		$( '#newPw' ).attr( 'disabled', 'disabled' );
	}
};

function changeNotifyEnabler() {

	var dis = $('#sendNotify').is(':checked');
	if( dis === true ) {
		$( '#notifyList' ).removeAttr( 'disabled' );
	} else {
		$( '#notifyList' ).attr( 'disabled', 'disabled' );
	}
};

function changeBehaviorEnabler( el ) {

	var dis = $( el ).val();
	if( dis == 'redirect' ) {
		$( '#redirectURI' ).removeAttr( 'disabled' );
	} else {
		$( '#redirectURI' ).attr( 'disabled', 'disabled' );
	}
};

function deleteAddtitionalField( delObj ) {
	$( delObj ).parent().parent().remove();
};

function addAdditionalField() {

	var addedHtml = '<tr class="additionalField"><td><input type="text" name="fieldName[]"/></td>' +
			'<td><input type="text" name="fieldValue[]" class="full-width"/></td>' +
			'<td><a href="#" class="action delete" onclick="deleteAddtitionalField( this )"/></td></tr>';

	$( 'tr.additionalField:last' ).after( addedHtml );



};