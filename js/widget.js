var beg_time = parseInt( progressWidget.beg_time );
var end_time = parseInt( progressWidget.end_time );
var total_seconds = 60 * 60 * ( end_time - beg_time );

var pupp = '#patched_up_progress'; 

function setCurrentTime() {
	jQuery( pupp + '_current_time' ).hide();

	now = new Date(),
    then = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        0, 0, 0 ),

    elapsed_seconds = ( now.getTime() - then.getTime() ) / 1000 | 0;
	elapsed_percent = ( elapsed_seconds - ( beg_time * 3600 ) ) / total_seconds * 100;

	min = now.getMinutes();
	if ( min < 10 ) min = '0' + min;

	hours = now.getHours();

	if ( hours < beg_time || hours >= end_time ) return;

	hours = hours % 12;
	if ( hours == 0 ) hours = 12;	

	jQuery( pupp + '_current_time' )
		.show().css( 'left', elapsed_percent + '%' )
		.find( pupp + '_current_time_display' ).html( hours + ":" + min);

}

function setInitialTime() {
	setCurrentTime(); 

	now = new Date();
	setTimeout( resetTime, ( 60 - now.getSeconds() ) * 1000 );
}

function resetTime() {
	setInterval( setCurrentTime, 60000 );
}

function setActionTimes() {
	jQuery( '#patched_up_actions li' ).each( function() {
		action_time = jQuery( this ).data( 'time' ).split( ':' );
		
		action_time_in_sec = action_time[0] * 3600 + action_time[1] * 60; 

		elapsed_percent = ( action_time_in_sec - ( beg_time * 3600 ) ) / total_seconds * 100;

		jQuery( this ).css( 'left', elapsed_percent + '%' ).show();

	} ); 
}

jQuery( document ).ready( function() {
	var bar_width = jQuery( pupp + '_bar' ).width();
	var hour_length = bar_width / ( end_time - beg_time ); 

	setInitialTime();

	setActionTimes();

	jQuery( pupp + '_bar' ).on( 'mouseover', function() {

		jQuery( pupp + '_cursor_time' ).show();
		jQuery( pupp + '_current_time_display' ).show();

		if ( jQuery( 'body' ).hasClass( 'logged-in' ) ) { 
			jQuery( pupp + '_add_btn' ).show();

			if ( jQuery( pupp + '_action' ).css( 'display' ) != 'none' )
				jQuery( pupp + '_close_btn' ).show();
		}

	} ).on( 'mouseout', function() {
		
		jQuery( pupp + '_cursor_time' ).hide();
		jQuery( pupp + '_current_time_display' ).hide();
		jQuery( '.btn' ).hide();

	} ).on( 'mousemove', function(e) {

		position = e.pageX - jQuery( pupp + '_bar' ).offset().left;
		position_percent = position / bar_width * 100;

		if ( position < 0 || position_percent > 100 ) return;

		jQuery( pupp + '_cursor_time' ).css( 'left' , position_percent + '%' );

		px_per_hour = bar_width / ( end_time - beg_time );

		hours = beg_time + ( ( end_time - beg_time ) * position_percent / 100 | 0 );

		min = ( position % px_per_hour ) / px_per_hour * 60 | 0; 
		if ( min < 10 ) min = '0' + min;
		if ( isNaN( min ) ) min = '00';

		hours = hours % 12;
		if ( hours == 0 ) hours = 12;	

		jQuery( pupp + '_cursor_time_display' ).html(
			hours + ":" + min 
		);
	} );

	jQuery( pupp + '_add_btn' ).on( 'click', function() {
		if ( jQuery( 'body' ).hasClass( 'logged-in' ) ) {
			jQuery( pupp + '_action' ).show().focus();
			jQuery( pupp + '_close_btn' ).show();
		} else {
			window.location = '/wp-login.php';
		}
	} );

	jQuery( pupp + '_close_btn' ).on( 'click', function() {
		jQuery( pupp + '_close_btn' ).hide();
		jQuery( pupp + '_action' ).empty().hide();
	} );

	jQuery( 'body' ).on( 'keyup', function(e) {
		if ( e.keyCode == 187 ) // +
			jQuery( pupp + '_add_btn' ).click();

		if ( e.keyCode == 27 ) // esc
			jQuery( pupp + '_close_btn' ).click();
	} );

	jQuery( pupp + '_action' ).on( 'keypress', function(e) {

		title = jQuery( e.target ).val();

		if ( title == '') return;

		if ( e.keyCode == 13 ) { // enter
			jQuery( '.load' ).show();

			jQuery.post(
				'/wp-admin/admin-ajax.php', 
				{
					'action': 'add_action',
					'title': title 
				}, 
				function( response ){
					if ( response.success ) {
						jQuery( '.load, .btn' ).hide();
						jQuery( pupp + '_action' ).val( '' ).hide();

						jQuery( pupp + '_response' )
							.html( "Successfully added '" + response.data.title + "'!" )
							.show().fadeOut( 2000 );
					} else {
						jQuery( '.load' ).hide();
						jQuery( pupp + '_action' ).empty().hide();

						jQuery( pupp + '_response' )
							.addClass( 'error' ).html( "Nope!" )
							.show().fadeOut( 2000 );
						
					}
				}
			);

		}
	} );

});
