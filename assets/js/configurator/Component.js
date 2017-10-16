var RP4WP_Component = function ( type, x, y, w, h, custom ) {

    // DOM element
    this.el = null;

    // the widget type
    this.type = type;

    // the widget text
    this.text = null;

    // position x
    this.x = 0;

    // position y
    this.y = 0;

    // width
    this.w = 2;

    // height
    this.h = 1;

    // custom
    this.custom = null;

    // remove callback
    this.removeCB = null;

    this.promptObj = null;

    /**
     * constructor
     *
     * @constructor
     */
    this.RP4WP_Widget = function () {

        var instance = this;

        if ( undefined != y ) {
            this.y = y;
        }

        if ( undefined != x ) {
            this.x = x;
        }

        if ( undefined != w ) {
            this.w = w;
        }

        if ( undefined != h ) {
            this.h = h;
        }

        if ( undefined != custom ) {
            this.custom = custom;
        }

        switch ( this.type ) {
            case 'title':
                this.text = 'Post Title';
                break;
            case 'image':
                this.text = 'Post Image';
                break;
            case 'excerpt':
                this.text = 'Post Excerpt';
                break;
            case 'author':
                this.text = 'Post Author';
		        break;
	        case 'date':
		        this.text = 'Post Date';
                break;
            case 'wcprice':
                this.text = 'WooCommerce Price';
                break;
	        case 'taxonomy':
		        this.text = 'Post Taxonomy';
		        this.promptObj = {
			        title: 'What Taxonomy would you like to add?',
			        text: 'Enter The Taxonomy:',
			        cb: function ( input_val ) {
				        instance.text = 'Taxonomy: ' + input_val;
				        instance.custom = input_val;
			        }
		        };
		        break;
	        case 'readmore':
		        this.text = 'Read More Link';
		        this.promptObj = {
			        title: 'What text should the link be?',
			        text: 'Enter The Text:',
			        cb: function ( input_val ) {
				        instance.text = 'Read More Link: ' + input_val;
				        instance.custom = input_val;
			        }
		        };
		        break;
            case 'custom':
                this.text = 'Custom Text';

                this.promptObj = {
                    title: 'Enter Custom Text',
                    text: 'Enter The Custom Text:',
                    cb: function ( input_val ) {
                        instance.text = 'Custom Text: ' + input_val;
                        instance.custom = input_val;
                    }
                };

                break;
            case 'meta':
                this.text = 'Post Meta';

                this.promptObj = {
                    title: 'What Post Meta should be displayed?',
                    text: 'Enter The Post Meta Key:',
                    cb: function ( input_val ) {
                        instance.text = 'Custom Meta: ' + input_val;
                        instance.custom = input_val;
                    }
                };

                break;
        }

        // Add custom to text
        if ( null != this.custom ) {
            this.text += ': ' + this.custom;
        }

    };

    // call constructor
    this.RP4WP_Widget();

}

RP4WP_Component.prototype.getY = function () {
    return this.y;
};

RP4WP_Component.prototype.getX = function () {
    return this.x;
};

RP4WP_Component.prototype.getW = function () {
    return this.w;
};

RP4WP_Component.prototype.getH = function () {
    return this.h;
};

RP4WP_Component.prototype.promptData = function () {

    // no need to prompt is there's no prompt object
    if( null == this.promptObj ) {
        return;
    }

    var instance = this;
    cSwal( {
        title: this.promptObj.title,
        text: this.promptObj.text,
        inputValue: '',
        type: "input",
        showCancelButton: true,
        closeOnConfirm: false,
        animation: "slide-from-top",
	    inputPlaceholder : 'test'
    }, function ( input_val ) {

        // must be set
        if ( input_val === false ) {
            instance.removeEl();
            return false;
        }

        // can't be empty
        if ( '' === input_val ) {
            cSwal.showInputError( "This field can't be empty." );
            return false
        } else {
            instance.custom = input_val;
            if ( undefined != instance.promptObj.cb ) {
                instance.promptObj.cb( input_val );
            }
            instance.updateEl();
            cSwal( "Perfection!", "Component set!", "success" );
        }

    } );

    if ( null != this.custom ) {
        jQuery( '.sweet-alert input[type=text]:first' ).val( this.custom );
    }
};

RP4WP_Component.prototype.getEl = function () {

    if ( null == this.el ) {
        this.el = this.generateEl();
    }

    return this.el;
};

RP4WP_Component.prototype.generateEl = function () {

    // instance
    var instance = this;

    // element
    var el = jQuery( '<div>' );

    // content element
    var content = jQuery( '<div>' ).addClass( 'grid-stack-item-content' ).data( 'type', this.type );

    // set custom if not null
    if ( null != this.custom ) {
        content.data( 'custom', this.custom );
    }

    // add content to el
    el.append( content );

    // title
    var title = jQuery( '<div>' ).append( jQuery( '<h4>' ).html( this.text ) );
    content.append( title );

    // close button
    var clsbtn = jQuery( '<a>' ).addClass( 'clsbtn' ).click( function () {
        instance.removeEl();
    } );
    content.append( clsbtn );

    if ( 'custom' === this.type || 'meta' === this.type || 'taxonomy' == this.type || 'readmore' == this.type ) {
        el.dblclick( function () {
            instance.promptData();
        } );
    }


    return el;
};

RP4WP_Component.prototype.updateEl = function () {
    if ( null != this.el ) {
        this.el.children().first().replaceWith( this.generateEl().children().first() ); // because of this the main element data is not set
    }
};

RP4WP_Component.prototype.removeEl = function () {
    if ( null != this.el ) {
        this.removeCB( this.el );
    }
};

RP4WP_Component.prototype.setRemoveCB = function ( removeCB ) {
    this.removeCB = removeCB;
}