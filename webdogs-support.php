<?php
/**
 * Plugin Name:       WEBDOGS Support + Maintenance
 * Plugin URI:        https://github.com/theWEBDOGS/webdogs-support
 * Description:       Support + Maintenance Configuration Tools: scheduled maintenance notifications, login page customizations, base plugin recommendations and more.
 * Version:           2.3.7
 * Author:            WEBDOGS Support Team
 * Author URI:        WEBDOGS.COM
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       webdogs-support
 * Domain Path:       /languages
 *
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
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WEBDOGS_SUPPORT_DIR', trailingslashit( __DIR__ ) );
define( 'WEBDOGS_SUPPORT_DIR_PATH', plugin_dir_path( __FILE__ ) );

define( 'WEBDOGS_SUPPORT_ID', "wds" );

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

/**
 * The code that runs after plugin updates.
 * This action is documented in includes/class-webdogs-support-upgrader.php
 */
function update_webdogs_support() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-webdogs-support-upgrader.php';
    Webdogs_Support_Upgrader::upgrade();
}


  register_activation_hook( __FILE__, 'activate_webdogs_support' );
register_deactivation_hook( __FILE__, 'deactivate_webdogs_support' );

if( defined('WPMU_PLUGIN_DIR') 
    && ( ! file_exists( WPMU_PLUGIN_DIR . '/watchdog.php' ) 
    || (   file_exists( WPMU_PLUGIN_DIR . '/watchdog.php' ) 
        && false !== ( $watchdog_plugin_data = get_file_data( WPMU_PLUGIN_DIR . '/watchdog.php', array( 'Version' => 'Version' ) ) )
        && version_compare( $watchdog_plugin_data['Version'], '1.0.1', '<' ) ) ) ){
            update_webdogs_support(); }

/**
 * The code that runs during plugin upgrades.
 * This action is documented in includes/class-webdogs-support-upgrader.php
 */
function upgrade_webdogs_support( $upgrader_object, $options ) {
    $current_plugin_path_name = plugin_basename( __FILE__ );
    if ( 'update' === $options['action'] && 'plugin' === $options['type'] && !empty( $options['packages'] ) ){
       foreach( $options['packages'] as $each_plugin ){
            if ( $each_plugin === $current_plugin_path_name ){
                update_webdogs_support(); } } }
}
add_action( 'upgrader_process_complete', 'upgrade_webdogs_support', 10, 2 );


/**
 * Common functions for transforming data and plugin   
 * context/state reporting.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/functions-webdogs-support-common.php';


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-webdogs-support.php';

/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * Not in a class to support backwards compatibility in themes.
 */

if ( ! function_exists( 'wds_get_option' ) ) {

    function wds_get_option( $name, $default = false ) {
        $config = get_option( 'webdogs_support' );

        if ( ! isset( $config['id'] ) ) {
            return $default;
        }

        $options = get_option( $config['id'] );

        if ( isset( $options[$name] ) ) {
            return $options[$name];
        }

        return $default;
    }
}


/**
 * Begins execution of the plugin.
 *
 * @since    2.3.5
 */
function webdogs_support() {

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    $GLOBALS[ WEBDOGS_SUPPORT_ID ] = new Webdogs_Support();
    return $GLOBALS[ WEBDOGS_SUPPORT_ID ];

}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
webdogs_support()->run();
