
(function($){
	var scss="";
	var pmap={};
	var defaultMap = {
	  '$icon-color':		 '#ffffff',
	  '$text-color':		 '#ffffff',
	  '$base-color':		 '#23282d',
	  '$highlight-color': 	 '#0073aa',
	  '$notification-color': '#d54e21',
	  '$body-background':	 '#f1f1f1',
	  '$link':				 '#0073aa'
	  };


    var previewAdminColorSchemeTimeout;

	function delayedPreview() {
	  $('#preview_scheme').hide();
	  clearDelayedPreview();
	  previewAdminColorSchemeTimeout = window.setTimeout( sync_scheme_preview, 30 );
	}

	function clearDelayedPreview() {
	  window.clearTimeout( previewAdminColorSchemeTimeout );
	}

	function escapeRegExp(str) {
	    return str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
	}
	function replaceAll(str, find, replace) {
	  return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
	}

	function addAdminColorStylesheet(body){

        style = document.createElement('style');
        style.id = 'colors-css';
        style.type = 'text/css';
        
        body.appendChild(style);
        return style;
    }

	function sass_admin_colors( scss ){

	    SassWorker.compile( scss, function(result){
	       
	        var css = result.text,
	        body = document.body || document.getElementsByTagName('body')[0],
	        style = document.getElementById('colors-css');

	        if( style === null ) {
	          style = addAdminColorStylesheet(body);
	        } else {
	          document.getElementById('colors-css').innerHTML = null;
	        }

	        if (style.styleSheet){
	          style.styleSheet.cssText = css;
	        } else {
	          style.appendChild(document.createTextNode(css));
	        }

	    });
	    clearDelayedPreview();
	}
	function sync_svg_painter() {

		if ( typeof window.wp.svgPainter !== 'undefined' ) {
                
            var wpSvgPainter = window.wp.svgPainter;

            var accessor_map = {'menu_icon':'base','menu_highlight_icon':'focus','menu_current_icon':'current'};

            var colorScheme  = {"icons":{"base":"#0073aa","focus":"#ffffff","current":"#ffffff"}};
            // var colorScheme  = {"icons":{"base":"#82878c","focus":"#fff","current":"#fff"}};
           
			if ( typeof window._wpColorScheme !== 'undefined' ) {
				colorScheme = window._wpColorScheme;
			}
 
            $.each( accessor_map, function(i,o){
            	var $color_field = $('#admin_color_scheme_' + i );
            	if( typeof $color_field !== 'undefined' && $color_field.length > 0 && $color_field.val() !== "" ){
            		colorScheme.icons[o] = $color_field.val();
            	} 
            });
            if ( typeof window._wpColorScheme !== 'undefined' ) {
				window._wpColorScheme = colorScheme;
			}


            wpSvgPainter.init();
            wpSvgPainter.setColors( colorScheme );
            wpSvgPainter.paint();

	    }
	}
	function sync_scheme_preview(){

		var $pickers = jQuery('.of-color.of-scheme');

		pmap = $pickers.map( function($val){

			var handle = jQuery(this).attr('data-handle');
			var value  = jQuery(this).val();
			var defval = ( defaultMap[handle] === undefined ) ? null : defaultMap[handle];

			if( value === "" && defval === null ) { return; }

			return handle + ': ' + (( value === "" ) ? defval : value ) + ";";
		});
		scss = pmap.toArray().join("\n") + "\n\n\n@import '_admin.scss';\n";

		if ( $('#colors-css').filter('link').length == 1 ) {
			$('#colors-css').remove();
		} 

		sass_admin_colors( scss );

		sync_svg_painter();
	}


    $('.of-color.of-scheme').wpColorPicker({ change: delayedPreview, clear: delayedPreview });

	$('#clear_scheme').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();

		var accessor_map = {'menu_icon':'base','menu_highlight_icon':'focus','menu_current_icon':'current'};

        var colorScheme  = {"icons":{"base":"#0073aa","focus":"#ffffff","current":"#ffffff"}};
        // var colorScheme  = {"icons":{"base":"#82878c","focus":"#fff","current":"#fff"}};
       
		if ( typeof window._wpColorScheme !== 'undefined' ) {
			colorScheme = window._wpColorScheme;
		}

        $.each( accessor_map, function(i,o){
        	var $color_field = $('#admin_color_scheme_' + i );
        	if( typeof $color_field !== 'undefined' && $color_field.length > 0 && $color_field.val() !== "" ){
        		colorScheme.icons[o] = $color_field.val();
        	} 
        });
        if ( typeof window._wpColorScheme !== 'undefined' ) {
			window._wpColorScheme = colorScheme;
		}

		$('#section-admin_color_scheme .color-scheme-picker .wp-picker-clear').trigger('click');

		var $pickers = $('.of-color.of-scheme');

		$pickers.each( function($val){

			var handle = $(this).attr('data-handle');
			var defval = ( defaultMap[handle] === undefined ) ? null : defaultMap[handle];

			if( defval ) { $(this).wpColorPicker('color', defval ); }

		});

  //       if ( typeof window._wpColorScheme !== 'undefined' ) {
		// 	window._wpColorScheme = colorScheme;
		// }

	});
	$('#preview_scheme').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();

		// clear any existing messages
		$('h1').next('div').remove();

		var wp_http_referer = '&_wp_http_referer=' + $(this).parents('form').find('input[name="_wp_http_referer"]').val();

		var preview = $(this).parents('form').find('.color-scheme-pickers input').serialize() + wp_http_referer + '&action=admin-color-schemes-save';

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: preview,
			success: function(r) {
				if ( typeof r.errors != 'undefined' ) {
					$('h1').after( '<div class="error"><p>' + r.message + '</p></div>' );
				} else if ( typeof r.uri != 'undefined' ) {
					if ( $('#colors-css').length !== 1 ) {
						$('<link rel="stylesheet" id="colors-css" type="text/css" media="all"/>').appendTo('head');
					} 
					$('#colors-css').attr('href', r.uri);
					$('h1').after( '<div class="update-nag notice">' + r.message + '</div>' );
				}
				sync_svg_painter();
			}
		})
	});
	$('#optionsframework .section-scheme .button-group > button').on('click', function(e){

		e.preventDefault();
		e.stopPropagation();

		var $this = $(this);
		var $advanced = $this.closest('.section-scheme').find('.advanced-color-scheme-pickers');

		if($this.hasClass('active')){ return false; }

		$this.parent().find('.active').removeClass('active');
		$this.addClass('active');

		if($this.hasClass('show-advanced-scheme')){
		     $advanced.slideDown();
		} else if($this.hasClass('show-basic-scheme')){
		     $advanced.slideUp();
		}

	});
	$('.advanced-color-scheme-pickers.hide-if-js').hide().removeClass('hide-if-js');
})(jQuery);
