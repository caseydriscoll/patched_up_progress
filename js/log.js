jQuery( document ).ready( function() {

    vex.defaultOptions.className = 'vex-theme-wireframe';

    //http://stackoverflow.com/questions/16454375/how-to-detect-press-twice-the-same-key-but-hold-it-the-second-time-for-2-secon
    var twice_190 = 0;

    jQuery( document ).on( 'keyup' , function(e) {

        if ( e.which === 190 ) { // '>' key    

            if ( twice_190 === 1 ) {
                open_vex();

                twice_190 = 0;
            } else
                twice_190 = 1;
        } else {
            twice_190 = 0;
        }
    } );

} );

function open_vex() {
    vex.dialog.open({
        message: '',
        input: "<textarea name='content'></textarea>",
        buttons: [
                jQuery.extend({}, vex.dialog.buttons.YES, {
                text: 'Add to log'
            })
        ],
        callback: function(data) {
            if ( data === false ) {
            return console.log('Cancelled');
        }
            return submit_log( data.content );
        }
    });
}

function submit_log( content ) {
    alert( content );
}