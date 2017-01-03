<?php

defined( 'WPINC' ) or die;

/**
 * A unique identifier is defined to store the options in the database.
 *
 */
function wds_option_name() {

						$id = 'wds_support_options';
	      $optionsframework = get_option('webdogs_support' );
	$optionsframework['id'] = $id;
	
	update_option( 'webdogs_support', $optionsframework );
}


/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */

function wds_options() {

	/////////////////////////
	//				       //
	// SETUP OPTION VALUES //
	//                     //
	/////////////////////////

	if ( current_user_can( 'manage_options' ) ) {

        if(!function_exists('is_plugin_active')) include_once( ABSPATH . 'wp-admin/includes/plugin.php');

        if(!function_exists('wp_prepare_themes_for_js')) include_once( ABSPATH . 'wp-admin/includes/theme.php');

        if(!function_exists('request_filesystem_credentials')) include_once( ABSPATH . 'wp-admin/includes/file.php');
    }

	// Retrieve a list of all 
	// installed plugins (WP cached).
	$installed_plugins = get_plugins(); 

	$plugins = wds_base_plugins();
	 $themes = wds_bundled_themes(); 

	$default_image = admin_url( 'images/wordpress-logo.svg' );

	// Custom Logo
	// check if set in cutomizer
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
	$login_logo = ( ! empty( $image[0] ) ) ? $image[0] : Webdogs_Login_Logo::$instance->get_location('url');

	$background_defaults = array(
		'color' => '#f1f1f1',
		'image' => $login_logo,
		'repeat' => 'no-repeat',
		'position' => 'bottom center',
		'attachment' => 'scroll' );

	$login_logo_height_array = array(
	   '50' => __('50px',  'webdogs-support' ),
	  '100' => __('100px', 'webdogs-support' ),
	  '200' => __('200px', 'webdogs-support' ),
	  '300' => __('300px', 'webdogs-support' ));

	$login_logo_margin_bottom_array = array(
	    '0' => __('none', 'webdogs-support' ),
	   '10' => __('10px', 'webdogs-support' ),
	   '20' => __('20px', 'webdogs-support' ),
	   '30' => __('30px', 'webdogs-support' ),
	   '40' => __('40px', 'webdogs-support' ));

	$service_array = array(
	   'deactivated'                         => __('Deactivated', 'webdogs-support' ),
	   'active_maintainance_notification'    => __('Active Maintenance', 'webdogs-support' ),
	   'on_demand_maintainance_notification' => __('On-Demand', 'webdogs-support' ),
	);

	$frequency_array = array(
		// '0' => __('Monthly',    'webdogs-support' ),
		'1' => __('Monthly',    'webdogs-support' ),
		'3' => __('Quarterly',  'webdogs-support' ),
		'6' => __('Biannually', 'webdogs-support' ),
	);
	
	$day_offset = array(); for ($i=1; $i < 29; $i++) { $day_offset[$i] = $i; }

	$domain_flag_array = array(
		 '0' => __('Never',     'webdogs-support' ),
		 '1' => __('Always',    'webdogs-support' ),
		'-1' => __('Logged In', 'webdogs-support' ),
	);

	$boolean_active = array(
	   'yes' => __('Active', 'webdogs-support' ),
	    'no' => __('Hidden', 'webdogs-support' ),
	);

	$boolean_radio = array(
	   'yes' => __('Yes', 'webdogs-support' ),
	    'no' => __('No',  'webdogs-support' ),
	);

	/////////////////////////////////
	//				               //
	// SETUP DYNAMIC DESCRIPTIONS  //
	//                             //
	/////////////////////////////////
	$active_deletion_notice = false;

	$delete_plugins = array();
	foreach ( $plugins as $slug => $plugin ) {
		if ( !empty($plugin['force_deletion']) && true === $plugin['force_deletion'] /*&& ! empty( $installed_plugins[ $plugin['file_path'] ] )*/ ) {
			if ( is_plugin_active( $plugin['file_path'] ) ) {
				$active_deletion_notice = true;
				$delete_plugins[] = $plugin['name'] . '<span style="position: absolute;"><sup>*</sup></span>' ;
			} else { 
				$delete_plugins[] = $plugin['name'];
			}
		}
	}

	$recommend_plugins = array();
	foreach ( $plugins as $slug => $plugin ) {
		if ( !empty($plugin['force_deletion']) && true === $plugin['force_deletion'] ) continue;
		$recommend_plugins[] = $plugin['name'];
	}

	$delete_themes = array();
	$delete_themes[] = 'Authored by the WordPress team';
	foreach ( $themes as $slug => $theme ) { 
		if ( !empty($theme['force_deletion']) && true === $theme['force_deletion'] ) {

			if ( !empty($theme['active']) && true === $theme['active'] ) {
				$active_deletion_notice = true;
				$delete_themes[] = $theme['name'] . '<span style="position: absolute;"><sup>*</sup></span>' ;
			} else { 
				$delete_themes[] = $theme['name'];
			}
		}
	}

	$delete_base = "";

	if ( ! empty($delete_plugins) ) $delete_base .= '<strong>' . __('Plugins: ', 'webdogs-support' ) . '</strong>' . implode(', ', $delete_plugins ) . "<br/><br/>";

	if ( ! empty($delete_themes)  ) $delete_base .= '<strong>' . __('Themes: ' , 'webdogs-support' ) . '</strong>' . implode(', ', $delete_themes  );
	
	if ( ! empty($delete_base)    ) $delete_base .= '</p><p class="explain"><span><sup>*</sup></span><small>' . __(' Excludes active themes &amp; plugins' , 'webdogs-support' ) . '</small>';

	$delete_base = empty($delete_base) ? "Nothing to cleanup." : $delete_base ;


	$exclude_domain = ( wds_domain_exculded() ) ? '<strong>Current domain:</strong> Excluded' : '<strong>Current domain:</strong> Not&nbsp;excluded';

	$next_scheduled = wp_next_scheduled( 'wds_scheduled_notification' ); 
	$next_maintainance_notification = ( $next_scheduled ) ? sprintf( __('Next notification: %s'), date(' F j, Y' , $next_scheduled ) ) : null ;



	$options = array();
	
	///////////////////////////
	//				         //
	//  SETUP TABS AND FORM  //
	//                       //
	///////////////////////////

	$options[] = array(
		'type' => 'form',
		'id' => 'options_framework_form',
		'name' => 'webdogs-support',
		'wrap' => array( 
			'start' => true,),
		'options' => array( 
			'action' => 'options.php',
			'method' => 'post'));

	///////////////////////////
	//				         //
	// SETUP TABS AND FIELDS //
	//                       //
	///////////////////////////

	/*
	 *  NOTIFICATIONS | TAB 1
	 */

	$options[] = array(
		'name' => __('Notifications', 'webdogs-support' ),
		'capability'   => 'manage_support',
		'order' => 1,
		'type' => 'heading');

	$options[] = array(
		'name' => __('Maintenance Notification', 'webdogs-support' ),
		'id' => 'active_maintenance_customer',
		// 'desc' => 'Determines service.',
		'std' => 'on_demand_maintainance_notification',
		'type' => 'select',
		'class' => 'small alignleft mini',
		'options' => $service_array,
		'rule' => array(                       // RULES w/EXE CURRENT INPUT'S ON TRIGGER CALLBACK 
			'on' => 'change',                  // PERFORMS A JQUERY METHOD ON THE ELEMENT WITH ID
			'id' => 'wds-submit .wds_notification_events > .wds_notification_scheduled', // OR IT'S TARGET CHILDREN
			'exe' => array(
				'text' => "( val == 'deactivated') ? wds.l10n['notification_deactivated'] : wds.get_next_schedule()")));

	$options[] = array(
		'name' => __('Notification Frequency', 'webdogs-support' ),
		'id' => 'maintenance_notification_frequency',
		'std' => '1',
		// 'desc' => $next_maintainance_notification,
		'type' => 'radio',
		'class' => 'alignleft inline', 
		'options' => $frequency_array,
		'rule' => array(
			'id' => 'active_maintenance_customer',
			'filter' => 'select',
			'on' => 'change',
			'set' => array(
				'fadeIn' => array( 'active_maintainance_notification', 'on_demand_maintainance_notification' ),
				'fadeOut' => 'deactivated')));

	$options[] = array(
		'name' => __('Delivery Offset', 'webdogs-support' ),
		'desc' => __('Day of the month', 'webdogs-support' ),
		'id' => 'maintenance_notification_offset',
		'std' => '1',
		'type' => 'select',
		'class' => 'mini alignleft', 
		'options' => $day_offset,
		'rule' => array(
			'id' => 'active_maintenance_customer',
			'filter' => 'select',
			'on' => 'change',
			'set' => array(
				'fadeIn' => array( 'active_maintainance_notification', 'on_demand_maintainance_notification' ),
				'fadeOut' => 'deactivated')));

	$options[] = array(
		'name' => __('Primary Customer Email Address', 'webdogs-support' ),
		'desc' => __('Include multiple recipients, separated by commas.', 'webdogs-support' ),
		'id' => 'on_demand_email',
		'std' => '',
		'class' => 'clear bottom-pad top-border inset',
		'type' => 'text',
		'rule' => array(
			'id' => 'active_maintenance_customer',
			'filter' => 'select',
			'on' => 'change',
			'set' => array(
				'slideDown' => 'on_demand_maintainance_notification',
				'slideUp' => array( 'active_maintainance_notification', 'deactivated' ))));

	$options[] = array(
		'id' => 'maintenance_notes_wrap',
		'type' => 'info',
		'wrap' => array( 
			'start' => true, 
			'class' => 'clear top-border'));

	$options[] = array(
		'name' => __('Maintenance Instructions', 'webdogs-support' ),
		'id' => 'maintenance_notes',
		'settings'=> array('rows' => 13),
		'class' => 'clear alignleft',
		'type' => 'textarea');

	$options[] = array(
		'name' => __('Exclusionary Keywords', 'webdogs-support' ),
		'desc' => $exclude_domain,
		'id' => 'exclude_domain',
		'std' => 'staging',
		'class' => 'alignleft mini bottom-pad', 
		'type' => 'text');

	$options[] = array(
		'name' => __('Show Environment Info', 'webdogs-support' ),
		'desc' => __('Only logged in admin users would see environemnt info when enabled', 'webdogs-support' ),
		'id' => 'show_domain_flags',
		'std' => 'yes',
		'type' => 'radio',
		'class' => 'alignleft mini inline', 
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'wrap' => array( 
			'end' => true));


	/*
	 *  META OPTIONS | TAB 2
	 */

	$options[] = array(
		'name' => __('Access', 'webdogs-support' ),
		'capability' => 'manage_support',
		'order' => 2,
		'type' => 'heading');

	$options[] = array(
		'name' => 'Maintenance Mode',
		'desc' => 'Temporarily put the site in maintenance mode with a custom message. The site will be visible to admins only.',
		'type' => 'info',
		'class' => 'small alignleft bottom-pad');

	$options[] = array(
		'name' => __('Restrict Site Access and Display Notice', 'webdogs-support' ),
		'id' => 'maintenance_mode',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Maintenance Notice', 'webdogs-support' ),
		'id' => 'maintenance_message',
		'std' => '',
		'class' => 'clear bottom-pad top-border inset',
		'type' => 'textarea',
		'rule' => array(
			'id' => 'maintenance_mode', // RULES w/SET LISTEN TO AN INPUT'S ON TRIGGER AND 
			'on' => 'change',           // PERFORMS A JQUERY METHOD TO THE CURRENT SECTION
			'filter' => ':checked',     // THIS IS THE ID OF THE INPUT TO LISTEN FOR 
			'set' => array(	
				'slideDown' => 'yes',
				'slideUp' => 'no')));

	$options[] = array(
		'type' => 'info',
		'class' => 'clear top-border');

	$options[] = array(
		'name' => 'Remove Meta Tags',
		'desc' => 'Control syndication and feed accessibility by removing meta tags from the document head.',
		'type' => 'info',
		'class' => 'small alignleft');

	$options[] = array(
		'name' => __('Remove Site RSS Feeds', 'webdogs-support' ),
		'id' => 'remove_site_feed_links',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Remove Comments Feeds', 'webdogs-support' ),
		'id' => 'remove_comments_feed_links',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft mini inline',
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'class' => 'clear small alignleft');

	$options[] = array(
		'name' => __('Remove Extra Feed Links', 'webdogs-support' ),
		'id' => 'remove_feed_links_extra',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Remove RSD Link', 'webdogs-support' ),
		'id' => 'remove_rsd_link',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'class' => 'clear small alignleft');

	$options[] = array(
		'name' => __('Remove WP Generator Tag', 'webdogs-support' ),
		'id' => 'remove_wp_generator',
		'type' => 'radio',
		'std' => 'yes',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Remove WLW Tag', 'webdogs-support' ),
		'id' => 'remove_wlwmanifest_link',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'class' => 'clear');

	

	/*
	 *  SETTINGS | TAB 3
	 */

	$options[] = array(
		'name' => __('Settings', 'webdogs-support' ),
		'capability'   => 'manage_support',
		'order' => 3,
		'type' => 'heading');

	$options[] = array(
		'name' => 'Recommended Plugins',
		'desc' => implode(', ', $recommend_plugins ),
		'type' => 'info',
		'class' => 'small alignleft bottom-pad');

	$options[] = array(
		'name' => __('Display Plugin Recommendation Notice', 'webdogs-support' ),
		'id' => 'has_plugin_notices',
		'type' => 'radio',
		'std' => 'yes',
		'class' => 'inline alignleft',
		'options' => $boolean_active);

	$options[] = array(
		'name' => __('Automatically Activate Plugin After Installation', 'webdogs-support' ),
		'id' => 'has_forced_activation',
		'type' => 'radio',
		'std' => 'yes',
		'class' => 'alignleft inline',
		'options' => $boolean_radio);
	
	$options[] = array(
		'name' => 'Cleanup Core Bundles',
		'type' => 'info',		
		'desc' => $delete_base,
		'class' => 'alignleft small',
		'wrap' => array( 
			'start' => true, 
			'class' => 'clear top-border inset bottom-pad',));

	$options[] = array(
		'name' => __('Remove Bundled Plugins', 'webdogs-support' ),
		'id' => 'has_forced_deletion',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'name' => __('Remove Bundled Themes', 'webdogs-support' ),
		'id' => 'has_theme_deletion',
		'type' => 'radio',
		'std' => 'no',
		'class' => 'alignleft small inline',
		'options' => $boolean_radio);

	$options[] = array(
		'type' => 'info',
		'wrap' => array( 
			'end' => true));



	/*
	 *  LOGO OPTIONS | TAB 5
	 */

	$options[] = array(
		'name' => __('Options', 'webdogs-support' ),
		'capability' => 'manage_support_options',
		'order' => 5,
		'type' => 'heading');

	$options[] = array(
		'name' => __('Toolbar Logo', 'webdogs-support' ),  	
		'desc' => __('Toolbar logo must be SVG format saved as a compound path in a square viewbox. The artwork element of a logo works best.', 'webdogs-support' ),
		'id' => 'logo_icon',
		'std' => $default_image,
		'class' => 'clear',
		'type' => 'upload');

	$options[] = array(
		'id' => 'logo_icon_css',
		'class' => 'hide',
		'type' => 'textarea');

	$options[] = array(
		'name' => __('Login Logo and Background', 'webdogs-support' ),
		'id' => 'login_logo_css',
		'std' => $background_defaults,
		'type' => 'background');

	$options[] = array(
		'desc' => __('Bottom Margin', 'webdogs-support' ),
		'id' => 'login_logo_bottom_margin',
		'std' => '0',
		'type' => 'select',
		'class' => 'mini inline alignright', 
		'options' => $login_logo_margin_bottom_array,
		'rule' => array(                       // RULES w/EXE CURRENT INPUT'S ON TRIGGER CALLBACK 
			'id' => 'login_logo_css-image',    // PERFORMS A JQUERY METHOD ON THE ELEMENT WITH ID
			'on' => 'change',                  // OR IT'S TARGET CHILDREN
			'exe' => array(
				'css' => "'marginBottom', (Number(val)) + 'px'")));

	$options[] = array(
		'desc' => __('Logo Height', 'webdogs-support' ),
		'id' => 'login_logo_height',
		'std' => '100',
		'type' => 'select',
		'class' => 'mini inline alignright', 
		'options' => $login_logo_height_array,
		'rule' => array(
			'id' => 'login_logo_css-image',
			'on' => 'change',
			'exe' => array(
				'height' => "val"/*,
				'css' => "'backgroundSize', val + 'px'"*/)));

	$options[] = array(
		'name' => __('Admin Color Scheme', 'webdogs-support' ),
		// 'desc' => '',
		'must_use' => __('Restrict the admin colors to tne custom scheme.'),
		'id' => 'admin_color_scheme',
		'class' => 'clear top-border', 
		'type' => 'scheme');

	$options[] = array(
		'type' => 'form',
		'wrap' => array(
			'end' => true));


	/*
	 *  PLUGINS | TAB 4
	 */

	$options[] = array(
		'name' => __('Plugins', 'webdogs-support' ),
		'capability'  => 'manage_support',
		'order' => 4,
		'type' => 'heading',
		'class' => 'inset bottom-pad',
		'function' => 'Webdogs_Install_Plugins_Page',
		'active_tab' => 'Is_Webdogs_Plugins_Page' ); 
		
	return $options;
}
add_filter( 'wds_options', 'wds_options');


