var optionsframework_upload,
	optionsframework_upload_accept = "image/*",
	optionsframework_selector,
	optionsframework_adminbar;

jQuery(document).ready(function($){

	optionsframework_adminbar = $('#wpadminbar').clone()[0];

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

	function optionsframework_add_file( e, selector) {

		var  upload = $(".uploaded-file"), frame; 

		var	 uploadElement = false,
			$uploadElement = false;

		var $el = $( event.target );
		optionsframework_selector = selector;

		event.preventDefault();

		optionsframework_upload = null;
		// If the media frame already exists, reopen it.
		// if ( optionsframework_upload ) {

			/*optionsframework_upload.on( 'open', function(ev){

		    	var $ofulel = optionsframework_upload.$el;

				$ofulel.one( 'mouseover', function(event){

				    if( ! $uploadElement.length ) {
						$uploadElement = $ofulel.find(':file');
						
						if( $uploadElement.length ) {
							uploadElement = $uploadElement.get(0);
						}
					}

					if($uploadElement.length){
						
						if( optionsframework_selector.attr('id') === "section-logo_icon") {
							uploadElement.accept = 'image/svg+xml,.svg,.svgz';
						} else {
							uploadElement.accept = 'image/*';
						}
						console.log( 'new uploadElement.accept', uploadElement.accept );
					}
				});
			});

			optionsframework_upload.open();*/

		// } else {

			var mediaOptions = {
				// Set the title of the modal.
				title: $el.data('choose'),

				// Customize the submit button.
				button: {
					text: $el.data('update'),
					close: false
				}
			};

			if( optionsframework_selector.attr('id') === "section-logo_icon" ){
				mediaOptions.library = { type : 'image/svg+xml' }; 
			} else {
				mediaOptions.library = { type : 'image' }; 
			} 

			// Create the media frame.
			optionsframework_upload = wp.media.frames.optionsframework_upload =  wp.media( mediaOptions );

			// When an image is selected, run a callback.
			optionsframework_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = optionsframework_upload.state().get('selection').first();

				optionsframework_upload.close();

				optionsframework_selector.find('.upload').val( attachment.attributes.url ).trigger('change');
				

				if ( attachment.attributes.type == 'image' ) {

					/*if( attachment.attributes.url.indexOf( '.svg' ) > -1 ) {
						
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

																
								optionsframework_show_preveiw( optionsframework_selector );
								// optionsframework_selector.find('.screenshot').empty().hide().append('<img src="' + url + '"><a class="remove-image">Remove</a>');
		            	});

						optionsframework_selector.find('.screenshot').empty().hide().append( $(svgObject) );


						optionsframework_selector.find('.screenshot').append( '<a class="remove-image">Remove</a>' );
						

					} else {*/
						optionsframework_selector.find('.screenshot').empty().hide().append('<img src="' + attachment.attributes.url + '"><a class="remove-image">Remove</a>');

						// IF this is the section-logo_icon
						if ( optionsframework_selector.attr('id') === "section-logo_icon" ) {
							optionsframework_adminbar_show_preview( attachment.attributes.url );
						} else {

							optionsframework_show_preveiw( optionsframework_selector );
						}
					// }
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
			optionsframework_upload.on( 'open', function(ev){

		    	var $optionsframework_upload = optionsframework_upload.$el;

				$optionsframework_upload.one( 'mouseover', function(event){

				    if( ! $uploadElement.length ) {

						$uploadElement = $optionsframework_upload.find(':file');
						
						if( $uploadElement.length ) {
							uploadElement = $uploadElement.get(0);
						}
					}

					if($uploadElement.length){
						
						if( optionsframework_selector.attr('id') === "section-logo_icon") {
							uploadElement.accept = 'image/svg+xml,.svg,.svgz';
						} else {
							uploadElement.accept = 'image/*';
						}
						// console.log( 'new uploadElement.accept', uploadElement.accept );
					}
				});
			});

			// Finally, open the modal.
			optionsframework_upload.open();
		    
		// }

	}

	

	function optionsframework_show_preveiw(selector) {

		var screenshot_background = {
			              "opacity": "1",
			     "background-image": "url(" + selector.find('.upload:input').val() + ")",
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

	function optionsframework_adminbar_show_preview( url ) {

		optionsframework_clone_adminbar();
		/*	
        $( optionsframework_adminbar ).find('#wp-admin-bar-site-name > a.ab-item .ab-icon.svg').remove();
		$( optionsframework_adminbar ).find('#wp-admin-bar-site-name > a.ab-item').prepend('<span class="ab-item ab-icon svg" />');
    	$( optionsframework_adminbar ).find('#wp-admin-bar-site-name > a.ab-item .ab-icon.svg').attr( 'style', 'background-image: url("' + url + '") !important;' );

        // get the inner element by id
/*
        if ( typeof window.wp.svgPainter !== 'undefined' ) {
                
                wpSvgPainter = window.wp.svgPainter;*/
/*
                var accessor_map = {'icon_color':'base','menu_icon':'base','highlight_color':'focus','menu_highlight_icon':'focus','menu_current_icon':'current'};

                var colorScheme  = {"icons":{"base":"#82878c","focus":"#00a0d2","current":"#fff"}};
                
                $.each( accessor_map, function(i,o){
                	var $color_field = $('#admin_color_scheme_' + i );
                	if( typeof $color_field !== 'undefined' && $color_field.length > 0 && $color_field.val() !== "" ){
                		colorScheme.icons[o] = $color_field.val();
                	} 
                });

                console.log(colorScheme);*/
                // wpSvgPainter.init();
                /*wpSvgPainter.setColors( colorScheme );
                wpSvgPainter.paint();*/

            // }

	}


	function optionsframework_clone_adminbar(){

		// adminbar preview clone. 
		$(optionsframework_adminbar).remove();

		optionsframework_adminbar = $('#wpadminbar').not('.adminbar-preview').clone()[0];
		$( optionsframework_adminbar ).prependTo('#section-login_logo_css').css({position:'relative'});
		$( optionsframework_adminbar ).addClass('adminbar-preview')

			.on('click', function(e){e.preventDefault();e.stopPropagation();return false;})
			.css({position:'static',overflow:'hidden'})
			
				// remove all menus not #wp-admin-bar-site-name
				.find('#wp-admin-bar-root-default > *, #wp-admin-bar-top-secondary')
					.not('#wp-admin-bar-site-name')
					.remove();

		 /*$( optionsframework_adminbar ).find('#wp-admin-bar-site-name > a.ab-item .ab-icon.svg').remove();
		 $( optionsframework_adminbar ).find('#wp-admin-bar-site-name > a.ab-item').prepend('<span class="ab-item ab-icon svg" />');*/
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
		var buttomFileType = "";
		if ( selector.attr('id') === "section-logo_icon" ) {
			buttomFileType = " SVG";
		}
		selector.find('.remove-file').unbind().addClass('upload-button').removeClass('remove-file').val(optionsframework_l10n.upload + buttomFileType);
		
		// We don't display the upload button if .upload-notice is present
		// This means the user doesn't have the WordPress 3.5 Media Library Support
		if ( $('.section-upload .upload-notice').length > 0 ) {
			$('.upload-button').remove();
		}
		if ( selector.attr('id') === "section-logo_icon" ) {
			optionsframework_clone_adminbar();
			$("#logo_icon_css").val('');
			$("#logo_icon_style").html('');
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
	$('#login_logo_css-image').attr({"data-parallaxify-range-y":"120","data-parallaxify-range-x":"120"}).wrap('<div id="login_logo_css-image_wrap" class="login">');
	$('#login_logo_css-image_wrap').hide().append('<div id="login_logo_css-login_facade" data-parallaxify-range-y="140" data-parallaxify-range-x="140"><p><label for="user_login">Username or Email<br><input type="text" id="faux_user_login" class="input" value="" size="20" disabled="disabled"></label></p></div>').prependTo( $('#section-login_logo_css') );
	$('#login_logo_css_repeat, #login_logo_css_attachment').find('option').not(':selected').remove();
	// , #login_logo_css_position
	clearDelayedPreview();
	previewLoginColorTimeout = window.setTimeout( function(){
	    var backgroundColor = $('#section-login_logo_css').find('.of-background-color:input').val();
		$('#section-login_logo_css').find('#login_logo_css-image_wrap').css( "background-color", backgroundColor )/*.parallaxify(parallaxifyArgs)*//*.hover(
		    function(){
		        $(this).parallaxify(parallaxifyArgs);
		    },
		    function(){
		        $(this).parallaxify('destroy');
		    }
		)*/.slideDown();


			$('.section-upload .has-file:input').each( function() {

				if ( $(this).parents('.section').attr('id') === "section-logo_icon" ) {
					optionsframework_clone_adminbar();
				}
			});

		
	}, 30 );

/*
	var xml = "";
	var url = "";
	var svgObject = $('#section-logo_icon object.screenshot-svg')[0];
	
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

			optionsframework_adminbar_show_preview( url );

	});*/
});
