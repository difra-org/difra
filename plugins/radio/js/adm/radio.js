function updateSizes() {
	var height = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		height = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		height = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		height = document.body.clientHeight;
	} else {
		return false;
	}
	$( '.playpist-container' ).css( 'height', ( height - 328 ) + 'px' );
	$( '.library-container' ).css( 'height', ( height - 287 ) + 'px' );
	$( '.artist-container' ).css( 'height', ( height - 247 ) + 'px' );
}
$( document ).ready( updateSizes );
$( window ).resize( updateSizes );

function startCounter( stopTime ) {

    console.info( 'stopTime = ' + stopTime );

	stopTime = (stopTime+5)*1000;

	currentTime = (new Date()).getTime();

    // для дебуга
    console.info( 'startTime = ' + currentTime );
    console.info( 'modifiedStopTime = ' + stopTime );
    console.info( 'diff = ' + (stopTime-currentTime) );

	if( currentTime>stopTime ) {

		$( '#countdown' ).html( 'Не играет! Что-то сломалось! Ахтунг!' );

	} else {
		$( '#countdown' ).countdown( {
			timestamp: stopTime,
			callback:function ( days, hours, minutes, seconds ) {
				if( minutes == 0 && seconds == 0 ) {
					window.location.reload();
				}
			}
		} );
	}
};

function showEdit( id ) {

	var showElem = document.getElementById( 'trackInfo-' + id );
	if( showElem.style.display == 'none' || showElem.style.display == '' ) {
		//$( showElem ).fadeIn( 'fast' );
		$( showElem ).slideDown( 'fast' );
	} else {
		$( showElem ).slideUp( 'fast' );
	}

};

function startEditList() {

	$( '#library, #activePlayList' ).sortable( {
		opacity: 0.4,
		connectWith: ".connectedSortable",
		cursor: 'move',
		placeholder: 'placebleWidget'
	} );

};

function savePlayList( channelName ) {

	req =  { 'channelName': channelName, 'songList': JSON.stringify( $( '#activePlayList' ).sortable( "toArray" ) ) };
	ajaxer.query( '/adm/radio/savelist/', req );

};

function changeTab( tabName ) {

	if( tabName=='history' ) {

		$( '#historyLibrary' ).show();
		$( '#radioLibrary' ).hide();
		$( '#libraryTab' ).removeClass( 'selectedTab' );
		$( '#historyTab' ).addClass( 'selectedTab' );

	}
	if( tabName=='library' ) {

		$( '#historyLibrary' ).hide();
		$( '#radioLibrary' ).show();
		$( '#libraryTab' ).addClass( 'selectedTab' );
		$( '#historyTab' ).removeClass( 'selectedTab' );

	}

};