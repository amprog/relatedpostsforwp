/**
 * Main configurator class
 *
 * @param tgt
 * @constructor
 */
var RP4WP_Configurator = function ( tgt ) {

    this.container = tgt;
    this.configurator = null;
    this.grid = null;

    /**
     * Load the components
     */
    this.loadComponents = function () {
        var instance = this;

        jQuery.each( jQuery( '.rp4wp-components ul li a' ), function ( k, v ) {
            jQuery( v ).click( function () {

                // new component
                var new_component = new RP4WP_Component( jQuery( this ).data( 'type' ) );

                // check if it fits
                if ( instance.grid.will_it_fit( new_component.getX(), new_component.getY(), new_component.getW(), new_component.getH(), true ) ) {

                    // prompt data
                    new_component.promptData();

                    // set remove callback
                    new_component.setRemoveCB( function ( element ) {
                        instance.removeComponent( element )
                    } );

                    // add to grid
                    instance.grid.add_widget( new_component.getEl(), new_component.getX(), new_component.getY(), new_component.getW(), new_component.getH(), true );
                } else {
                    swal( "Out Of Space!", "Can't add component because you're out of space! Please remove a component first.", 'error' );
                }

            } );
        } );
    };

    /**
     * Load the presets
     */
    this.loadPresets = function () {
        var instance = this;

        jQuery.each( jQuery( '.rp4wp-presets ul li a' ), function ( k, v ) {
            jQuery( v ).click( function () {

                var preset = jQuery( this ).data( 'config' );

                jQuery( '#posts_per_row:first' ).val( JSON.stringify( preset.ppr ) );
                jQuery( '#fixed_height:first' ).val( JSON.stringify( preset.height ) );

                instance.grid.remove_all();
                jQuery( '.rp4wp-config:first' ).val( JSON.stringify( preset.config ) );
                instance.loadConfig();

            } );
        } );
    };

    /**
     * Load the config
     */
    this.loadConfig = function () {
        // The components
        var components = [];

        // get & check JSON string
        var conf_json_str = jQuery( '.rp4wp-config:first' ).val();
        if ( '' != conf_json_str ) {

            var instance = this;

            try {
                // parse JSON
                var config = JSON.parse( conf_json_str );

                if ( config.length > 0 ) {
                    for ( var i = 0; i < config.length; i++ ) {
                        // current config item
                        var cur = config[ i ];

                        // check for custom data
                        var cur_custom = (undefined != cur.custom) ? cur.custom : null;

                        // create new component
                        var component = new RP4WP_Component( cur.type, cur.x, cur.y, cur.width, cur.height, cur_custom );

                        // set remove callback
                        component.setRemoveCB( function ( element ) {
                            instance.removeComponent( element )
                        } );

                        // add component to grid
                        this.grid.add_widget( component.getEl(), component.getX(), component.getY(), component.getW(), component.getH(), true );
                    }
                }

            } catch ( e ) {
                swal( "Config parse error!", "I'm unable to parse the config JSON string!\nPlease contact support! (Error code: C1)", "error" );
            }


        }

    };

    /**
     * Bind our form action
     */
    this.bindForm = function () {

        // instance
        var instance = this;

        // hold it right there
        this.container.closest( 'form' ).submit( function () {

            // dat data \o/
            var res = _.map( jQuery( instance.container ).find( '.grid-stack-item:visible' ), function ( el ) {
                el = jQuery( el );
                var gsic = el.find( '.grid-stack-item-content:first' );
                var node = el.data( '_gridstack_node' );

                return {
                    type: gsic.data( 'type' ),
                    custom: gsic.data( 'custom' ),
                    x: node.x,
                    y: node.y,
                    width: node.width,
                    height: node.height
                };
            } );

            // Set value
            jQuery( '.rp4wp-config:first' ).val( JSON.stringify( res ) );

            // proceed
            return true;
        } );
    };

    /**
     * Remove component from grid
     *
     * @param el
     */
    this.removeComponent = function ( el ) {
        this.grid.remove_widget( el, true );
    };

    /**
     * The constructor
     *
     * @constructor
     */
    this.RP4WP_Configurator = function () {

        // set container
        jQuery( this.container ).closest( 'table' ).before( this.container );

        // Set configuration view
        this.configurator = jQuery( '<div>' ).addClass( 'configurator' );
        this.container.find( '.rp4wp-configurator' ).append( this.configurator );

        // Enable Gridstack
        this.grid = this.configurator.gridstack( {
            item_class: 'grid-stack-item',
            width: 2,
            height: 5,
            cell_height: 50,
            cell_width: 50,
            resizable: {
                handles: 'e, se, s, sw, w'
            }
        } ).data( 'gridstack' );

        // Load the config
        this.loadConfig();

        // Load components
        this.loadComponents();

        // Load presets
        this.loadPresets();

        // bind form
        this.bindForm();

    };

    // constructor
    this.RP4WP_Configurator();
};