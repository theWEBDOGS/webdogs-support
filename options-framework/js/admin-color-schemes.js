(function($){

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
