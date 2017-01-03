var wds = window.wds?window.wds:{};

wds.upload,
wds.upload_accept = "image/*",
wds.upload_selector,
wds.adminbar;


wds.current_schema = window.location.protocol + '//'

jQuery(document).ready(function($){

	wds.adminbar = $('#wpadminbar').clone()[0];

	var previewLoginColorTimeout;
	var parallaxifyArgs = {
			positionProperty: 'transform',
			responsive: true,
			motionType: 'natural',
			mouseMotionType: 'linear',
			motionAngleX: 80,
			motionAngleY: 80,
			alphaFilter: 0.5,
			adjustBasePosition: true,
			alphaPosition: 0.025
		};

	function addEvent (o, e, f) {
		if (window.addEventListener) o.addEventListener(e, f, false);
		else if (window.attachEvent) r = o.attachEvent('on' + e, f);
	}
	function clearDelayedPreview() {
	  window.clearTimeout( previewLoginColorTimeout );
	}

	function wds_add_file( e, selector) {

		var  upload = $(".uploaded-file"), frame; 

		var	 uploadElement = false,
			$uploadElement = false;

		var $el = $( event.target );
		wds.upload_selector = selector;

		event.preventDefault();

		wds.upload = null;

			var mediaOptions = {
				// Set the title of the modal.
				title: $el.data('choose'),

				// Customize the submit button.
				button: {
					text: $el.data('update'),
					close: false
				}
			};

			if( wds.upload_selector.attr('id') === "section-logo_icon" ){
				mediaOptions.library = { type : 'image/svg+xml' }; 
			} else {
				mediaOptions.library = { type : 'image' }; 
			} 

			// Create the media frame.
			wds.upload = wp.media.frames.wds_upload =  wp.media( mediaOptions );

			// When an image is selected, run a callback.
			wds.upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = wds.upload.state().get('selection').first();

				wds.upload.close();

				wds.upload_selector.find('.upload').val( String( attachment.attributes.url ).replace("http://", wds.current_schema ) ).trigger('change');
				

				if ( attachment.attributes.type == 'image' ) {

					wds.upload_selector.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url.replace("http://", wds.current_schema ) + '"><a class="remove-image">Remove</a>');

					// IF this is the section-logo_icon
					if ( wds.upload_selector.attr('id') === "section-logo_icon" ) {
						wds_adminbar_show_preview( attachment.attributes.url.replace("http://", wds.current_schema ) );
					} else {

						wds_show_preveiw( wds.upload_selector );
					}

					wds.upload_selector.find('.screenshot').appendTo( wds.upload_selector.closest('.option').slideDown('fast') ).slideDown('fast');
					wds.upload_selector.find('.screenshot').slideDown('fast');
				} else {
					wds_show_preveiw( wds.upload_selector );
				}
				wds.upload_selector.find('.upload-button').unbind().addClass('remove-file').removeClass('upload-button').val(wds.l10n['remove']);
				wds.upload_selector.find('.of-background-properties').slideDown();
				wds.upload_selector.find('.remove-image, .remove-file').on('click', function() {
					wds_remove_file( $(this).parents('.section') );
				});


			});
			wds.upload.on( 'open', function(ev){

		    	var $wds_upload = wds.upload.$el;

				$wds_upload.one( 'mouseover', function(event){

				    if( ! $uploadElement.length ) {

						$uploadElement = $wds_upload.find(':file');
						
						if( $uploadElement.length ) {
							uploadElement = $uploadElement.get(0);
						}
					}

					if($uploadElement.length){
						
						if( wds.upload_selector.attr('id') === "section-logo_icon") {
							uploadElement.accept = 'image/svg+xml,.svg,.svgz';
						} else {
							uploadElement.accept = 'image/*';
						}
						// console.log( 'new uploadElement.accept', uploadElement.accept );
					}
				});
			});

			// Finally, open the modal.
			wds.upload.open();
		    

	}

	

	function wds_show_preveiw(selector) {

		var screenshot_background = {
			              "opacity": "1",
			     "background-image": "url(" + String( selector.find('.upload:input').val() ).replace("http://", wds.current_schema ) + ")",
		        "background-repeat": selector.find('.of-background-repeat:input').val(),
		      "background-position": selector.find('.of-background-position:input').val(),
		    "background-attachment": selector.find('.of-background-attachment:input').val()
		};
		
	  	clearDelayedPreview();
		previewLoginColorTimeout = window.setTimeout( function(){
		    var backgroundColor = $('#section-login_logo_css').find('.of-background-color:input').val();
			$('#section-login_logo_css').find('#login_logo_css-image_wrap').css( "background-color", backgroundColor ).slideDown();
			
		}, 30 );
		selector.find('.screenshot').css( screenshot_background ).slideDown().find('img,object').css({'opacity':"0"});

	}

	function wds_adminbar_show_preview( url ) {

		wds_clone_adminbar();

	}


	function wds_clone_adminbar(){

		// adminbar preview clone. 
		$(wds.adminbar).remove();

		$default_class = ( $('#logo_icon[value*="wordpress-logo.svg"]').length > 0 ) ? 'adminbar-preview default' : 'adminbar-preview';

		wds.adminbar = $('#wpadminbar').not('.adminbar-preview').clone()[0];
		$( wds.adminbar ).prependTo('#section-login_logo_css').css({position:'relative'});
		$( wds.adminbar ).addClass($default_class)

			.on('click', function(e){e.preventDefault();e.stopPropagation();return false;})
			.css({position:'static',overflow:'hidden'})
			
				// remove all menus not #wp-admin-bar-site-name
				.find('#wp-admin-bar-root-default > *, #wp-admin-bar-top-secondary')
					.not('#wp-admin-bar-site-name')
					.remove();
	}
	function wds_remove_file(selector) {
		selector.find('.remove-image').hide();
		selector.find('.upload').val('');
		selector.find('.of-background-properties').hide();
		selector.find('.screenshot').slideUp();
		var buttomFileType = "";
		if ( selector.attr('id') === "section-logo_icon" ) {
			buttomFileType = " SVG";
		}
		selector.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(wds.l10n['upload'] + buttomFileType);
		
		// We don't display the upload button if .upload-notice is present
		// This means the user doesn't have the WordPress 3.5 Media Library Support
		if ( $('.section-upload .upload-notice').length > 0 ) {
			$('.upload-button').remove();
		}
		if ( selector.attr('id') === "section-logo_icon" ) {
			wds_clone_adminbar();
			$('.adminbar-preview.default').removeClass('default');
			$("#logo_icon_css").val('');
			$("#logo_icon_style").html('');
			$('#logo-icon-css').removeAttr('href');
		}
		selector.find('.upload-button').on('click', function(event) {
			wds_add_file(event, $(this).parents('.section'));
		});
	}

	$('.remove-image, .remove-file').on('click', function() {
		wds_remove_file( $(this).parents('.section') );
    });

    $('.upload-button').click( function( event ) {
    	wds_add_file(event, $(this).parents('.section'));
    });

    $('.of-background:input').on('change', function() {
		wds_show_preveiw( $(this).parents('.section') );
	});

    $('.of-background-color').wpColorPicker({
    	change: function(){
    		wds_show_preveiw( $(this).parents('.section') );
    	}
    });
	
	$('.section-background .has-file:input').each( function() {
		wds_show_preveiw( $(this).parents('.section') );
	});
	$('#login_logo_css-image').attr({"data-parallaxify-range-y":"120","data-parallaxify-range-x":"120"}).wrap('<div id="login_logo_css-image_wrap" class="login">');
	$('#login_logo_css-image_wrap').hide().append('<div id="login_logo_css-login_facade" data-parallaxify-range-y="140" data-parallaxify-range-x="140"><p><label for="user_login">Username or Email<br><input type="text" id="faux_user_login" class="input" value="" size="20" disabled="disabled"></label></p></div>').prependTo( $('#section-login_logo_css') );
	$('#login_logo_css_repeat, #login_logo_css_attachment').find('option').not(':selected').remove();
	// , #login_logo_css_position
	clearDelayedPreview();
	previewLoginColorTimeout = window.setTimeout( function(){
	    var backgroundColor = $('#section-login_logo_css').find('.of-background-color:input').val();
		$('#section-login_logo_css').find('#login_logo_css-image_wrap').css( "background-color", backgroundColor ).slideDown();


			$('.section-upload .has-file:input').each( function() {

				if ( $(this).parents('.section').attr('id') === "section-logo_icon" ) {
					wds_clone_adminbar();
				}
			});

		
	}, 30 );
});
