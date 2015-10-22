jQuery(function ($) {
    $('body').on('click', '.wpdm-download-locked.pop-over' ,function () {

        var $dc = $($(this).attr('href'));
        if ($(this).attr('data-ready') == undefined) {

            $(this).popover({
                placement: 'bottom',
                html: true,
                content: function () {

                    return $dc.html();


                }
            });
            $(this).attr('data-ready', 'hide');
        }

        if ($(this).attr('data-ready') == 'hide'){
            $(this).popover('show');
            $(this).attr('data-ready', 'show');
        } else if ($(this).attr('data-ready') == 'show'){
            $(this).popover('hide');
            $(this).attr('data-ready', 'hide');
        }


    return false;
    });

    $('body').on('click', '.po-close' ,function () {

        $('.wpdm-download-link').popover('hide');
        $('.wpdm-download-link').attr('data-ready','hide');

    });


    // Uploading files
    var file_frame, dfield;


    jQuery('body').on('click', '.wpdm-media-upload' , function( event ){
        event.preventDefault();
        dfield = jQuery(jQuery(this).attr('rel'));

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            dfield.val(attachment.url);

        });

        // Finally, open the modal
        file_frame.open();
    });

});