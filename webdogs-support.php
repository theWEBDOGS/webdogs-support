<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              WEBDOGS.COM
 * @since             1.0.0
 * @package           Webdogs_Support
 *
 * @wordpress-plugin
 * Plugin Name:       WEBDOGS Support
 * Plugin URI:        https://github.com/theWEBDOGS/webdogs-support
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            WEBDOGS Support Team
 * Author URI:        WEBDOGS.COM
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webdogs-support
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WEBDOGS_SUPPORT_DIR', trailingslashit( __DIR__ ) );
define( 'WEBDOGS_SUPPORT_DIR_PATH', plugin_dir_path( __FILE__ ) );

if(!function_exists('WEBDOGS_VERSION')) {

    function WEBDOGS_VERSION(){ 
        if( defined( 'WEBDOGS_VERSION' ) ){ return WEBDOGS_VERSION; }

        $webdogs_plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
        $webdogs_version = $webdogs_plugin_data['Version']; 
        
        return $webdogs_version; }
}

if(class_exists('Webdogs_Support')) { return; }

define( 'WEBDOGS_TITLE', "WEBDOGS Support" );
define( 'WEBDOGS_SUPPORT', "support@webdogs.com" );
define( 'WEBDOGS_DOMAIN', "webdogs.com" );

define( 'WEBDOGS_VERSION', WEBDOGS_VERSION() );
define( 'WEBDOGS_LATEST_VERSION', function_exists( 
        'WEBDOGS_LATEST_VERSION' ) ? 
         WEBDOGS_LATEST_VERSION() : 
         WEBDOGS_VERSION() );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-webdogs-support-activator.php
 */
function activate_webdogs_support() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webdogs-support-activator.php';
	Webdogs_Support_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webdogs-support-deactivator.php
 */
function deactivate_webdogs_support() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-webdogs-support-deactivator.php';
	Webdogs_Support_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_webdogs_support' );
register_deactivation_hook( __FILE__, 'deactivate_webdogs_support' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-webdogs-support.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_webdogs_support() {

	$plugin = new Webdogs_Support();
	$plugin->run();

}
run_webdogs_support();
