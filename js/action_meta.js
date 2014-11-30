jQuery( document ).ready( function() {
	jQuery( '.end-time' ).on( 'click', function() {
		endtime = new Date();
		jQuery( 'input[name="action_end_time"]' ).val( endtime.getHours() + ":" + endtime.getMinutes() );
	} );
} );