if ( ! function_exists( 'Is_Webdogs_Plugins_Page' ) ) {
	/**
	 * Helper function to register a collection of required plugins.
	 *
	 * @since 2.0.0
	 * @api
	 *
	 * @param array $plugins An array of plugin arrays.
	 * @param array $config  Optional. An array of configuration values.
	 */
	function Is_Webdogs_Plugins_Page( $boolval ) {
		return ( ! empty( $_GET['plugin_status'] ) );
	}
}

/**
 * Filter fields and tabs by capability.
 *
 */
function wds_filter_options_capability($options=array()) {
    
    if(empty($options)) return $options;

    $capability = array();
       $counter = 0;

         $clean = array();

    foreach ( $options as $value ) {

        $cap = false;
        $prev_cap = ( isset( $capability[ $counter ] ) && ! empty( $capability[ $counter ] ) ) ? $capability[ $counter ] : false ;
        
        if ( $value['type'] === "heading" ) {
            ++$counter;
        }
        
        if ( isset( $value['capability'] ) 
        && ! empty( $value['capability'] ) ) {

            $cap = $value['capability'];

            if ( $value['type'] = "heading" ) {
                $capability[ $counter ] = $cap;
            }
        }
        if ( isset( $capability[ $counter ] ) 
        && ! empty( $capability[ $counter ] ) ) {
            $cap = $capability[ $counter ];
        }

        // Check capability. Continue if user not incapable
        if( $cap && ! current_user_can( $cap ) ) {
            continue;
            // unset( $options[ $key ] );
        }
        $clean[]=$value;
    }
    return $clean;

}

