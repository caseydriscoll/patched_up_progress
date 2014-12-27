jQuery( document ).ready( function() {

    jQuery( 'select#tasks' ).on( 'change', function(e) {
        console.log( jQuery( e.target ).val() );
        window.location = progressReportData.admin_url + '&task=' + jQuery( e.target ).val()
    } );



} );