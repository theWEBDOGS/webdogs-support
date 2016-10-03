var optionsframework_upload,
	optionsframework_selector,
	optionsframework_adminbar;

jQuery(document).ready(function($){

	optionsframework_adminbar = $('#wpadminbar').clone()[0];

	function optionsframework_add_file( event, selector) {

		var upload = $(".uploaded-file"), frame;
		var $el = $(this);
		optionsframework_selector = selector;

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( optionsframework_upload ) {
			optionsframework_upload.open();
		} else {
			// Create the media frame.
			optionsframework_upload = wp.media.frames.optionsframework_upload =  wp.media({
				// Set the title of the modal.
				title: $el.data('choose'),

				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: $el.data('update'),
					// Tell the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			});

			// When an image is selected, run a callback.
			optionsframework_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = optionsframework_upload.state().get('selection').first();
				optionsframework_upload.close();
				optionsframework_selector.find('.upload').val(attachment.attributes.url);
				

				if ( attachment.attributes.type == 'image' ) {

					if( attachment.attributes.url.indexOf( '.svg' ) > -1 ) {
						
						var xml = "";
						var url = "";
						var svgObject = $('<object data="' + attachment.attributes.url + '" type="image/svg+xml" class="screenshot-svg" width="100%" height="100%"></object>')[0];
						svgObject.addEventListener(

							"load", function(){
		                
				                // get the inner DOM of alpha.svg
				                xml = new XMLSerializer().serializeToString( svgObject.contentDocument );
				                // console.log( xml );
								if ( 'btoa' in window ) {
									xml = window.btoa( xml );
								} else {
									xml = base64.btoa( xml );
								}
								url = 'data:image/svg+xml;base64,' + xml;

								// IF this is the section-logo_icon
								if ( optionsframework_selector.attr('id') === "section-logo_icon" ) {

									optionsframework_adminbar_show_preview( url );
								}
								// console.log(url);
								// console.log(xml,url,svgObject);
								
								optionsframework_show_preveiw( optionsframework_selector );
								// optionsframework_selector.find('.screenshot').empty().hide().append('<img src="' + url + '"><a class="remove-image">Remove</a>');
		            	});

						optionsframework_selector.find('.screenshot').empty().hide().append( $(svgObject) );


						optionsframework_selector.find('.screenshot').append( '<a class="remove-image">Remove</a>' );
						

					} else {
						optionsframework_selector.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url + '"><a class="remove-image">Remove</a>');

						// IF this is the section-logo_icon
						if ( optionsframework_selector.attr('id') === "section-logo_icon" ) {

							optionsframework_adminbar_show_preview( attachment.attributes.url );
						}
					}
					optionsframework_selector.find('.screenshot').appendTo( optionsframework_selector.closest('.option').slideDown('fast') ).slideDown('fast');
					optionsframework_selector.find('.screenshot').slideDown('fast');
				} else {
					optionsframework_show_preveiw( optionsframework_selector );
				}
				optionsframework_selector.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(optionsframework_l10n.remove);
				optionsframework_selector.find('.of-background-properties').slideDown();
				optionsframework_selector.find('.remove-image, .remove-file').on('click', function() {
					optionsframework_remove_file( $(this).parents('.section') );
				});
			});

		}

		// Finally, open the modal.
		optionsframework_upload.open();
	}

	function optionsframework_show_preveiw(selector) {

		var screenshot_background = {
			              "opacity": "1",
			     "background-image": "url(" + selector.find('.upload:input').val() + ")",
			     "background-color": selector.find('.of-background-color:input').val(),
		        "background-repeat": selector.find('.of-background-repeat:input').val(),
		      "background-position": selector.find('.of-background-position:input').val(),
		    "background-attachment": selector.find('.of-background-attachment:input').val()
		};
		selector.find('#login_logo_css-image_wrap').css( "background-color", screenshot_background["background-color"] );
		selector.find('.screenshot').css( screenshot_background ).slideDown().find('img').css({'opacity':"0"});
	}

	function optionsframework_adminbar_show_preview( url ) {
			
        $preview_element = $( optionsframework_adminbar ).find('#wp-admin-bar-site-name .ab-icon.svg').attr( 'style', 'background-image: url("' + url + '") !important;' );

        // get the inner element by id
		wp.svgPainter.init();

	}


	function optionsframework_clone_adminbar(){

		// adminbar preview clone. 
		$(optionsframework_adminbar).remove();

		optionsframework_adminbar = $('#wpadminbar').not('.adminbar-preview').clone()[0];
		$(optionsframework_adminbar).appendTo('#section-logo_icon').css({position:'relative'});
		$(optionsframework_adminbar).addClass('adminbar-preview')

			.on('click', function(e){e.preventDefault();e.stopPropagation();return false;})
			.css({position:'static',overflow:'hidden'})
			
			// remove all menus not #wp-admin-bar-site-name
			.find('#wp-admin-bar-root-default > *, #wp-admin-bar-top-secondary')
				.not('#wp-admin-bar-site-name')
				.remove();
	}
	/*
	if( screenshot_background["background-repeat"] === "no-repeat" ) {
		$('body').attr('style', null );
		// $('body').css( {'background-color': screenshot_background['background-color'] } );
		selector.find('.screenshot').css( screenshot_background ).slideDown().find('img').css({'opacity':"0"});
	} else {
		// $('body').css( screenshot_background );
		selector.find('.screenshot').css({'opacity':"0"});
	} */	
	function optionsframework_remove_file(selector) {
		selector.find('.remove-image').hide();
		selector.find('.upload').val('');
		selector.find('.of-background-properties').hide();
		selector.find('.screenshot').slideUp();
		selector.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(optionsframework_l10n.upload);
		
		// We don't display the upload button if .upload-notice is present
		// This means the user doesn't have the WordPress 3.5 Media Library Support
		if ( $('.section-upload .upload-notice').length > 0 ) {
			$('.upload-button').remove();
		}
		if ( selector.attr('id') === "section-logo_icon" ) {
			optionsframework_clone_adminbar();
		}
		selector.find('.upload-button').on('click', function(event) {
			optionsframework_add_file(event, $(this).parents('.section'));
		});
	}

	$('.remove-image, .remove-file').on('click', function() {
		optionsframework_remove_file( $(this).parents('.section') );
    });

    $('.upload-button').click( function( event ) {
    	optionsframework_add_file(event, $(this).parents('.section'));
    });

    $('.of-background:input').on('change', function() {
		optionsframework_show_preveiw( $(this).parents('.section') );
	});

    $('.of-background-color').wpColorPicker({
    	change: function(){
    		optionsframework_show_preveiw( $(this).parents('.section') );
    	}
    });
	
	$('.section-background .has-file:input').each( function() {
		optionsframework_show_preveiw( $(this).parents('.section') );
	});
	
	$('#login_logo_css-image').wrap('<div id="login_logo_css-image_wrap">');
	$('#login_logo_css-image_wrap').append('<div id="login_logo_css-login_facade">').prependTo( $('#login_logo_css-image_wrap').parent() );
	$('#login_logo_css_repeat, #login_logo_css_attachment, #login_logo_css_position').find('option').attr('disabled', 'disabled' ).closest('.of-background-properties').hide();

	$('.section-upload .has-file:input').each( function() {

		if ( $(this).parents('.section').attr('id') === "section-logo_icon" ) {
			optionsframework_clone_adminbar();
		}
	});

});