add_filter( 'wds_options', 'wds_filter_options_capability', 20, 1 );


/**
 *
 *
 */
function wds_extra_domain_strings(){

	return apply_filters(
	'wds_extra_domain_strings', array(

        'wpengine.',
             '.com',
             '.net',
             '.org',
             '.edu',
              'www.' 
    ));
}

/**
 *
 *
 */
function wds_internal_greetings(){
	return apply_filters( 
	'wds_internal_greetings', array(

        'hYAh %s',

        'ON FLE3K',

        'This is What We Do',

        'No Bone is Too BIG for %s!',

        'Y3K Ready',

        'NETCATS RULEZ',

        'Always… never forget: Log Your Time.',

        'Quick %s… Look busy.',

        '…You\'re lookin\' swell, Dolly!',
	
		'%s builds websites that will blow your mind...POOF!',
				
		'IT\'S A TRAP!!!',
				
		'Be exceptional (deliver above and beyond)',

		'Ask the right questions (be proactive and never assume)',
				
		'Answer the right questions (listen for the real meaning)',
				
		'Learn constantly (and learn from mistakes)',
				
		'Everything has a map (find the best path)',
				
		'Make use of tools (everything has been done before)',
				
		'Let go of your ego',
				
		'It\'s everyone\'s first life (our intentions are good)',
				
		'You can\'t be perfect so you can always be better',
				
		'Positive words are great!',
			
		'10 - 15 minute rule (are you in a rabbit-hole?)',
			
		'If something seems broken, bring it up to the team',
				
		'Assume there is a simple explanation first',
				
		'Name files correctly (use descriptive titles, hyphens replace spaces)'

    ) );
}

