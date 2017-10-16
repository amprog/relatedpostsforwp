jQuery( document ).ready( function ( $ ) {

    $.fn.rp4wp_install_pt_table = function () {

        this.editing = false;

        this.available_post_type = $( '#rp4wp-availabe_post_types' ).val().split( ',' );

        this.get_row_by_pt = function ( pt ) {
            var row = $( this ).find( 'tr[rel=' + pt + ']' );
            if ( row ) {
                return row;
            }
            return null;
        };

        this.get_linked_post_types = function ( row ) {
            var linked = new Array();
            $.each( row.find( 'td.rp4wp-children ul li' ), function ( k, v ) {
                if ( $( v ).attr( 'id' ) ) {
                    linked.push( $( v ).attr( 'id' ) );
                }
            } );
            return linked;
        };

        this.get_available_post_types = function ( pt ) {

            var post_types = this.available_post_type.slice();
            var linked = this.get_linked_post_types( this.get_row_by_pt( pt ) );
            var available = new Array();

            for ( var i = 0; i < post_types.length; i++ ) {
                var pos = linked.indexOf( post_types[ i ] );
                if ( -1 == pos ) {
                    available.push( post_types[ i ] );
                }
            }

            return available;
        };

        this.disable_buttons = function () {
            $( this ).find( '.button' ).addClass( 'disabled' );
        };

        this.enable_buttons = function () {
            $( this ).find( '.button' ).removeClass( 'disabled' );
        };

        this.add_new_pt_button = function ( row ) {

            var instance = this;

            var available_pt = this.get_available_post_types( row.attr( 'rel' ) );

            // Only display 'add new pt' button if there are available post types
            if ( 0 == row.find( '.add-pt' ).length && available_pt.length > 0 ) {

                row.find( 'td.rp4wp-children ul' ).append(
                    $( '<li>' ).addClass( 'add-pt' ).append(
                        $( '<span>' ).html( 'Add New Post Type' )
                    ).click( function () {

                            var pt_select = $( '<select>' );
                            for ( var i = 0; i < available_pt.length; i++ ) {
                                pt_select.append(
                                    $( '<option>' ).val( available_pt[ i ] ).html( rp4wp_js[ available_pt[ i ] ] )
                                );
                            }

                            $( this ).parent().append(
                                $( '<li>' ).addClass( 'new-pt' ).append(
                                    pt_select
                                ).append(
                                    $( '<a>' ).attr( 'href', 'javascript:;' ).addClass( 'add-pt-plus rp4wp-has-tip' ).attr( 'title', rp4wp_js.lbl_add_pt ).html( '+' ).click( function () {
                                        instance.add_post_type( row, $( this ).parent().find( 'select option:selected' ).val() );
                                    } )
                                )
                            );
                            $( this ).remove();
                            Tipped.create( '.rp4wp-has-tip', { position: 'top' } );
                        } )
                )

            }

        };

        this.add_post_type = function ( row, pt ) {

            var instance = this;

            // Remove new-pt list item
            row.find( '.new-pt' ).remove();

            // Add new Post Type
            row.find( 'td.rp4wp-children ul' ).append(
                $( '<li>' ).attr( 'id', pt ).addClass( 'edit' ).append(
                    $( '<span>' ).html( rp4wp_js[ pt ] )
                ).append(
                    $( '<a>' ).addClass( 'remove-btn' ).click( function () {
                        $( this ).parent().remove()
                        instance.add_new_pt_button( row );
                    } )
                )
            );

            // Add 'new pt' button
            this.add_new_pt_button( row );

        };

        // Edit row function
        this.edit_row = function ( pt ) {

            var instance = this;

            this.editing = true;

            this.disable_buttons();

            var row = this.get_row_by_pt( pt );

            if ( !row ) {
                this.editing = false;
                return;
            }

            row.removeClass( 'inactive' );

            // Make linked PT's editable
            $.each( row.find( 'td.rp4wp-children ul li' ), function ( k, v ) {

                $( v ).addClass( 'edit' ).append(
                    $( '<a>' ).addClass( 'remove-btn' ).click( function () {
                        $( this ).parent().remove()
                        instance.add_new_pt_button( row );
                    } )
                )

            } );

            // Add 'Add post type' button
            this.add_new_pt_button( row );

            // Hide buttons
            row.find( '.rp4wp-button .rp4wp-buttons-wrap' ).hide();

            // Add new button
            row.find( '.rp4wp-button' ).append(
                $( '<a>' ).addClass( 'button button-primary' ).addClass( 'rp4wp-btn-save rp4wp-has-tip' ).attr( 'href', 'javascript:;' ).attr( 'rel', 'save' ).attr( 'title', rp4wp_js.lbl_save ).click( function () {
                    instance.save_row( pt );
                } )
            );

            Tipped.create( '.rp4wp-has-tip', { position: 'top' } );
        };

        this.restore_row = function ( pt ) {

            // Get row
            var row = this.get_row_by_pt( pt );

            // Remove new-pt list item
            row.find( '.add-pt' ).remove();
            row.find( '.new-pt' ).remove();

            // Restore linked post types list items
            $.each( row.find( 'td.rp4wp-children ul li' ), function ( k, v ) {
                $( v ).removeClass( 'edit' ).find( 'a' ).remove()
            } );

            // Remove save button
            row.find( 'a[rel=save]' ).remove();

            // Restore buttons
            row.find( '.rp4wp-button .rp4wp-buttons-wrap' ).show();

            // Check if the row is inactive
            if ( 0 === row.find( 'td.rp4wp-children ul li' ).length ) {
                row.addClass( 'inactive' );
                row.find( '.rp4wp-buttons-wrap a.button[rel=delete]' ).remove();
            }
        };

        this.save_row = function ( pt ) {

            // Restore row
            this.restore_row( pt );

            // Set editing to false
            this.editing = false;

            // Re-enable buttons
            this.enable_buttons();

            // AJAX
            $.post( ajaxurl, {
                action: 'rp4wp_install_set_post_types',
                parent: pt,
                nonce: $( '#rp4wp-ajax-nonce' ).val(),
                post_types: this.get_linked_post_types( this.get_row_by_pt( pt ) )
            }, function ( response ) {

                // Check if the response is success
                if ( 'success' === response.result ) {
                    if ( true === response.redirect ) {
                        window.location = $( '#rp4wp_admin_url' ).val() + '?page=rp4wp_install&pt=' + pt + '&step=2&rp4wp_nonce=' + $( '#rp4wp_nonce' ).val();
                    }
                } else {
                    alert( "Whoops! Something went wrong:\n\n" + response.error );
                }

            } );

        };

        this.generate_remove_button = function () {
            var instance = this;
            return $( '<a>' ).addClass( 'button' ).addClass( 'button-primary' ).addClass( 'rp4wp-btn-delete rp4wp-has-tip' ).attr( 'rel', 'delete' ).attr( 'href', 'javascript:;' ).attr( 'title', rp4wp_js.lbl_delete ).click( function () {
                if ( false === instance.editing ) {
                    var pt = $( this ).closest( 'tr' ).attr( 'rel' );

                    instance.edit_row( pt );

                    var row = instance.get_row_by_pt( pt );

                    $.each( row.find( 'td.rp4wp-children ul li' ), function ( k, v ) {
                        v.remove();
                    } );

                    instance.save_row( pt );

                }
            } );
        };

        this.generate_relink_button = function () {
            var instance = this;
            return $( '<a>' ).addClass( 'button' ).addClass( 'button-primary' ).addClass( 'rp4wp-btn-relink rp4wp-has-tip' ).attr( 'rel', 'rerun' ).attr( 'title', rp4wp_js.lbl_relink ).attr( 'href', 'javascript:;' ).click( function () {
                if ( false === instance.editing ) {
                    var pt = $( this ).closest( 'tr' ).attr( 'rel' );

                    // AJAX
                    $.post( ajaxurl, {
                        action: 'rp4wp_install_relink',
                        parent: pt,
                        nonce: $( '#rp4wp-ajax-nonce' ).val(),
                    }, function ( response ) {

                        // Check if the response is success
                        if ( 'success' === response.result ) {
                            if ( true === response.redirect ) {
                                window.location = $( '#rp4wp_admin_url' ).val() + '?page=rp4wp_install&pt=' + pt + '&step=3&rp4wp_nonce=' + $( '#rp4wp_nonce' ).val();
                            }
                        } else {
                            alert( "Whoops! Something went wrong:\n\n" + response.error );
                        }

                    } );

                }
            } );
        };

        this.generate_reinstall_button = function () {
            var instance = this;
            return $( '<a>' ).addClass( 'button' ).addClass( 'button-primary' ).addClass( 'rp4wp-btn-reinstall rp4wp-has-tip' ).attr( 'rel', 'rerun' ).attr( 'title', rp4wp_js.lbl_reinstall ).attr( 'href', 'javascript:;' ).click( function () {
                if ( false === instance.editing ) {
                    var pt = $( this ).closest( 'tr' ).attr( 'rel' );

                    // AJAX
                    $.post( ajaxurl, {
                        action: 'rp4wp_install_reinstall',
                        parent: pt,
                        nonce: $( '#rp4wp-ajax-nonce' ).val(),
                    }, function ( response ) {

                        // Check if the response is success
                        if ( 'success' === response.result ) {
                            if ( true === response.redirect ) {
                                window.location = $( '#rp4wp_admin_url' ).val() + '?page=rp4wp_install&pt=' + pt + '&step=2&rp4wp_nonce=' + $( '#rp4wp_nonce' ).val();
                            }
                        } else {
                            alert( "Whoops! Something went wrong:\n\n" + response.error );
                        }

                    } );

                }
            } );
        };


        // Init function
        this.init = function () {

            var instance = this;

            $.each( $( this ).find( 'tr' ), function ( k, v ) {

                if ( null != $( v ).attr( 'rel' ) ) {

                    var pt = $( v ).attr( 'rel' );

                    // Edit button
                    $( v ).find( 'a.button[rel=edit]' ).click( function () {
                        if ( false === instance.editing ) {
                            instance.edit_row( pt );
                        }
                    } );

                    if ( instance.get_linked_post_types( $( v ) ).length > 0 ) {
                        $( v ).find( '.rp4wp-buttons-wrap' ).append( instance.generate_relink_button() );
                        $( v ).find( '.rp4wp-buttons-wrap' ).append( instance.generate_reinstall_button() );
                        $( v ).find( '.rp4wp-buttons-wrap' ).append( instance.generate_remove_button() );
                    }

                }

            } );

            Tipped.create( '.rp4wp-has-tip', { position: 'top' } );
        };

        // Start init
        this.init();

    };

    // Determine steps
    var step = $( '.rp4wp-step' ).attr( 'rel' );

    // Checks steps
    if ( 1 == step ) {
        $( '.rp4wp-table-pt-overview' ).rp4wp_install_pt_table();
    } else if ( 2 == step ) {

        // Install the cache
        rp4wp_install_wizard( 2 );

    } else if ( 3 == step ) {

        // Link the posts
        $( '#rp4wp-link-now' ).click( function () {
            rp4wp_install_wizard( 3 );
        } );

    }

    function rp4wp_install_wizard( step ) {

        this.step = step;
        this.pt = null;
        this.linked_pt_count = 0;
        this.linked_pt_cur = 0;
        this.total_posts = 0;
        this.ppr = null;
        this.action = null;
        this.percentage_object = null;
        this.nonce = null;

        this.do_request = function () {
            var instance = this;
            $.post( ajaxurl, {
                action: this.action,
                nonce: $( '#rp4wp-ajax-nonce' ).val(),
                ppr: this.ppr,
                pt: this.pt,
                linked_pt_cur: this.linked_pt_cur
            }, function ( response ) {

                // The RegExp
                var response_regex = new RegExp( "^[0-9]+$" );

                // Trim that string o/
                response = response.trim();

                // Test it
                if ( response_regex.test( response ) ) {

                    var posts_left = parseInt( response );

                    // Do Progressbar
                    instance.do_progressbar( posts_left );

                    if ( posts_left > 0 ) {
                        // Do request
                        instance.do_request();
                    } else {
                        // Done
                        instance.done();
                    }

                } else {
                    alert( "Woops! Something went wrong while linking.\n\nResponse:\n\n" + response );
                }

            } );
        };

        this.done = function () {

            // Update progressbar
            $( '#progressbar' ).progressbar( { value: 100 } );

            var url = $( '#rp4wp_admin_url' ).val() + '?page=rp4wp_install&pt=' + this.pt + '&rp4wp_nonce=' + this.nonce;

            if ( 2 === this.step && this.linked_pt_cur < this.linked_pt_count ) {
                url += '&step=' + this.step + '&cur=' + ( this.linked_pt_cur + 1 );
            } else {
                url += '&step=' + ( this.step + 1 );
            }

            // Redirect to next step
            window.location = url;
        };

        this.do_progressbar = function ( posts_left ) {

            var posts_done = (this.total_posts - posts_left);

            if ( posts_done < 0 ) {
                posts_done = 0;
            }

            jQuery( '#progress-done' ).html( posts_done );
            jQuery( '#progress-todo' ).html( posts_left );

            var progress = Math.round( ( posts_done / this.total_posts ) * 100 );
            if ( progress > 0 ) {
                this.percentage_object.html( progress + '%' );
                $( '#progressbar' ).progressbar( { value: progress } );
            }
        };

        this.init = function () {

            // Set the Post Type
            this.pt = $( '#rp4wp_post_type' ).val();

            // Set the linked PT count
            this.linked_pt_count = parseInt( $( '#linked_pt_count' ).val() );

            // Set the linked PT cur
            this.linked_pt_cur = parseInt( $( '#linked_pt_cur' ).val() );

            // Set the Nonce
            this.nonce = $( '#rp4wp_nonce' ).val();

            // Setup the progressbar
            $( '#progressbar' ).progressbar( { value: false } );

            // Create the span
            this.percentage_object = jQuery( '<span>' );
            $( '#progressbar' ).find( 'div:first' ).append( this.percentage_object );

            // Set the current progress
            this.do_progressbar( 0 );

            // Get the total posts
            this.total_posts = $( '#rp4wp_total_posts' ).val();

            // Set the correct action
            switch ( this.step ) {
                case 2:
                    this.ppr = 25;
                    this.action = 'rp4wp_install_save_words';
                    break;
                case 3:
                    this.ppr = 5;
                    this.action = 'rp4wp_install_link_posts';
                    break;
            }

            // show process container
            jQuery( '#progress-container' ).show();

            // Save the options prior to starting requests
            if ( this.step == 3 ) {

                var instance = this;

                $.post( ajaxurl, {
                    action: 'rp4wp_install_save_options',
                    nonce: $( '#rp4wp-ajax-nonce' ).val(),
                    pt: this.pt,
                    rel_amount: $( '#rp4wp_related_posts_amount' ).val(),
                    rp4wp_related_posts_age: $( '#rp4wp_related_posts_age' ).val()
                }, function ( response ) {

                    // Trim that string o/
                    response = response.trim();

                    // Test it
                    if ( 'success' == response ) {

                        // start the actual process
                        instance.do_request();

                    } else {
                        alert( "Woops! Something went wrong while linking.\n\nResponse:\n\n" + response );
                    }

                } );

            } else {
                // Do the first request
                this.do_request();
            }

        };

        this.init();

    }

} );