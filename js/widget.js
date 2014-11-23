var beg_time = parseInt( progressWidget.beg_time );
var end_time = parseInt( progressWidget.end_time );

function setCurrentTime() {
	jQuery( '#patched_up_progress_current_time' ).hide();

	now = new Date(),
    then = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        0, 0, 0 ),

    elapsed_seconds = ( now.getTime() - then.getTime() ) / 1000 | 0;

	total_seconds = 60 * 60 * ( end_time - beg_time );

	elapsed_percent = ( elapsed_seconds - ( beg_time * 3600 ) ) / total_seconds * 100;

	min = now.getMinutes();
	if ( min < 10 ) min = '0' + min;

	hours = now.getHours();

	if ( hours < beg_time || hours >= end_time ) return;

	hours = hours % 12;
	if ( hours == 0 ) hours = 12;	


	jQuery( '#patched_up_progress_current_time' )
		.show().css( 'left', elapsed_percent + '%' )
		.find( '#patched_up_progress_current_time_display' ).html( hours + ":" + min);
}

jQuery( document ).ready( function() {
	var bar_width = jQuery( '#patched_up_progress_bar' ).width();
	var hour_length = bar_width / ( end_time - beg_time ); 

	setCurrentTime();
	
	setInterval( setCurrentTime, 60000 );

	jQuery( '#patched_up_progress_bar' ).on( 'mouseover', function() {

		jQuery( '#patched_up_progress_cursor_time' ).show();
		jQuery( '#patched_up_progress_current_time_display' ).show();
		jQuery( '#patched_up_progress_add_action' ).show();

	} ).on( 'mouseout', function() {
		
		jQuery( '#patched_up_progress_cursor_time' ).hide();
		jQuery( '#patched_up_progress_current_time_display' ).hide();
		jQuery( '#patched_up_progress_add_action' ).hide();

	} ).on( 'mousemove', function(e) {

		position = e.pageX - jQuery( '#patched_up_progress_bar' ).offset().left;
		position_percent = position / bar_width * 100;

		if ( position < 0 || position_percent > 100 ) return;

		jQuery( '#patched_up_progress_cursor_time' ).css( 'left' , position_percent + '%' );

		px_per_hour = bar_width / ( end_time - beg_time );

		hours = beg_time + ( ( end_time - beg_time ) * position_percent / 100 | 0 );

		min = ( position % px_per_hour ) / px_per_hour * 60 | 0; 
		if ( min < 10 ) min = '0' + min;
		if ( isNaN( min ) ) min = '00';

		hours = hours % 12;
		if ( hours == 0 ) hours = 12;	

		jQuery( '#patched_up_progress_cursor_time_display' ).html(
			hours + ":" + min 
		);
	} );

	jQuery( '#patched_up_progress_add_action' ).on( 'click', function() {
		jQuery( '#patched_up_progress_action' ).show().focus();
	} );

	jQuery( 'body' ).on( 'keyup', function(e) {
		if ( e.keyCode == 187 )
			jQuery( '#patched_up_progress_add_action' ).click();

		if ( e.keyCode == 27 )
			jQuery( '#patched_up_progress_action' ).hide();
	} );

	jQuery( '#patched_up_progress_action' ).on( 'keypress', function(e) {

		title = jQuery( e.target ).val();

		if ( title == '') return;

		if ( e.keyCode == 13 ) {
			jQuery( '.load' ).show();

			jQuery.post(
				'/wp-admin/admin-ajax.php', 
				{
					'action': 'add_action',
					'title': title 
				}, 
				function( response ){
					if ( response.success ) {
						jQuery( '.load' ).hide();
						jQuery( '#patched_up_progress_action' ).empty().hide();

						jQuery( '#patched_up_progress_response' )
							.html( "Successfully added '" + response.data.title + "'!" )
							.show().fadeOut( 3000 );
					}
				}
			);

		}
	} );
	

});
