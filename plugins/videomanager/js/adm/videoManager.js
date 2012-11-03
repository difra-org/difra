function addVideo( vId ) {

	$( '#videoAction-' + vId ).slideUp();
	$( '#videoAdd-' + vId ).slideDown();
};

function closeAdd( vId ) {

	$( '#videoAction-' + vId ).slideDown();
	$( '#videoAdd-' + vId ).slideUp();
};

function addPoster( vId ) {

	$( '#posterChangeDiv-' + vId ).slideDown();
	$( '#posterDiv-' + vId ).slideUp();
};

function closeAddPoster( vId ) {

	$( '#posterChangeDiv-' + vId ).slideUp();
	$( '#posterDiv-' + vId ).slideDown();
};

function editVideoName( vId ) {

	$( '#videoNameDiv-' + vId ).slideUp();
	$( '#videoNameDivEdit-' + vId ).slideDown();
	$( '#videoNameSubmit-' + vId ).slideDown();
};

function videoEditClose( vId ) {

	$( '#videoNameSubmit-' + vId ).slideUp();
	$( '#videoNameDivEdit-' + vId ).slideUp();
	$( '#videoNameDiv-' + vId ).slideDown();
};