var wds = window.wds?window.wds:{};

(function( $ ) {
	'use strict';

	wds.get_url_param = function( sParam ) {

	    var sPageURL = decodeURIComponent( window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;

	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');

	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? false : sParameterName[1];
	        }
	    }
	}


	wds.get_active_dates = function( freq, day, month, year ) {

		var date = Date.parse( String( new Date() ) );
	 	var active = [];
		var n = 0;
	 	for (var i = month; i <= 12; i++) {

	 		var parsed = Date.parse( i+"-"+day+"-"+year  );

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

	wds.get_next_schedule = function(sep=''){
		// todo l10n
		var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

		var date = new Date();
		var freq = Number( $('#section-maintenance_notification_frequency :checked').val() ),
	      offset = Number( $('#section-maintenance_notification_offset :selected').val() ),
	        year = date.getFullYear(),
	       month = date.getMonth()+1,
	         day = date.getDate();


	    var prev_sent = ( $('.wds_notification_events > .wds_last_notification_sent').length > 0 ) ? new Date( Number( $('.wds_notification_events > .wds_last_notification_sent').attr('data-prev-notice') )*1000 ) : new Date(0);
		var next_send = "";
		var next_date = "";

	    //	if ( String( prev_sent ) === String( new Date(0) )  ) {
		// 	prev_sent = date;
		//  	next_send = wds.l10n['next_notification'] + ": ";
		// } else {
		 	next_send = /*"| " +*/ wds.l10n['next_notification'] + ": ";
		// }

	 	var prev_month = prev_sent.getMonth()+1;
	 	var prev_year = prev_sent.getFullYear();
	 	
	 	
	 	month = ( year === prev_year && month === prev_month && day >= offset ) ? ++month : month ;

	 	var active_this_year = wds.get_active_dates( freq, offset, month, year   );
	 	var active_next_year = wds.get_active_dates( freq, offset, 1    , 1 + year );

	 	// console.log(active_this_year, Date.parse( String( date ) ));

	 	if( active_this_year.length > 0 ) {
	 		next_date = new Date( active_this_year[0] );
	 	} else if( active_next_year.length > 0 ){
	 		next_date = new Date( active_next_year[0] );
	 	}

	 	return next_send + monthNames[next_date.getMonth()] + ' ' + next_date.getDate() + ', ' + next_date.getFullYear();

	}

	wds.tabs = function() {

		var $group = $('.group'),
			$navtabs = $('.nav-tab-wrapper a'),
			active_tab = '',
			active_scroll = 0;

		// Hides all the .group sections to start
		$group.slideUp();

		$navtabs.on('click',function(e){
	    	
	    	$(this).blur();

			active_scroll = document.body.scrollTop;

			if ( $(this).attr('href') == location.hash ) {
		    	e.preventDefault();
			  	document.body.scrollTop = active_scroll;
			}
		})
		$(window).on('hashchange', function(e) {

			$navtabs.each(function(index, a) {

			    if ( $(a).attr('href') == location.hash ) {

			    	$navtabs.removeClass('nav-tab-active');

					$(a).addClass('nav-tab-active').blur();

					if ( typeof( localStorage ) != 'undefined' ) {
						localStorage.setItem('active_tab', $(a).attr('href') );
					}

			      	var selected = $(a).attr('href');

					$group.slideUp();
					$(selected).slideDown(function(){$( selected.replace( 'section', 'tab' ) ).addClass('nav-tab-active');});
			    } else {
			    	e.preventDefault();
				  	document.body.scrollTop = active_scroll;
			    }

			});
		});
		
		// Find if a selected tab is saved in localStorage
		if ( typeof(wds.tab) != 'undefined' ) {
			active_tab = wds.tab;
		} else
		// Find if a selected tab is saved in localStorage
		if ( location.hash != "" ) {
			active_tab = location.hash;
			$( active_tab ).slideDown(function(){$( active_tab.replace( 'section', 'tab' ) ).addClass('nav-tab-active');});
		} else
		// Find if a selected tab is saved in localStorage
		if ( typeof(localStorage) != 'undefined' ) {
			active_tab = localStorage.getItem('active_tab');
		}
		console.log( active_tab );
		// If active tab is saved and exists, load it's .group
		if ( active_tab != '' && $(active_tab).length /*&& active_tab != location.hash*/ ) {
			active_scroll = document.body.scrollTop;
			location.hash = active_tab;
		  	document.body.scrollTop = active_scroll;
			// $( active_tab ).slideDown(function(){$( active_tab.replace( 'section', 'tab' ) ).addClass('nav-tab-active');});
			
		} else {
			$('.group:first').slideDown(function(){$('.nav-tab-wrapper a:first').addClass('nav-tab-active');});
			
		}
	}
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
