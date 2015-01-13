$( '.comment-reply a' ).live( 'click dblclick', function( event ) {

	var container = $( this ).parent( '.comment-reply' );
	var id = container.attr( 'comment_id' );
	$( '.comment-reply-summon' ).css( 'display', 'inline' );
	$( this ).children( '.comment-reply-summon' ).css( 'display', 'none' );
	container.append( $( '#newComment' )[0] );
	$( '#newComment #replyId' ).val( id );
	event.preventDefault();
} );

$( '#commentCancel' ).live( 'click dblclick', function( event ) {
	$( '.comment-reply-summon' ).css( 'display', 'inline' );
	$( '#newCommentPlace' ).append( $( '#newComment' )[0] );
	$( '#newComment #replyId' ).val( 0 );
	event.preventDefault();
} );