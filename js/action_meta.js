jQuery( document ).ready( function() {
	jQuery( '.end-time' ).on( 'click', function() {
		endtime = moment();
		jQuery( 'input[name="action_end_time"]' ).val( endtime.format( 'YYYY-MM-DD HH:mm:ss') );
	} );
} );
