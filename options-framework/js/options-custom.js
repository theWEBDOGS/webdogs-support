/**
 * Custom scripts needed for the colorpicker, image button selectors,
 * and navigation tabs.
 */

jQuery(document).ready(function($) {

	// Loads the color pickers
	$('.of-color').wpColorPicker();

	// Image Options
	$('.of-radio-img-img').click(function(){
		$(this).parent().parent().find('.of-radio-img-img').removeClass('of-radio-img-selected');
		$(this).addClass('of-radio-img-selected');
	});

	$('.of-radio-img-img').show();
	$('.of-radio-img-label').hide();
	$('.of-radio-img-radio').hide();

	// Loads tabbed sections if they exist
	if ( $('.nav-tab-wrapper').length > 0 ) {
		options_framework_tabs();
	}

	// calculate next maintenance notification
	if ( $('#section-maintenance_notification_frequency').length > 0 ) {

		$('.wd_notification_events > .wd_notification_scheduled').text( get_next_schedule() );

		$('#section-maintenance_notification_frequency :input, #section-maintenance_notification_offset :input').on('change', function(){
			$('.wd_notification_events > .wd_notification_scheduled').text( get_next_schedule() );
		});
	}



	function get_active_dates( freq, day, month, year ) {

		var date = Date.parse( String( new Date() ) );
	 	var active = [];
		var n = 0;
	 	for (var i = month; i <= 12; i++) {

	 		parsed = Date.parse( i+"-"+day+"-"+year  );

	 		if( i % freq === 0 && parsed > date )	{
	 			var m = ( i < 12 ) ? i+1 : 1 ;
	 			var y = ( i < 12 ) ? year : year + 1 ;


	 			parsed = ( freq > 1 ) ? Date.parse( m+"-"+day+"-"+y ) : parsed;

	 			active[n] = parsed;
	 			n++;
		 	}

	 	}
	 	return active;
	}

	function get_next_schedule(){
		var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

		var date = new Date();
		var freq = Number( $('#section-maintenance_notification_frequency :checked').val() ),
	      offset = Number( $('#section-maintenance_notification_offset :selected').val() ),
	        year = date.getFullYear(),
	       month = date.getMonth()+1,
	         day = date.getDate();


	    var prev_sent = ( $('.wd_notification_events > .wd_last_notification_sent').length > 0 ) ? new Date( Number( $('.wd_notification_events > .wd_last_notification_sent').attr('data-prev-notice') )*1000 ) : new Date(0);
		var next_send = "";
		var next_date = "";

	 	if ( String( prev_sent ) === String( new Date(0) )  ) {
			prev_sent = date;
		 	next_send = "Next notification: ";
		} else {
		 	next_send = "| Next notification: ";
		}

	 	var prev_month = prev_sent.getMonth()+1;
	 	var prev_year = prev_sent.getFullYear();
	 	
	 	
	 	month = ( year === prev_year && month === prev_month && day >= offset ) ? ++month : month ;

	 	var active_this_year = get_active_dates( freq, offset, month, year   );
	 	var active_next_year = get_active_dates( freq, offset, 1    , 1 + year );

	 	// console.log(active_this_year, Date.parse( String( date ) ));

	 	if( active_this_year.length > 0 ) {
	 		next_date = new Date( active_this_year[0] );
	 	} else if( active_next_year.length > 0 ){
	 		next_date = new Date( active_next_year[0] );
	 	}

	 	return next_send + monthNames[next_date.getMonth()] + ' ' + next_date.getDate() + ', ' + next_date.getFullYear();

	}

	function options_framework_tabs() {

		var $group = $('.group'),
			$navtabs = $('.nav-tab-wrapper a'),
			active_tab = '';

		// Hides all the .group sections to start
		$group.hide();

		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			active_tab = localStorage.getItem('active_tab');
		}

		// If active tab is saved and exists, load it's .group
		if ( active_tab != '' && $(active_tab).length ) {
			$(active_tab).fadeIn();
			$(active_tab + '-tab').addClass('nav-tab-active');
		} else {
			$('.group:first').fadeIn();
			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
		}

		// Bind tabs clicks
		$navtabs.click(function(e) {

			e.preventDefault();

			// Remove active class from all tabs
			$navtabs.removeClass('nav-tab-active');

			$(this).addClass('nav-tab-active').blur();

			if (typeof(localStorage) != 'undefined' ) {
				localStorage.setItem('active_tab', $(this).attr('href') );
			}

			var selected = $(this).attr('href');

			$group.hide();
			$(selected).fadeIn();

		});
	}

});