/**
 *
 * Determine which bundled themes are 
 * installed and mark them for deletion.
 *
 */
function wds_bundled_themes(){
	
    if(!function_exists('wp_prepare_themes_for_js')) include_once( ABSPATH . 'wp-admin/includes/theme.php');

	$themes = wp_prepare_themes_for_js();

	$marked_themes = array();

	foreach ($themes as $theme) {
	    if( 'the WordPress team' !== $theme['author'] ) { continue; }
	    
	    $marked_themes[] = array(
			'name'           => $theme['name'],
			'slug'           => $theme['id'],
			'active'         => $theme['active'],
			'force_deletion' => true,
		);
	}
	return $marked_themes;
}


/**
 *
 * Return an array of recommended plugins
 * and plugins marked for deletion.
 *
 */
function wds_base_plugins(){

	return apply_filters( 'wds_base_plugins', array(

		array(
			'name'      => 'WEBDOGS Support + Maintenance',
			'slug'      => 'webdogs-support',
			'source'    => 'https://github.com/theWEBDOGS/webdogs-support/archive/master.zip',
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			'version'            => WEBDOGS_LATEST_VERSION, // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
			'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => 'https://github.com/theWEBDOGS/webdogs-support',
		),
		/*array(
			'name'      => 'WATCHDOG',
			'slug'      => 'watchdog',
			'source'    => WEBDOGS_SUPPORT_DIR_PATH. 'watchdog.zip',
			'file_path' => WPMU_PLUGIN_DIR . '/watchdog.php',
			'must_use'           => true, // If false, the plugin is only 'recommended' instead of required.
			'required'           => true, // If false, the plugin is only 'recommended' instead of required.
			// 'version'            => '1.0.1',
			'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
			'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
			'external_url'       => 'https://github.com/theWEBDOGS/watchdog',
		),*/
		array(
			'name'      => 'Simple History',
			'slug'      => 'simple-history',
		),
		array(
			'name'      => 'Antispam Bee',
			'slug'      => 'antispam-bee',
		),
		array(
			'name'      => 'Google Analytics by MonsterInsights',
			'slug'      => 'google-analytics-for-wordpress',
		),
		array(
			'name'      => 'Yoast SEO',
			'slug'      => 'wordpress-seo',
		),
		array(
			'name'      => 'Gravity Forms',
			'slug'      => 'gravityforms',
			'source'    => 'https://github.com/wp-premium/gravityforms/archive/master.zip'
		),
		array(
			'name'      => 'Advanced Custom Fields',
			'slug'      => 'advanced-custom-fields',
		),
		array(
			'name'      => 'Format Media Titles',
			'slug'      => 'format-media-titles',
		),
		array(
			'name'      => 'Members',
			'slug'      => 'members',
		),
		array(
			'name'      => 'Admin Menu Editor',
			'slug'      => 'admin-menu-editor',
		),
		array(
			'name'      => 'Admin Columns',
			'slug'      => 'codepress-admin-columns',
		),
		array(
			'name'      => 'SVG Support',
			'slug'      => 'svg-support',
		),
		array(
			'name'      => 'Redirection',
			'slug'      => 'redirection',
		),
		array(
			'name'      => 'WEBDOGS Support Dashboard Widget',
			'slug'      => 'webdogs-support-dashboard-widget',
			'file_path' => 'webdogs-support-dashboard-widget/webdogs-support-dashboard-widget.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Login Logo - SVG',
			'slug'      => 'login-logo-svg',
			'file_path' => 'login-logo-svg/login-logo.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Login Logo',
			'slug'      => 'login-logo',
			'file_path' => 'login-logo/login-logo.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Akismet',
			'slug'      => 'akismet',
			'file_path' => 'akismet/akismet.php',
			'force_deletion' => true,
		),
		array(
			'name'      => 'Hello Dolly',
			'slug'      => 'hello-dolly',
			'file_path' => 'hello.php',
			'force_deletion' => true, 
		)
	));
}



