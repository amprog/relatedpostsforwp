jQuery( function ( $ ) {
    var rp4wp_is_submitting = false;
    $( '#rp4wp-settings-form' ).submit( function () {
        if ( rp4wp_is_submitting ) {
            return false;
        }
        rp4wp_is_submitting = true;
        $( this ).find( '#submit' ).attr( 'disabled', 'disabled' ).val( 'Saving ...' );
        return true;
    } )

} );

function rp4wpUploadImage( id ) {
    var uploader = wp.media( {
        title: 'Custom Image',
        button: {
            text: 'Upload Image'
        },
        multiple: false  // Set this to true to allow multiple files to be selected
    } ).on( 'select', function () {
        var attachment = uploader.state().get( 'selection' ).first().toJSON();
        jQuery( '#' + id ).val( attachment.url );
    } ).open();
}