<?php

/**
 * Fired during plugin activation
 *
 * @link       WEBDOGS.COM
 * @since      1.0.0
 *
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 * @author     WEBDOGS Support Team <thedogs@webdogs.com>
 */
class Webdogs_Support_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        if(!function_exists('wp_cookie_constants') ) require_once( ABSPATH . 'wp-includes/default-constants.php');

        if(!function_exists('wp_get_current_user') ) require_once( ABSPATH . 'wp-includes/pluggable.php');

        if(!function_exists('is_plugin_active')) require_once( ABSPATH . 'wp-admin/includes/plugin.php');

        if(!function_exists('wp_prepare_themes_for_js')) require_once( ABSPATH . 'wp-admin/includes/theme.php');

        $deactivate_plugins = apply_filters( 'webdogs_support_activator_deactivate_plugins', 
            array(
                WP_PLUGIN_DIR . '/webdogs-support-dashboard-widget/webdogs-support-dashboard-widget.php',
                WP_PLUGIN_DIR . '/login-logo-svg/login-logo.php',
                WP_PLUGIN_DIR . '/login-logo/login-logo.php' ) );

		deactivate_plugins( $deactivate_plugins );


        ///////////////////////////////
        //                           //
        //   ADD NOTIFICATION CRON   //
        //                           //
        ///////////////////////////////
        Self::init_schedule();


        ///////////////////////////////
        //                           //
        //   REWRITE API ENDPOINTS   //
        //                           //
        ///////////////////////////////
        Self::init_endpoint();


        ///////////////////////////////
        //                           //
        //   UNPACK WATCHDOG TO MU   //
        //                           //
        ///////////////////////////////
        Self::init_watchdog();


        ///////////////////////////////
        ///////////////////////////////
        //                           //
        //    A C T I V A T I O N    //
        //     C O M P L E T E D     //
        //                           //
        //     Y O U   E N J O Y     //
        //                           //
        ///////////////////////////////

	}

        ///////////////////////////////
        //                           //
        //   ADD NOTIFICATION CRON   //
        //                           //
        ///////////////////////////////
    public static function init_schedule() { 
        if(!class_exists('Webdogs_Support_Maintenance_Notifications') ) {
            require_once WEBDOGS_SUPPORT_DIR . 'includes/class-webdogs-support-maintainance-notifications.php'; }
            Webdogs_Support_Maintenance_Notifications::create_daily_notification_schedule(); }

        ///////////////////////////////
        //                           //
        //   REWRITE API ENDPOINTS   //
        //                           //
        ///////////////////////////////
    public static function init_endpoint() { 
        if(!class_exists('Webdogs_Support_Endpoint') ) {
            require_once WEBDOGS_SUPPORT_DIR . 'includes/class-webdogs-support-endpoint.php'; }
            $endpoint = new Webdogs_Support_Endpoint; $endpoint->add_endpoint(); flush_rewrite_rules(); }

        ///////////////////////////////
        //                           //
        //   UNPACK WATCHDOG TO MU   //
        //                           //
        ///////////////////////////////
    public static function init_watchdog() {
        require_once(ABSPATH .'/wp-admin/includes/file.php'); $creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array());
        if ( ! WP_Filesystem($creds) ) { return false; } global $wp_filesystem;

        if(!defined('WATCHDOG_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog.zip'; $WATCHDOG_TO = WEBDOGS_SUPPORT_DIR .'watchdog/'; 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file ) ) { wp_die( $unzip_file->get_error_message() ); } } }

        if(!defined('WATCHDOG_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog/watchdog.php'; $WATCHDOG_TO = str_replace( untrailingslashit( WEBDOGS_SUPPORT_DIR ), WPMU_PLUGIN_DIR, WEBDOGS_SUPPORT_DIR . 'watchdog.php' ); 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) { $move_file = $wp_filesystem->move( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $move_file ) ) { wp_die( $move_file->get_error_message()); } } }

        if( defined('WPMU_PLUGIN_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog/watchdog.zip'; $WATCHDOG_TO = WPMU_PLUGIN_DIR .'/watchdog/'; 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file ) ) { wp_die( $unzip_file->get_error_message() /*'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.'*/ ); } } } }
}
