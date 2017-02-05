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

        if(!function_exists('deactivate_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

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
    protected static function init_schedule() { 
        if(!class_exists('Webdogs_Support_Maintenance_Notifications') ) {
            require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support-maintainance-notifications.php'; }
            Webdogs_Support_Maintenance_Notifications::create_daily_notification_schedule(); }

        ///////////////////////////////
        //                           //
        //   REWRITE API ENDPOINTS   //
        //                           //
        ///////////////////////////////
    protected static function init_endpoint() { 
        if(!class_exists('Webdogs_Support_Endpoint') ) {
            require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/class-webdogs-support-endpoint.php'; }
            Webdogs_Support_Endpoint::register();
            add_action( 'shutdown', 'flush_rewrite_rules' );
        }
}
