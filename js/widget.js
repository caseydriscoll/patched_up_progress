var beg_time = parseInt( progressWidget.beg_time );
var end_time = parseInt( progressWidget.end_time );
var total_seconds = 60 * 60 * ( end_time - beg_time );
var current_action_width = 0;

var pupp = '#patched_up_progress'; 

function setCurrentTime() {
	jQuery( pupp + '_current_time' ).hide();

	now = new Date();
    then = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        0, 0, 0 );

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

	if ( current_action_width != 0 )
		setCurrentActionWidth( jQuery( '.current' ) );
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

		action_end_time = jQuery( this ).data( 'end' ).split( ':' );
		action_end_time_in_sec = action_end_time[0] * 3600 + action_end_time[1] * 60; 
		elapsed_percent = ( action_time_in_sec - ( beg_time * 3600 ) ) / total_seconds * 100;

		jQuery( this ).css( 'left', elapsed_percent + '%' ).show();

		if ( jQuery( this ).data( 'end' ) == '' ) {
			setCurrentActionWidth( this );
		} else {
			sec_per_px = total_seconds / jQuery( pupp + '_bar' ).width();
			width = ( action_end_time_in_sec - action_time_in_sec ) / sec_per_px + 1;

			jQuery( this ).css( 'width', width + 'px' ).show();
		}

	} ); 
}

function setCurrentActionWidth( action ) {
	current_action_width = jQuery( pupp + '_current_time').offset().left - jQuery( action ).offset().left + 1;

	jQuery( action ).css( 'width', current_action_width + 'px' ).show();

	if ( current_action_width > 3 ) jQuery( '.blink' ).removeClass( 'blink' );
}

var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substrRegex;
 
    matches = [];
 
    substrRegex = new RegExp(q, 'i');
 
    jQuery.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        matches.push({ value: str });
      }
    });
 
    cb(matches);
  };
};

jQuery( document ).ready( function() {
	Tipped.create( '#patched_up_actions li' );

	jQuery( pupp + '_action' ).typeahead({
		  hint: true,
		  highlight: true,
		  minLength: 1
	},
	{
		  name: 'actions',
		  displayKey: 'value',
		  source: substringMatcher( progressWidget.actions )
	});

	jQuery( pupp + '_task' ).typeahead({
		  hint: true,
		  highlight: true,
		  minLength: 1
	},
	{
		  name: 'tasks',
		  displayKey: 'value',
		  source: substringMatcher( progressWidget.tasks )
	});


	var bar_width = jQuery( pupp + '_bar' ).width();
	var hour_length = bar_width / ( end_time - beg_time ); 

	setInitialTime();

	setActionTimes();

	if ( current_action_width <= 3 && current_action_width != 0 ) 
		jQuery( pupp + '_current_time' ).addClass( 'blink' );

	jQuery( pupp + '_bar' ).on( 'mouseover', function() {

		jQuery( pupp + '_cursor_time' ).show();
		jQuery( pupp + '_current_time_display' ).show();

		if ( 
			jQuery( 'body' ).hasClass( 'logged-in' ) ||
			jQuery( 'body' ).hasClass( 'wp-admin' ) 
		) { 
			jQuery( pupp + '_add_btn' ).show();

			if ( jQuery( '#patched_up_actions li' ).last().hasClass( 'current' ) )
				jQuery( pupp + '_stop_btn' ).show();

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
		if ( 	jQuery( 'body' ).hasClass( 'logged-in' ) || 
				jQuery( 'body' ).hasClass( 'wp-admin' ) 
		) {
			jQuery( '.twitter-typeahead'  ).removeClass( 'hide' );
			jQuery( '.tt-hint' ).show();
			jQuery( pupp + '_action' ).show().focus();
			jQuery( pupp + '_task' ).show();
			jQuery( pupp + '_content' ).show();
			jQuery( pupp + '_close_btn' ).show();
		} else {
			window.location = '/wp-login.php';
		}
	} );

	jQuery( pupp + '_close_btn' ).on( 'click', function() {
		jQuery( pupp + '_close_btn' ).hide();
		jQuery( pupp + '_action' ).empty().hide();
		jQuery( pupp + '_task' ).empty().hide();
		jQuery( pupp + '_content' ).empty().hide();
		jQuery( '.twitter-typeahead' ).addClass( 'hide' );
		jQuery( '.tt-hint' ).hide();
	} );

	jQuery( pupp + '_stop_btn' ).on( 'click', function() {
		jQuery( pupp + '_stop_btn' ).hide();
		jQuery( '.current' ).removeClass( 'current' );

		jQuery.post(
			'/wp-admin/admin-ajax.php', 
			{
				'action': 'stop_action',
			}, 
			function( response ){
				if ( response.success ) {
					jQuery( pupp + '_response' )
						.html( "Stopped " + response.data.title + "" )
						.show().fadeOut( 2000 );
				} else {	
				}
			}
		);

	} );

	jQuery( 'body' ).on( 'keyup', function(e) {
		if ( jQuery( pupp + '_action' ).is( ':focus' ) )
			return;

		if ( e.keyCode == 187 ) // +
			jQuery( pupp + '_add_btn' ).click();

		if ( e.keyCode == 189 ) // -
			if ( confirm( "Are you sure?" ) )
				jQuery( pupp + '_stop_btn' ).click();

		if ( e.keyCode == 27 ) // esc
			jQuery( pupp + '_close_btn' ).click();
	} );

	jQuery( pupp + '_action, ' + pupp + '_task, ' + pupp + '_content' ).on( 'keypress', function(e) {

		action  = jQuery( pupp + '_action' ).val();
		task    = jQuery( pupp + '_task' ).val();
		content = jQuery( pupp + '_content' ).val();

		if ( action == '') return;

		if ( e.keyCode == 27 ) // esc
			jQuery( pupp + '_close_btn' ).click();
		else if ( e.keyCode == 13 ) { // enter
			jQuery( '.load' ).show();

			jQuery.post(
				'/wp-admin/admin-ajax.php', 
				{
					'action': 'add_action',
					'action_title': action,
					'task' : task,
					'content' : content
				}, 
				function( response ){
					if ( response.success ) {
						now = new Date();
						then = new Date(
							now.getFullYear(),
							now.getMonth(),
							now.getDate(),
							0, 0, 0 );

						boundary_time = now.getHours() + ":" + now.getMinutes();
						jQuery.data( jQuery( '.current' ), 'end', boundary_time );

						jQuery( '.current' ).removeClass( 'current' );

						elapsed_seconds = ( now.getTime() - then.getTime() ) / 1000 | 0;
						elapsed_percent = ( elapsed_seconds - ( beg_time * 3600 ) ) / total_seconds * 100;

						new_li = '<li style="left:' + elapsed_percent + '%;display:list-item;" class="current"></li>'; 

						jQuery( '#patched_up_actions' ).append( new_li );
						jQuery( pupp + '_current_time' ).addClass( 'blink' );

						jQuery( '.load, .btn, .tt-hint' ).hide();
						jQuery( pupp + '_action' ).val( '' ).hide();
						jQuery( pupp + '_task' ).val( '' ).hide();
						jQuery( pupp + '_content' ).val( '' ).hide();
						jQuery( '.twitter-typeahead' ).addClass( 'hide' );

						jQuery( pupp + '_response' )
							.html( "Started " + response.data.action + " " + response.data.task )
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
