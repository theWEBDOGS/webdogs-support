var wds = window.wds?window.wds:{};

/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {

	// Loads the color pickers
	$('.of-color').wpColorPicker();

	// Image Options
	$('.of-radio-img-img').on('click',function(){
		$(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
		$(this).addClass('of-radio-img-selected');
	});

	$('.of-radio-img-img').show();
	$('.of-radio-img-label').hide();
	$('.of-radio-img-radio').hide();

	// Loads tabbed sections if they exist
	if ( $('.nav-tab-wrapper').length > 0 ) {
		wds.tabs();
	}

	// calculate next maintenance notification
	if ( $('#section-maintenance_notification_frequency').length > 0 ) {

		var $top_scheduled = $('.wds_notification_events > .wds_notification_scheduled').clone().addClass('bar_sep_before').appendTo( $("<span class='wds_notification_events'></span>") ).parent();

		$('#section-maintenance_notification_frequency').parents('.group').first().find('> h3').append( $top_scheduled );

		$('.wds_notification_events > .wds_notification_scheduled').text( wds.get_next_schedule() );

		$('#section-maintenance_notification_frequency :input, #section-maintenance_notification_offset :input').on('change', function(){
			$('.wds_notification_events > .wds_notification_scheduled').text( wds.get_next_schedule() );
		});
	}

});