/**
 *
 *
 */
function wds_l10n_strings(){
	return apply_filters( 'wds_l10n', array() );
}


/**
 *
 * Determine which bundled themes are 
 * installed and mMark them for deletion.
 *
 */
function wds_base_strings( $key = null ){

	$strings = array(
		'page_title'                      => __( 'Recommended Plugins', 'webdogs-support' ),
		'menu_title'                      => __( 'Recommended Plugins', 'webdogs-support' ),
		'installing'                      => __( 'Installing Plugin: %s', 'webdogs-support' ), // %s = plugin name.
		'updating'                        => __( 'Updating Plugin: %s', 'webdogs-support' ), // %s = plugin name.
		'oops'                            => __( 'Something went wrong with the plugin API.', 'webdogs-support' ),
		'notice_can_install_required'     => _n_noop(
			'Required plugin: %1$s.',
			'Required plugins: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_install_recommended'  => _n_noop(
			'WEBDOGS recommends the following plugin: %1$s.',
			'WEBDOGS recommends the following plugins: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_install'           => _n_noop(
			'Sorry, but you do not have the correct permissions to install the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to install the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_ask_to_update'            => _n_noop(
			'The following plugin needs to be updated to its latest version to ensure maximum compatibility: %1$s.',
			'The following plugins need to be updated to their latest version to ensure maximum compatibility: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_ask_to_update_maybe'      => _n_noop(
			'Update available for: %1$s.',
			'Updates available for: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_update'            => _n_noop(
			'Sorry, but you do not have the correct permissions to update the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to update the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_activate_required'    => _n_noop(
			'The following required plugin is currently inactive: %1$s.',
			'The following required plugins are currently inactive: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_can_activate_recommended' => _n_noop(
			'The following recommended plugin is currently inactive: %1$s.',
			'The following recommended plugins are currently inactive: %1$s.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'notice_cannot_activate'          => _n_noop(
			'Sorry, but you do not have the correct permissions to activate the %1$s plugin.',
			'Sorry, but you do not have the correct permissions to activate the %1$s plugins.',
			'webdogs-support'
		), // %1$s = plugin name(s).
		'install_link'                    => _n_noop(
			'Install plugin',
			'Install plugins',
			'webdogs-support'
		),
		'update_link' 					  => _n_noop(
			'Update plugin',
			'Update plugins',
			'webdogs-support'
		),
		'activate_link'                   => _n_noop(
			'Activate plugin',
			'Activate plugins',
			'webdogs-support'
		),
		'plugin_deletion'                 => _n_noop( 
			'The following plugin has been removed: %1$s.', 
			'The following plugins have been removed: %1$s.', 
			'webdogs-support' 
		),
		'theme_deletion'      			  => _n_noop( 
			'The following theme has been removed: %s1$.', 
			'The following themes have been removed: %1$s.', 
			'webdogs-support' 
		),
        'maintainance_updates'     		  => _n_noop( 
			"You have %d update…\n\r", 
			"You have %d updates…\n\r", 
			'webdogs-support' 
		),

		'active_maintainance_notification'    => 
        array( 
            'subject' => "Scheduled Maintenance for %s", // hostname
            'message' => "The following updates are available for %s website: \n\r%s\n\r"), // hostname, updates
		
		'on_demand_maintainance_notification' => 
        array(
            'subject' => "WordPress Updates are Available for %s", // hostname
            'message' => "The following updates are available for the %s website. \n\r%s \n\rIf you would like WEBDOGS to install these updates, please reply to this email. \n*Note: Standard hourly billing rate will apply.\n\r"), // hostname, updates
        
        'maintainance_schedule'		      => __( 'Maintainance Notifications have been scheduled for %s.', 'webdogs-support' ),
        'maintainance_reschedule'		  => __( 'Maintainance Notifications have been rescheduled from %s to %s.', 'webdogs-support' ),
        'maintainance_deactivate'		  => __( 'Maintainance Notifications have been deactivated.', 'webdogs-support' ),
        'maintainance_send'               => __( 'Maintainance notifications have been sent.', 'webdogs-support' ),
        'maintainance_fail'               => __( 'Maintainance notifications failed to send.', 'webdogs-support' ),

        'maintainance_scheduled'		  => __( 'Maintainance Notifications is scheduled for %s.', 'webdogs-support' ),
        'maintainance_excluded'		      => __( "Maintainance Notifications are excluded \nfor this enviroment: %s", 'webdogs-support' ),
        'maintainance_deactivated'        => __( 'Maintainance Notifications are deactivated.', 'webdogs-support' ),
        'maintainance_sent'               => __( 'Maintainance Notifications last sent: %s.', 'webdogs-support' ),


        'acs_selections_preview'		  => __( 'Please make more selections to preview the color scheme.', 'webdogs-support' ),
        'acs_previewing_scheme'		      => __( 'Previewing. Be sure to save if you like the result.', 'webdogs-support' ),
        'acs_write_compiled_fail'		  => __( 'Could not write compiled CSS file.', 'webdogs-support' ),
        'acs_write_custom_fail'		      => __( 'Could not write custom SCSS file.', 'webdogs-support' ),
        'acs_copy_file_fail'		      => __( 'Could not copy a core file.', 'webdogs-support' ),

		'return'                          => __( 'Go back to Recommended Plugins', 'webdogs-support' ),
		'plugin_activated'                => __( 'Plugin activated successfully.', 'webdogs-support' ),
		'activated_successfully'          => __( 'The following plugin was activated successfully:', 'webdogs-support' ),
		'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'webdogs-support' ),  // %1$s = plugin name(s).
		'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed. Please update the plugin.', 'webdogs-support' ),  // %1$s = plugin name(s).
		'complete'                        => __( 'All plugins and themes have been installed and activated successfully. %1$s', 'webdogs-support' ), // %s = dashboard link.
		'contact_admin'                   => __( 'Please contact WEBDOGS for support.', 'webdogs-support' ),

		'nag_type'                        => 'update-nag', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
	);


    if( isset($key) && array_key_exists( $key, $strings ) )
    	return $strings[$key];
    else 
    	return $strings;
}


/**
 * 
 *
 */
function wds_load_plugins( &$instance ) {

    unset( $GLOBALS['wds_plugin_activation'] );

    $GLOBALS['wds_plugin_activation'] = $instance; 
} 

add_action( 'wds_plugin_activation_init', 'wds_load_plugins', 20, 1 );


/**
 * Register the required plugins for this theme.
 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
 */
function wds_register_base_activation() {

	/* Array of plugin arrays. Required keys are name and slug.
	 */
	$plugins = wds_base_plugins();

	/* Array of themes arrays. Required keys are name and slug.
	 */
	$themes = wds_bundled_themes();

	/* Load has_plugin_notices option from framework.
	 * Show admin notices or not.
	 */
	$has_notices = Webdogs_Utils::validate_bool( wds_get_option( 'has_plugin_notices', true));

	/* Load has_forced_activation option from framework.
	 * Automatically activate plugins after installation or not.
	 */
	$is_automatic = Webdogs_Utils::validate_bool( wds_get_option( 'has_forced_activation', true));

	/* Array of strings used throughout the admin screens.
	 */
	$strings = wds_base_strings();

	/* Array of configuration settings. Amend each line as needed.
	 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
	 * strings available, please help us make TGMPA even better by giving us access to these translations or by
	 * sending in a pull-request with .po file(s) with the translations.
	 *
	 */
	$config = array(
		'id'           => 'webdogs-support',                  // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                                 // Default absolute path to bundled plugins.
		'menu'         => 'wds-install-plugins',              // Menu slug.
		'parent_slug'  => 'plugins.php',                      // Parent menu slug.
		'capability'   => 'manage_options',                   // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => $has_notices,                       // Show admin notices or not.
		'dismissable'  => true,                               // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                                 // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => $is_automatic,                      // Automatically activate plugins after installation or not.
		'message'      => '',                                 // Message to output right before the plugins table.
		'message'      => '',                                 // Message to output right before the plugins table.
		'strings'      => $strings                            // Array of strings used throughout the admin screens.
	);

	Webdogs_Register_Plugins( $plugins, $themes, $config );
}

add_action( 'wds_register', 'wds_register_base_activation', 10 );

/**
 * 
 *
 */
function wds_admin_color_schemes( $scheme = null ) {
	
	$suffix = is_rtl() ? '-rtl' : '';

	$admin_color_schemes = array(

		'webdogs_ps' => array(

			'id' => 2,
			'slug' => 'webdogs_ps',
			'name' => 'WEBDOGS PS',
			'uri' => plugins_url( "admin/css/webdogs-ps/colors$suffix.css",  dirname( __FILE__ ) ),
			'icon_focus' => '#ECFFD3',
			'icon_current' => '#ECFFD3',
			'base_color' => '#F7FBFC',
			'icon_color' => '#7BBC49',
			'highlight_color' => '#3D5E63',
			'notification_color' => '#748477',
			'button_color' => '#7BBC49',
			'text_color' => '#474747',
			'body_background' => '#FCFEFF',
			'link' => '#3D5E63',
			'link_focus' => '#9CB4BC',
			'form_checked' => '#F28E4F',
			'menu_background' => '#F7FBFC',
			'menu_text' => '#C4C4C4',
			'menu_icon' => '#7BBC49',
			'menu_highlight_background' => '#748477',
			'menu_highlight_text' => '#F7FFEF',
			'menu_highlight_icon' => '#ECFFD3',
			'menu_current_background' => '#3D5E63',
			'menu_current_text' => '#ECFFD3',
			'menu_current_icon' => '#ECFFD3',
			'menu_submenu_background' => '#FFFFFF',
			'menu_submenu_text' => '#9CB4BC',
			'menu_submenu_background_alt' => '#D0F2FC',
			'menu_submenu_focus_text' => '#3D5E63',
			'menu_submenu_current_text' => '#748477',
			'menu_bubble_background' => '#F28E4F',
			'menu_bubble_text' => '#FFFFFF',
			'menu_bubble_current_background' => '#F28E4F',
			'menu_bubble_current_text' => '#FFFFFF',
			'menu_collapse_text' => '#919191',
			'menu_collapse_icon' => '#919191',
			'menu_collapse_focus_text' => '#9CB4BC',
			'menu_collapse_focus_icon' => '#9CB4BC',
			'adminbar_avatar_frame' => 'transparent',
			'adminbar_input_background' => '',
		),

		'webdogs_hs' => array(

			'id' => 5,
			'slug' => 'webdogs_hs',
			'name' => 'Webdogs HS',
			'uri' => plugins_url( "admin/css/webdogs-hs/colors$suffix.css",  dirname( __FILE__ ) ),
			'icon_focus' => '#002347',
			'icon_current' => '#ffffff',
			'base_color' => '#dbe9f0',
			'icon_color' => '#0c5b90',
			'highlight_color' => '#1274a8',
			'notification_color' => '#ef8f00',
			'button_color' => '#40bac8',
			'text_color' => '#0c5b90',
			'body_background' => '#f4f4f4',
			'form_checked' => '#ef8f00',
			'menu_text' => '#0c5b90',
			'menu_icon' => '#0c5b90',
			'menu_current_background' => '#0c5b90',
			'menu_current_text' => '#ffffff',
			'menu_submenu_background' => '#eff2f4',
			'menu_submenu_text' => '#1274a8',
			'menu_submenu_background_alt' => '#1be28f',
			'menu_submenu_focus_text' => '#002347',
			'menu_submenu_current_text' => '#002347',
			'menu_bubble_background' => '#ef8f00',
			'menu_bubble_text' => '#ffffff',
			'menu_bubble_current_background' => '#ef8f00',
			'menu_bubble_current_text' => '#ffffff',
			'menu_highlight_text' => '#ffffff',
			'menu_highlight_icon' => '#002347',
			'menu_current_icon' => '#ffffff',
			'adminbar_avatar_frame' => 'transparent',
			'adminbar_input_background' => '',
			'button_color' => '#ef8f00',
		),

		'webdogs_ds' => array(
			'id' => 3,
			'slug' => 'webdogs_ds',
			'name' => 'WEBDOGS DS',
			'uri' => plugins_url( "admin/css/webdogs-ds/colors$suffix.css",  dirname( __FILE__ ) ),
			'icon_focus' => '#ECFFD3',
			'icon_current' => '#ECFFD3',
			'base_color' => '#666666',
			'icon_color' => '#D0F2FC',
			'highlight_color' => '#377A9F',
			'notification_color' => '#9BB567',
			'button_color' => '#9BB567',
			'text_color' => '#C4C4C4',
			'body_background' => '#D6D6D6',
			'link' => '#377A9F',
			'link_focus' => '#31353f',
			'form_checked' => '#9BB567',
			'menu_background' => '#666666',
			'menu_text' => '#C4C4C4',
			'menu_icon' => '#D0F2FC',
			'menu_highlight_background' => '#31353F',
			'menu_highlight_text' => '#F7FFEF',
			'menu_highlight_icon' => '#ECFFD3',
			'menu_current_background' => '#377A9F',
			'menu_current_text' => '#ECFFD3',
			'menu_current_icon' => '#ECFFD3',
			'menu_submenu_background' => '#474747',
			'menu_submenu_text' => '#FCFCFC',
			'menu_submenu_background_alt' => '#D0F2FC',
			'menu_submenu_focus_text' => '#ECFFD3',
			'menu_submenu_current_text' => '#D0F2FC',
			'menu_bubble_background' => '#9BB567',
			'menu_bubble_text' => '#D0F2FC',
			'menu_bubble_current_background' => '#9DB780',
			'menu_bubble_current_text' => '#FFFFFF',
			'menu_collapse_text' => '#919191',
			'menu_collapse_icon' => '#919191',
			'menu_collapse_focus_text' => '#9CB4BC',
			'menu_collapse_focus_icon' => '#9CB4BC',
			'adminbar_avatar_frame' => 'transparent',
			'adminbar_input_background' => '',
		),

		'webdogs_wpe' => array(
			'id' => 4,
			'slug' => 'webdogs_wpe',
			'name' => 'WEBDOGS WPE',
			'uri' => plugins_url( "admin/css/webdogs-wpe/colors$suffix.css",  dirname( __FILE__ ) ),
			'icon_focus' => '#80d8de',
			'icon_current' => '#80d8de',
			'base_color' => '#f7fbfc',
			'icon_color' => '#80d8de',
			'highlight_color' => '#3d5e63',
			'notification_color' => '#60bb8f',
			'button_color' => '#40bac8',
			'text_color' => '#3d5e63',
			'body_background' => '#fcfeff',
			'link' => '#40bac8',
			'link_focus' => '#2a8792',
			'form_checked' => '#2a8792',
			'menu_background' => '#f7fbfc',
			'menu_text' => '#3d5e63',
			'menu_icon' => '#80d8de',
			'menu_highlight_background' => '#eaf6f9',
			'menu_highlight_text' => '#474747',
			'menu_highlight_icon' => '#40bac8',
			'menu_current_background' => '#2a8792',
			'menu_current_text' => '#ffffff',
			'menu_current_icon' => '#80d8de',
			'menu_submenu_background' => '#ffffff',
			'menu_submenu_text' => '#9cb4bc',
			'menu_submenu_background_alt' => '#80d8de',
			'menu_submenu_focus_text' => '#3d5e63',
			'menu_submenu_current_text' => '#3d5e63',
			'menu_bubble_background' => '#f28e4f',
			'menu_bubble_text' => '#ffffff',
			'menu_bubble_current_background' => '#f28e4f',
			'menu_bubble_current_text' => '#ffffff',
			'menu_collapse_text' => '#3d5e63',
			'menu_collapse_icon' => '#80d8de',
			'menu_collapse_focus_text' => '#3d5e63',
			'menu_collapse_focus_icon' => '#40bac8',
			'adminbar_avatar_frame' => 'transparent',
			'adminbar_input_background' => '',
		),

		'wpengine_tc' => array(

			'id' => 5,
			'slug' => 'wpengine_tc',
			'name' => 'WPEngine TC',
			'uri' => plugins_url( "admin/css/wpengine-tc/colors$suffix.css",  dirname( __FILE__ ) ),
			'icon_focus' => '#162a33',
			'icon_current' => '#162a33',
			'base_color' => '#162a33',
			'icon_color' => '#40bac8',
			'highlight_color' => '#2a8792',
			'notification_color' => '#60bb8f',
			'button_color' => '#40bac8',
			'text_color' => '#2a8792',
			'body_background' => '#f0f0f0',
			'link' => '#40bac8',
			'link_focus' => '#2a8792',
			'form_checked' => '#2a8792',
			'menu_background' => '#f0f0f0',
			'menu_text' => '#40bac8',
			'menu_icon' => '#40bac8',
			'menu_highlight_background' => '#F7FBFC',
			'menu_highlight_text' => '#2a8792',
			'menu_highlight_icon' => '#2a8792',
			'menu_current_background' => '#F7FBFC',
			'menu_current_text' => '#162a33',
			'menu_current_icon' => '#162a33',
			'adminbar_avatar_frame' => 'transparent',
			'adminbar_input_background' => '',
		)
	);

	return ( isset( $scheme ) && array_key_exists( $scheme, $admin_color_schemes ) ) ? apply_filters("admin_color_schemes_{$scheme}", $admin_color_schemes[ $scheme ] ) : apply_filters("admin_color_schemes", $admin_color_schemes );
}


/**
 * 
 *
 */
function wds_filter_admin_color_schemes( $schemes ) {
	// ALT DEV DOMAINS
	unset( $schemes['webdogs_ps'], $schemes['webdogs_wpe'], $schemes['wpengine_tc'] );

	//return webdogs schemes
	if( is_user_logged_in() && is_webdog( get_current_user() ) ) {
		return $schemes;

	} else {
	// nothing for you.
		return array();
	}
}
add_filter( 'admin_color_schemes', 'wds_filter_admin_color_schemes', 10, 1 );



function wds_remove_wp_logo( $wp_admin_bar ) {

    $wp_admin_bar->remove_node( 'wp-logo' );
}

add_action( 'admin_bar_menu', 'wds_remove_wp_logo', 999 );



function filter_admin_scss( $_admin_scss ) {

      $patch = "";
    $patches = apply_filters( 'scss_patches', array(

     array(
        'keys'   => array( 'body-background','body-background' ), 
        'format' =>"\nhtml {\n  background: \$%s;\n\n   body {\n        background: \$%s;\n }\n}\n" ),
                
     array(
        'keys'   => array( ), 
        'format' => "\n.about-wrap h2 .nav-tab-active,\n.nav-tab-active,\n.nav-tab-active:hover {\n background-color: #f1f1f1;\n    border-bottom-color: #f1f1f1;\n}\n" )
    
    ) );

    foreach ($patches as $args ) {
        
        $patch .= vsprintf( $args['format'], apply_filters( 'scss_keys', $args['keys'] ) );

    }
    return $_admin_scss . $patch;
}

add_filter( '_admin.scss', 'filter_admin_scss', 10, 1 );
add_filter( 'custom.scss', 'filter_admin_scss', 10, 1 );



// PUT SITE IN MAINENANCE MODE
function webdogs_maintenace_mode() {
    if ('yes' === wds_get_option('maintenance_mode', 'no')){
        if (! current_user_can('administrator')) {
            wp_die( wds_get_option('maintenance_message', 'Maintenance Mode') );
        }
    }
}
add_action('get_header', 'webdogs_maintenace_mode');


// REMOVE HEADER META TAGS
if ( 'yes' === wds_get_option('remove_rsd_link', 'no')){
    remove_action('wp_head', 'rsd_link');
}

if ( 'yes' === wds_get_option('remove_wp_generator', 'no')){
    remove_action('wp_head', 'wp_generator');
}

if ( 'yes' === wds_get_option('remove_site_feed_links', 'no')){
    remove_action('wp_head', 'feed_links', 2);
}

if ( 'yes' === wds_get_option('remove_comments_feed_links', 'no')){
    remove_action('wp_head', 'automatic_feed_links', 3);
}

if ( 'yes' === wds_get_option('remove_wlwmanifest_link', 'no')){
    remove_action('wp_head', 'wlwmanifest_link');
}

if ( 'yes' === wds_get_option('remove_feed_links_extra', 'no')){
    remove_action('wp_head', 'feed_links_extra', 3);
}
