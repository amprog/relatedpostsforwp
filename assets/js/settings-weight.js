jQuery(function ($) {

    navigator.sayswho = (function () {
        var ua = navigator.userAgent, tem,
            M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
        if (/trident/i.test(M[1])) {
            tem = /\brv[ :]+(\d+)/g.exec(ua) || [];
            return 'IE ' + (tem[1] || '');
        }
        if (M[1] === 'Chrome') {
            tem = ua.match(/\bOPR\/(\d+)/)
            if (tem != null) return 'Opera ' + tem[1];
        }
        M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];
        if ((tem = ua.match(/version\/(\d+)/i)) != null) M.splice(1, 1, tem[1]);
        return M[0];
    })();

    $('.rp4wp-weight-slider').addClass(navigator.sayswho.toLowerCase());

   $('.rp4wp-weight-slider input').on('change',function() {
      $(this).closest('div').find('p').html($(this).val());
   });

});

function RP4WP_Weight_Reset() {
	var defaults = [80, 20, 20, 10, 15];
	var sliders = jQuery( '.rp4wp-weight-slider' );

	for ( var i = 0; i < defaults.length; i ++ ) {

		if( sliders[ i ] != undefined ) {
			jQuery( sliders[ i ] ).find('input').val( defaults [ i ] ).trigger( 'change' );
		}

	}
}