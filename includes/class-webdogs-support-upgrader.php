<?php

/**
 * Fired during plugin upgrade
 *
 * @link       WEBDOGS.COM
 * @since      1.0.0
 *
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 */

/**
 * Fired during plugin upgrades.
 *
 * This class defines all code necessary to run during the plugin's upgrade.
 *
 * @since      1.0.0
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 * @author     WEBDOGS Support Team <thedogs@webdogs.com>
 */
class Webdogs_Support_Upgrader {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function upgrade() {        

        if(!function_exists('wp_cookie_constants') ) require_once( ABSPATH . 'wp-includes/default-constants.php');
        wp_cookie_constants();

        if(!function_exists('wp_get_current_user') ) include_once( ABSPATH . 'wp-includes/pluggable.php');

        if(!function_exists('is_plugin_active')) include_once( ABSPATH . 'wp-admin/includes/plugin.php');

        if(!function_exists('wp_prepare_themes_for_js')) include_once( ABSPATH . 'wp-admin/includes/theme.php');

        if(!function_exists('request_filesystem_credentials')) include_once( ABSPATH . 'wp-admin/includes/file.php');


        ///////////////////////////////
        //                           //
        //   UNPACK WATCHDOG TO MU   //
        //                           //
        ///////////////////////////////
        Self::upgrade_watchdog();


        ///////////////////////////////
        ///////////////////////////////
        //                           //
        //       U P G R A D E       //
        //     C O M P L E T E D     //
        //                           //
        //     Y O U   E N J O Y     //
        //                           //
        ///////////////////////////////

	}

        ///////////////////////////////
        //                           //
        //   UNPACK WATCHDOG TO MU   //
        //                           //
        ///////////////////////////////
    public static function upgrade_watchdog() {
        require_once(ABSPATH .'/wp-admin/includes/file.php'); $creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array());
        if ( ! WP_Filesystem($creds) ) { return false; } global $wp_filesystem;

        $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog.zip'; $WATCHDOG_TO = WEBDOGS_SUPPORT_DIR .'watchdog/'; 
        if( file_exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->delete( $WATCHDOG_TO ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( file_exists( $WATCHDOG_FROM ) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file  ) ) { wp_die( $unzip_file->get_error_message() ); } }

        if( defined('WPMU_PLUGIN_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog/watchdog.php'; $WATCHDOG_TO = str_replace( untrailingslashit( WEBDOGS_SUPPORT_DIR ), WPMU_PLUGIN_DIR, WEBDOGS_SUPPORT_DIR . 'watchdog.php' ); 
        if( file_exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->delete( $WATCHDOG_TO ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( file_exists( $WATCHDOG_FROM ) ) { $move_file = $wp_filesystem->move( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $move_file   ) ) { wp_die( $move_file->get_error_message()); } } }

        if( defined('WPMU_PLUGIN_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR . 'watchdog/watchdog.zip'; $WATCHDOG_TO = WPMU_PLUGIN_DIR .'/watchdog/';
        if( file_exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->delete( $WATCHDOG_TO ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( file_exists( $WATCHDOG_FROM ) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file  ) ) { wp_die( $unzip_file->get_error_message() /*'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.'*/ ); } } } }
}
