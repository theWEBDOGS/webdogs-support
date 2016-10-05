var scss="";
var pmap={};

(function($){

	var defaultMap = {
	  '$text-color':		 '#ffffff',
	  '$base-color':		 '#23282d',
	  '$highlight-color': 	 '#0073aa',
	  '$notification-color': '#d54e21',
	  '$body-background':	 '#f1f1f1',
	  '$link':				 '#0073aa'
	  };


    var previewAdminColorSchemeTimeout;

	function delayedPreview() {
	  $('#preview').hide();
	  clearDelayedPreview();
	  previewAdminColorSchemeTimeout = window.setTimeout( sync_scheme_preveiw, 30 );
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
        style.id = 'adminColor';
        style.type = 'text/css';
        
        body.appendChild(style);
        return style;
    }

	function sass_admin_colors( scss ){

	    SassWorker.compile( scss, function(result){
	       
	        var css = result.text,
	        body = document.body || document.getElementsByTagName('body')[0],
	        style = document.getElementById('adminColor');

	        if( style === null ) {
	          style = addAdminColorStylesheet(body);
	        } else {
	          document.getElementById('adminColor').innerHTML = null;
	        }

	        if (style.styleSheet){
	          style.styleSheet.cssText = css;
	        } else {
	          style.appendChild(document.createTextNode(css));
	        }

	    });
	    clearDelayedPreview();
	}

	function sync_scheme_preveiw(){

		var $pickers = jQuery('.of-color.of-scheme');

		pmap = $pickers.map( function($val){

			var handle = jQuery(this).attr('data-handle');
			var value  = jQuery(this).val();
			var defval = ( defaultMap[handle] === undefined ) ? null : defaultMap[handle];

			if( value === "" && defval === null ) { return; }

			return handle + ': ' + (( value === "" ) ? defval : value ) + ";";
		});
		scss = pmap.toArray().join("\n") + "\n\n\n@import '_admin.scss';\n";
		sass_admin_colors( scss );
	}


    $('.of-color.of-scheme').wpColorPicker({ change: delayedPreview });

	$('#preview').on('click', function(e){
		e.preventDefault();
		e.stopPropagation();

		// clear any existing messages
		$('h1').next('div').remove();

		var wp_http_referer = '&_wp_http_referer=' + $(this).parents('form').find('input[name="_wp_http_referer"]').val();

		var preview = $(this).parents('.color-scheme-pickers').find('input').serialize() + wp_http_referer + '&action=admin-color-schemes-save';

		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxurl,
			data: preview,
			success: function(r) {
				if ( typeof r.errors != 'undefined' ) {
					$('h1').after( '<div class="error"><p>' + r.message + '</p></div>' );
				} else if ( typeof r.uri != 'undefined' ) {
					$('<link rel="stylesheet" id="colors-css" type="text/css" media="all"/>').appendTo('head');
					$('#colors-css').attr('href', r.uri);
					$('h1').after( '<div class="webdogs-nag">' + r.message + '</div>' );
				}
			}
		})
	});
	$('#optionsframework .section-scheme > .button-group > button').on('click', function(e){

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