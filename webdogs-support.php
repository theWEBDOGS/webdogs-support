<?php
/**
 * Plugin Name:       WEBDOGS Support + Maintenance
 * Plugin URI:        https://github.com/theWEBDOGS/webdogs-support
 * Description:       Support + Maintenance Configuration Tools: scheduled maintenance notifications, login page customizations, base plugin recommendations and more.
 * Version:           2.4.0
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
define( 'WEBDOGS_SUPPORT_SLUG', "webdogs-support" );

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
    require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support-activator.php';
    Webdogs_Support_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_webdogs_support' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-webdogs-support-deactivator.php
 */
function deactivate_webdogs_support() {
    require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support-deactivator.php';
    Webdogs_Support_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_webdogs_support' );

/**
 * The code that runs after plugin updates.
 * This action is documented in includes/class-webdogs-support-updater.php
 */
function update_webdogs_support() {
    require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support-updater.php';
    Webdogs_Support_Updater::update();
}
add_action( 'webdogs_support_updates', 'update_webdogs_support' );

/**
 * The code that runs during plugin updater.
 * This action is documented in includes/class-webdogs-support-updater.php
 */
function webdogs_support_update( $updater_object = null, $options = array() ) {
    $plugin_path = plugin_basename( __FILE__ );
    if  // IF
        ///////////////////////////////
        //  Was the plugin updated?  //
        ///////////////////////////////
        (   (   (     ! empty( $options[ 'action' ] ) && $options['action'] === 'update'     )
            &&  (     ! empty( $options[  'type'  ] ) && $options[ 'type' ] === 'plugin'     )
            &&  (   ( ! empty( $options[ 'plugin' ] ) && $options['plugin'] === $plugin_path )
                ||  ( ! empty( $options['packages'] ) && in_array( $plugin_path, $options['packages'] ) ) ) )
        //  OR 
        ///////////////////////////////
        //  WATCHDOG need updating?  //
        ///////////////////////////////
        ||  (   ( defined( 'WPMU_PLUGIN_DIR' ) && ! defined( 'WATCHDOG_DIR' ) )
            ||  ( defined( 'WPMU_PLUGIN_DIR' ) &&   defined( 'WATCHDOG_DIR' )
                &&  ( false !== ( $watchdog_plugin_data = get_file_data( WPMU_PLUGIN_DIR . '/watchdog.php', array( 'Version' => 'Version' ) ) ) )
                &&  ( version_compare( $watchdog_plugin_data['Version'], '1.0.1', '<' ) ) ) ) ) {
        // 
        do_action( 'webdogs_support_updates' ); }
}
add_action( 'updater_process_complete', 'webdogs_support_update', 10, 2 );
add_action( 'webdogs_support_init',     'webdogs_support_update', 11    );

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
        if ( ! isset( $config['id'] ) ) { return $default; }

        $options = get_option( $config['id'] );
        if ( isset( $options[$name] ) ) { return $options[$name]; }

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
    require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support.php';

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
