jQuery( document ).ready( function() {
    jQuery( '.stop-action' ).on( 'click', function(e){
        e.preventDefault();
        
        if ( confirm( "Are you sure?" ) )
            jQuery.post(
                '/wp-admin/admin-ajax.php', 
                {
                    'action': 'stop_action',
                }, 
                function( response ){
                    if ( response.success ) {
                        location.reload();
                    } else {    
                    }
                }
            );
    } );
} );
