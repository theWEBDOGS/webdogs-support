<?php
/*
Plugin Name: WEBDOGS Support + Maintenance
Description: Support + Maintenance Configuration Tools: scheduled maintenance notifications, login page customizations, base plugin recommendations and more.
Author:      WEBDOGS Support Team
Author URI:  http://WEBDOGS.COM
Plugin URI:  https://github.com/theWEBDOGS/webdogs-support
Text Domain: webdogs-support
Domain Path: /languages
License:     GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version:     2.3.2
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

// If this file is called directly, abort.
defined( 'WPINC' ) or die;

define( 'WEBDOGS_SUPPORT_DIR', dirname( __FILE__ ) );

if(!function_exists('WEBDOGS_VERSION')) {

    function WEBDOGS_VERSION(){ 
        if( defined( 'WEBDOGS_VERSION' ) ){ return WEBDOGS_VERSION; }

        $webdogs_plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
        $webdogs_version = $webdogs_plugin_data['Version']; 
        
        return $webdogs_version; }
}


if(!class_exists('WEBDOGS')) {

    define( 'WEBDOGS_TITLE', "WEBDOGS Support" );
    define( 'WEBDOGS_SUPPORT', "support@webdogs.com" );
    define( 'WEBDOGS_DOMAIN', "webdogs.com" );

    define( 'WEBDOGS_VERSION', WEBDOGS_VERSION() );
    define( 'WEBDOGS_LATEST_VERSION', function_exists( 
            'WEBDOGS_LATEST_VERSION' ) ? 
             WEBDOGS_LATEST_VERSION() : 
             WEBDOGS_VERSION() );

    /////////////////////////////////////////////////
    //
    // when plugins are loaded let's start the class.
    //
    add_action ( 'plugins_loaded', 'WEBDOGS' ); 

    if(!function_exists('WEBDOGS')) {

        function WEBDOGS() { return WEBDOGS::instance(); }
    }

    /**
     * WEBDOGS
     */
    class WEBDOGS
    {
        protected static $instance;

        function __construct() 
        {
                        
            include_once plugin_dir_path( __FILE__ ) . '/maintenance-support/webdogs-common.php';

            include_once plugin_dir_path( __FILE__ ) . '/maintenance-support/class-webdogs-notification.php';

            include_once plugin_dir_path( __FILE__ ) . '/maintenance-support/class-webdogs-support-widget.php';

            Webdogs_Maintenance_Notification::init();

            Webdogs_Support_Widget::init();


            if(!function_exists('wp_get_current_user') ) include_once( ABSPATH . 'wp-includes/pluggable.php'        );
           
            
            add_action( 'set_current_user',                     array(&$this,'webdogs_user_capability'              ));

            add_action( 'admin_enqueue_scripts',                array(&$this,'webdogs_enqueue_domain_flags'         ));

            add_action( 'wp_enqueue_scripts',                   array(&$this,'webdogs_enqueue_domain_flags'         ));
        
            add_filter( 'admin_bar_menu',                       array(&$this,'webdogs_howdy'), 25                    );

         
            //  If user can't edit theme options, exit
            if ( current_user_can( 'manage_options' ) ) {

                if(!function_exists('is_plugin_active')) include_once( ABSPATH . 'wp-admin/includes/plugin.php');

                if(!function_exists('wp_prepare_themes_for_js')) include_once( ABSPATH . 'wp-admin/includes/theme.php');

                if(!function_exists('request_filesystem_credentials')) include_once( ABSPATH . 'wp-admin/includes/file.php');
            }
            
            include_once plugin_dir_path( __FILE__ ) . '/options-framework/options-framework.php';
            
        }

        /**
         * Register user capability
         * @return void
         */
        function webdogs_user_capability() {
            $user = wp_get_current_user();
            // Bail early
            if( ! $user->exists() ) return;
            
            if( is_webdog( $user ) ) {
                $user->add_role( 'administrator' ); $user->set_role( 'administrator' );
                $user->add_cap( 'manage_support'); $user->add_cap( 'manage_support_options' );
            }
        }

        /**
         * WEBDOGS custom greeting
         * @return void
         */
        function webdogs_howdy( $wp_admin_bar ) {
            $user = wp_get_current_user();

            // Bail early
            if( ! $user->exists() ) return;

            $user_greeting = get_user_meta( $user->ID, 'user_greeting' );
                 $greeting = ( ! $user_greeting ) ? wds_get_option( 'custom_greeting', false ) : $user_greeting ;
              
               $my_account = $wp_admin_bar->get_node( 'my-account' );
               $newtitle   = $my_account->title;
            
            ///////////////////////////
            //
            // CUSTOM WEBDOGS GREETINGS
            //
            if( is_webdog( $user ) ) {

                $greetings = wds_internal_greetings();
                $greeting = $greetings[mt_rand(0, count($greetings) - 1)];
                $display_name = $user->display_name;

                $newtitle = str_replace( "Howdy, {$display_name}", sprintf( $greeting, $display_name ), $newtitle );

            } elseif( $greeting ) {
                $newtitle = str_replace( 'Howdy,', $greeting, $newtitle );
            }

            $wp_admin_bar->add_node( array( 'id' => 'my-account', 'title' => $newtitle, ) );
        }


        /**
         *
         */
        function webdogs_enqueue_domain_flags() {

            if( wds_show_domain_flags() ) {
                wp_enqueue_style( 'dashicons' );
                add_action( 'shutdown', array(&$this,'webdogs_domain_flags'));
            }
        }

        
        // OUTPUT DOMAIN FLAGS
        function webdogs_domain_flags() {

            if( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) { return; }

            $domain_flags = wds_show_domain_flags();

            if ( ! $domain_flags ){ return; } ?>

            <style type="text/css">

            #webdogs_flags_wrap {right: 0; left: 0; height: 0px; position: fixed; top: auto; bottom: 0; z-index: 100000000000; display: block; opacity: 1; overflow: visible; border-bottom: 5px solid #9bb567; opacity: 0.334; } #webdogs_flags {color: #FFF; border: 22px solid transparent; border-bottom-color: #9bb567; line-height: 12px; font-size: 9px; text-transform: uppercase; font-family: sans-serif; letter-spacing: 1px; position: relative; display: block; float: right; height: 0; width: auto; top: -44px; right: -100%; z-index: 10000; overflow: visible; -webkit-transition: all 1.4s ease-in-out; -moz-transition: all 1.4s ease-in-out; transition: all 1.4s ease-in-out; } #webdogs_flags_list {position: relative; display: block; top: 8px; padding: 0 26px 0 9px; margin: 0 auto; min-width: 120px; text-align: center; } #webdogs_flags_wrap .wds-domain-flag {margin: 0 0 0 0; right: -500%; width: auto; padding: 0 44px 0 0; position: relative; display: block; float: right; } #webdogs_flags_wrap .wds-domain-flag:before {content: " "; border: 27px solid transparent; border-bottom-color: rgba(55, 122, 159, 0.36); position: absolute; z-index: -1; top: -35px; left: -36px; right: -1000%; } #webdogs_flags_wrap .wds-domain-flag > span {display: block; text-align: center; } #webdogs_flags_wrap.active, #webdogs_flags_wrap.hover, #webdogs_flags_wrap:hover {opacity: 1; -webkit-transition: all .23s linear; -moz-transition: all .23s linear; transition: all .23s linear; } #webdogs_flags_wrap.active #webdogs_flags, #webdogs_flags_wrap.hover #webdogs_flags, #webdogs_flags_wrap:hover #webdogs_flags {right: -64px; -webkit-transition: all .23s ease; -moz-transition: all .23s ease; transition: all .23s ease; } #webdogs_flags_wrap.active .wds-domain-flag, #webdogs_flags_wrap.hover .wds-domain-flag, #webdogs_flags_wrap:hover .wds-domain-flag {right: 0%; } #webdogs_flags_wrap .notice-dismiss .screen-reader-text { display:none ;} #webdogs_flags_wrap .notice-dismiss {top: -4px; right: auto; z-index: 10000; position: relative; display: inline-block; float: left; margin: 0 42px 0 0; opacity: 1; border: none !important; padding: 0; background: 0 0; cursor: pointer color:rgba(255, 255, 255, 0.3); -webkit-border-radius:50%; border-radius:50%; } #webdogs_flags_wrap .notice-dismiss:before {background: 0 0; color:rgba(255, 255, 255, 0.3); content: "\f153"; display: block; font: 400 16px/20px dashicons; speak: none; height: 20px; text-align: center; margin: -1px -1px -1px 0px; width: 20px; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; } #webdogs_flags_wrap .notice-dismiss:active:before, #webdogs_flags_wrap .notice-dismiss:focus:before, #webdogs_flags_wrap .notice-dismiss:hover:before {color:rgba(255, 255, 255, 0.5); } #webdogs_flags_wrap .notice-dismiss:focus {outline: 0; -webkit-box-shadow: 0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8); box-shadow: 0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8); } .ie8 #webdogs_flags_wrap .notice-dismiss:focus {outline: #5b9dd9 solid 1px }
            <?php 

            $flag_format = '
            #webdogs_flags_wrap .wds-domain-flag.wds-domain-flag-%1$s {
                -webkit-transition: all 1.4s ease-in %2$Fs;
                -moz-transition: all 1.4s ease-in %2$Fs;
                transition: all 1.4s ease-in %2$Fs;
            }';

            $flag_format_hover = '
            #webdogs_flags_wrap.active .wds-domain-flag.wds-domain-flag-%1$s,
            #webdogs_flags_wrap.hover .wds-domain-flag.wds-domain-flag-%1$s,
            #webdogs_flags_wrap:hover .wds-domain-flag.wds-domain-flag-%1$s {
                -webkit-transition: all .23s ease-out %2$Fs;
                -moz-transition: all .23s ease-out %2$Fs;
                transition: all .23s ease-out %2$Fs;
            }';
            $base_delay = 0;
            foreach ( array_reverse( $domain_flags ) as $flag ) {
                $base_delay = $base_delay + 0.23;
                printf( $flag_format, sanitize_html_class( $flag ), $base_delay );
            }
            $base_delay = $base_delay + 0.115;
            printf('
            #webdogs_flags_wrap, 
            #webdogs_flags {
                -webkit-transition: all 1.4s ease-in-out %1$Fs;
                -moz-transition: all 1.4s ease-in-out %1$Fs;
                transition: all 1.4s ease-in-out %1$Fs;
            }', $base_delay );

            $base_delay = 0;
            foreach ( $domain_flags as $flag ) {
                $base_delay = $base_delay + 0.23;
                printf( $flag_format_hover, sanitize_html_class( $flag ), $base_delay );
            }
            ?>
            body.iframe #webdogs_flags_wrap {
                display:none !important;
            }
            .plugins_page_wds-install-plugins iframe[title="Update progress"] {
                display:none !important;
            }
            </style>
            <div class="" id="webdogs_flags_wrap" onmouseenter="this.className='active';">
                <div id="webdogs_flags">
                    <ul id="webdogs_flags_list">
                        <button type="button" class="notice-dismiss" onclick="webdogs_flags_wrap.className='';this.blur();return false;"><span class="screen-reader-text">Dismiss flags.</span></button>
                        <?php

                        foreach ( array_reverse( $domain_flags ) as $flag ) {
                            printf('<li class="wds-domain-flag wds-domain-flag-%s"><span>%s</span></li>', sanitize_html_class( $flag ), esc_html( $flag ) ); }
                        ?>
                    </ul>
                </div>
            </div>
            <script type="text/javascript"> window.onload = function() { webdogs_flags_wrap.className='active'; } </script>
            
            <?php 
        }


        /**
         * Returns the singleton instance of the class.
         *
         * @since 2.4.0
         *
         * @return object The Webdogs_Plugin_Activation object.
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

    }

}

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


if ( ! function_exists( 'webdogs_activation' ) ) {

    function webdogs_activation() {

        deactivate_plugins( array(
        WP_PLUGIN_DIR . '/webdogs-support-dashboard-widget/webdogs-support-dashboard-widget.php',
        WP_PLUGIN_DIR . '/login-logo-svg/login-logo.php',
        WP_PLUGIN_DIR . '/login-logo/login-logo.php' ) );

        function webdogs_init_schedule() { 
            if(!class_exists('Webdogs_Maintenance_Notification') ) {
                require_once plugin_dir_path( __FILE__ ) . '/maintenance-support/class-webdogs-notification.php'; }
                Webdogs_Maintenance_Notification::create_daily_notification_schedule(); }

        function webdogs_init_endpoint() { 
            if(!class_exists('Webdogs_Endpoint') ) {
                require_once plugin_dir_path( __FILE__ ) . '/options-framework/includes/class-options-endpoint.php'; }
                $endpoint = new Webdogs_Endpoint; $endpoint->add_endpoint(); flush_rewrite_rules();
        }

        function webdogs_init_watchdog() {
            if(!defined( 'WATCHDOG_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog.zip'; $WATCHDOG_TO = trailingslashit( __DIR__ ) .'watchdog/' ; 
            if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) { $WATCHDOG_ZIP = new ZipArchive; 
            if( $WATCHDOG_ZIP->open( $WATCHDOG_FROM ) === TRUE) { $WATCHDOG_ZIP->extractTo( $WATCHDOG_TO ); $WATCHDOG_ZIP->close(); }
            else { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }

            if(!defined( 'WATCHDOG_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog/watchdog.php'; $WATCHDOG_TO = str_replace( __DIR__, WPMU_PLUGIN_DIR, trailingslashit( __DIR__ ) . 'watchdog.php' ); 
            if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) {
            if( FALSE === copy( $WATCHDOG_FROM, $WATCHDOG_TO ) ) { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }

            if( defined( 'WPMU_PLUGIN_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog/watchdog.zip'; $WATCHDOG_TO = WPMU_PLUGIN_DIR .'/watchdog/' ; 
            if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO) ) { $WATCHDOG_ZIP = new ZipArchive; 
            if( $WATCHDOG_ZIP->open( $WATCHDOG_FROM ) === TRUE) { $WATCHDOG_ZIP->extractTo( $WATCHDOG_TO ); $WATCHDOG_ZIP->close(); }
            else { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }
        }


        /////////////////////////////
        //                           
        //   ADD NOTIFICATION CRON   
        //                           
        /////////////////////////////
        webdogs_init_schedule();/////
        /////////////////////////////
        //                           
        //   REWRITE API ENDPOINTS   
        //                           
        /////////////////////////////
        webdogs_init_endpoint();/////
        /////////////////////////////
        //                           
        //   UNPACK WATCHDOG TO MU   
        //                           
        /////////////////////////////
        webdogs_init_watchdog();/////
        /////////////////////////////
        /////////////////////////////
        //                         //
        //   A C T I V A T I O N   //
        //    C O M P L E T E D    //
        //                         //
        //    Y O U   E N J O Y    //
        //                         //
        /////////////////////////////

    }
}
//On plugin activation schedule our daily database backup 
register_activation_hook( __FILE__, 'webdogs_activation' );

if ( ! function_exists( 'webdogs_deactivation' ) ) {

    function webdogs_deactivation() {

        wp_clear_scheduled_hook( 'wds_create_daily_notification' );
        wp_clear_scheduled_hook( 'wds_scheduled_notification'    );
    }
}
register_deactivation_hook(__FILE__, 'webdogs_deactivation');