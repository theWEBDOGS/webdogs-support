<?php

/**
 * Fired during plugin deactivation
 *
 * @link       WEBDOGS.COM
 * @since      1.0.0
 *
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 * @author     WEBDOGS Support Team <thedogs@webdogs.com>
 */
class Webdogs_Support_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        wp_clear_scheduled_hook( 'wdw_create_daily_notification' );
        wp_clear_scheduled_hook( 'wds_scheduled_notification'  );
    }

}
