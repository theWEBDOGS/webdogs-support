<?php

/**
 * Fired during plugin update
 *
 * @link       WEBDOGS.COM
 * @since      1.0.0
 *
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 */

/**
 * Fired during plugin updates.
 *
 * This class defines all code necessary to run during the plugin's update.
 *
 * @since      1.0.0
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 * @author     WEBDOGS Support Team <thedogs@webdogs.com>
 */
class Webdogs_Support_Updater {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function update() {

        ///////////////////////////////
        //                           //
        //   UNPACK WATCHDOG TO MU   //
        //                           //
        ///////////////////////////////
        Self::update_watchdog();


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
    protected static function update_watchdog() {
        require_once( ABSPATH .'/wp-admin/includes/file.php'); $creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', false, false, array());
        if ( ! WP_Filesystem($creds) ) { return false; } global $wp_filesystem;

        $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR_PATH . 'watchdog.zip'; $WATCHDOG_TO = WEBDOGS_SUPPORT_DIR_PATH .'watchdog/'; 
        if( $wp_filesystem->exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->rmdir( $WATCHDOG_TO, true ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( $wp_filesystem->exists( $WATCHDOG_FROM ) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file  ) ) { wp_die( $unzip_file->get_error_message() ); } }

        if( defined('WPMU_PLUGIN_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR_PATH . 'watchdog/watchdog.php'; $WATCHDOG_TO = str_replace( untrailingslashit( WEBDOGS_SUPPORT_DIR_PATH ), WPMU_PLUGIN_DIR, WEBDOGS_SUPPORT_DIR_PATH . 'watchdog.php' ); 
        if( $wp_filesystem->exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->delete( $WATCHDOG_TO ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( $wp_filesystem->exists( $WATCHDOG_FROM ) ) { $move_file = $wp_filesystem->move( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $move_file   ) ) { wp_die( $move_file->get_error_message()); } } }

        if( defined('WPMU_PLUGIN_DIR')) { $WATCHDOG_FROM = WEBDOGS_SUPPORT_DIR_PATH . 'watchdog/watchdog.zip'; $WATCHDOG_TO = WPMU_PLUGIN_DIR .'/watchdog/';
        if( $wp_filesystem->exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->rmdir( $WATCHDOG_TO, true ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } }
        if( $wp_filesystem->exists( $WATCHDOG_FROM ) ) { $unzip_file = unzip_file( $WATCHDOG_FROM, $WATCHDOG_TO );
        if( is_wp_error( $unzip_file  ) ) { wp_die( $unzip_file->get_error_message() ); } } }

        $WATCHDOG_TO = WEBDOGS_SUPPORT_DIR_PATH .'watchdog/'; 
        if( $wp_filesystem->exists( $WATCHDOG_TO ) ) { $delete_file = $wp_filesystem->rmdir( $WATCHDOG_TO, true ); 
        if( is_wp_error( $delete_file ) ) { wp_die( $delete_file->get_error_message()); } } 
    }
}
