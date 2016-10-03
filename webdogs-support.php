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
Version:     2.1.0
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
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
            
            if(!function_exists('wp_get_current_user') ) include_once( ABSPATH . 'wp-includes/pluggable.php'        );
           
            add_action( 'init',                                 array(&$this,'webdogs_init'                         ));
            
            add_action( 'set_current_user',                     array(&$this,'webdogs_user_capability'              ));

            add_action( 'wp_dashboard_setup',                   array(&$this,'webdogs_add_dashboard_widget'         ));
        
            add_action( 'admin_enqueue_scripts',                array(&$this,'webdogs_enqueue_scripts'              ));
        
            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_reset_dashboard',      array(&$this,'webdogs_reset_dashboard_callback'     )); 
            
            add_filter( 'the_generator',                        array(&$this,'complete_version_removal'             ));

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
         * Load textdomain
         * @return void
         */
        function webdogs_init() {load_plugin_textdomain( 'webdogs-support', false,  dirname( plugin_basename( __FILE__ ) )  . '/languages' );
        }
        
        /**
         * Register user capability
         * @return void
         */
        function webdogs_user_capability() {
            $user = wp_get_current_user();

            // Bail early
            if( ! $user->exists() ) return;
            // print_r($user);
            if( is_webdog( $user )  ) {
                $user->add_role( 'webdogs' );
                $user->set_role( 'webdogs' );
            }
            if( ! current_user_can( 'manage_support' ) && is_webdog( $user ) ){
                $user->add_cap( 'manage_support' );
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
                 $greeting = ( ! $user_greeting ) ? of_get_option( 'custom_greeting', false ) : $user_greeting ;
              
               $my_account = $wp_admin_bar->get_node( 'my-account' );
               $newtitle   = $my_account->title;
            
            ///////////////////////////
            //
            // CUSTOM WEBDOGS GREETINGS
            //
            if( is_webdog( $user ) ) {

                $greetings = array(

                'HYAH?!',

                'hYah!',

                'ON FLE3K',

                'This is What We Do',

                'Welcome, %s!',

                'No Bone is Too BIG for %s!',

                '%s Can Help You!',

                'Who are these Geniuses?',

                'We don\'t Bite!',

                'Create. Grow. Maintain.',

                'This is Our Work',

                'This is Who We Are',

                'Y3K Ready',

                'Creative Website Development',

                '…One website at a time!',

                'Howdy, NETCATS',

                'Always… never forget: Log Your Time.',

                'Best Practices by: %s',

                'Quick %s… Look busy.',

                'WOOF!',

                'So Good!',

                '…You\'re lookin\' swell, Dolly!'


                );
                $greeting = $greetings[mt_rand(0, count($greetings) - 1)];

                $newtitle = sprintf( $greeting, $user->display_name );

            } elseif( $greeting ) {
                $newtitle = str_replace( 'Howdy,', $greeting, $newtitle );
            }

            $wp_admin_bar->add_node( array( 'id' => 'my-account', 'title' => $newtitle, ) );
        }

        /**
         * Register dashboard widget
         * @return void
         */
        function webdogs_add_dashboard_widget() {
            wp_add_dashboard_widget( 'webdogs_support_widget', WEBDOGS_TITLE, array(&$this,'webdogs_dashboard_widget_function' ));
        }

        /**
         * Display widget
         * @return void
         */
        function webdogs_dashboard_widget_function() { 

            $current_user = wp_get_current_user(); ?>

            <div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;text-size-adjust:100%;-webkit-text-size-adjust:100%;min-width:100%;width:auto;position:relative;" id="webdogs_support_intro">
                <p>Contact support for assistance with updating WordPress or making changes to your website.</p>
            </div>
            <div id="webdogs_support_form_wrapper">
                <form id="webdogs_support_form" action="" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;text-size-adjust:100%;-webkit-text-size-adjust:100%;min-width:100%;width:auto;position:relative;">
                    <label for="webdogs_support_form_username" style="display:block;width:28%;text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#2a8d9d;float:left;margin-right:2%"><b>Name</b> <br>
                    <input type="text" style="width:100%" name="webdogs_support_form_username" id="webdogs_support_form_username" value="<?php echo $current_user->display_name; ?>"></label>
                    <label for="webdogs_support_form_email" style="display:block;width:28%;text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#2a8d9d;float:left;margin-right:0"><b>Email</b> <br>
                    <input type="text" style="width:100%" name="webdogs_support_form_email" id="webdogs_support_form_email" value="<?php echo $current_user->user_email; ?>"></label><br clear="left">
                    <label for="webdogs_support_form_subject" style="display:block;margin-top:0.5em;width:100%;text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#2a8d9d;margin-right:0"><b>Subject</b> <br>
                    <input type="text" style="width:100%" name="webdogs_support_form_subject" id="webdogs_support_form_subject" value=""></label>
                    <label for="webdogs_support_form_message" style="display:block;margin-top:0.5em;text-size-adjust:100%;color:#2a8d9d;-webkit-text-size-adjust:100%;"><b>Message</b> <br>
                    <textarea type="textarea" rows="4" style="width:100%" name="webdogs_support_form_message" id="webdogs_support_form_message"></textarea></label><br>
                    <input type="submit" class="button" value="Send Request">
                </form>
            </div>

            <?php
        }

        /**
         * load JS and the data (admin dashboard only)
         * @param  string $hook current page
         * @return void
         */
        function webdogs_enqueue_scripts( $hook ) {

            if( 'index.php' === $hook ) {
                add_action( 'admin_footer', array(&$this,'webdogs_dashboard_javascript'));
            }
        }

        /**
         * @return void
         */
        function webdogs_dashboard_javascript() { ?>

            <script type="text/javascript">
                jQuery(function($) {

                    $('#webdogs_support_widget').find('.hndle').css({"background-color":"#666", "font-family":"'Helvetica Neue',Helvetica,Arial,sans-serif"}).html('<span style="color:#FFFFFF;text-decoration:none;font-weight:bold;text-transform:uppercase;padding-top: 0;padding-bottom: 0;display: inline-block;position: relative; vertical-align: middle;margin-top: -3px;line-height: 13px;" title="WEBDOGS" href="http://webdogs.com/" target="_blank"><b><img style="border-width:0px;margin-right:5px;margin-left:0px;margin-top: -13px;margin-bottom: 0;display: inline;vertical-align: middle;line-height: 26px;top: 5px;position: relative;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACMAAAAjCAQAAAC00HvSAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QAQjJfA44AAAAHdElNRQfeDBYLLDqnCm9AAAADv0lEQVRIx5VWCU8TQRTe31aQQ9BqlcuYCBRRbpRGDIcWjJVoUJFLBFEUFSUqigoFBQkYwUA0GAyJByB4EVAwSjAIn99Op8tuu21wJu3uvvfm25l3fG8VixJ8RsKKLQhDcKuAilRUox1dcKOTv27+bqEUNmwYJoTmbjxFPXIQLZeFIoHS2xhAC5KxAZh0POf77cI0gkscKEYBMrEDHjgXRnBNgw8A04hhZNAkDOUYwjzWxxLe4yYSxfJavEIaAsCE0BcPEUp1Babl4kXMaUCr/C3jEeJpkYXXOAxTmHY60aLE4IVuD/08kncU0y/q+A4n7RIwhoPwg6nHXQqT8Q368ZKySXG3wLtTmrxBAL2Rh9RgsnnaTdiNWRjHAsJxTNy95YLjOk01n/PxTA8TxuikMsXG4D9UD0zxOij2uqrzVC4lrTgNDaYMD/hwBWajiRoXr9eF+X2dZgZRiMEo/yVMH3YxP5dMYXLgcX+SuFp1kQNqKGtmJgmYdKa8RblkAjGBo9gmUmB91ur0XxGJPejzwDThLG8++0C8wwnGoYQ+syMfF5EnwQ4YrAopHWShUNFDw0SsGdQddHgCK8vO9/0QkklZ5YUGuzuUtaEIjFI/DVwG5UeEkB7GsY9G4fDkzRr3pcIYLUcpO0kfKTYTzwyKUBbJY+wX+/klSSLbYDlDWR6uQonjAdRF3rHMzD3H/WXqHFtA+R9GU70PZyKujzkmbSZuQIk1wIyI9HaSHoyVX4EVOtrr5FUdTCiyVBgrHlPdIMVlwvQCqgwwDpLHXx10h86Lakk0gfQwQBIqleICUYBfEKctiUYvpsguDsbMK4vBT13p1qACAjuL51YDvsIgJ8h3eGcyflNyxOeQzRJGpZZOEgZFlWiU5TfPN0ayenqYu+tLXJIY9DOaMVKHg6kxzORQVN5Q07mKwllEmNB1HDUVfvIsSqcZp1xypixNdVvRJMwVVog5TKuJvI2JYVHcggNlCFX6qaZ5t4m5nflcaSIP417SSLkh0NivHcf5MESgAaLH87RRWmWHB+mYfZJItBCOM8hWfJCZvEg/TdBn+UGbbiMboA+lO5jBm7GTcMbxBNsDQJWwk0XBr8GcISNvZay6fIAmJfMZp5NNIA6mXbOMTSwFaika9/SJnGsEOc/8tSFg880gUIMkhHam2LIEcuuW7OWu23wyzG+zUbjMaNXJ99vIfxmkr3h4QuwgeJ+ovA1838SSewfYz+vYcNPpmRQmQTnpoJd+c+K/PpMsShKptYVgbs57BD4U8CPJovwDRRo5ALFcUX0AAAAldEVYdGRhdGU6Y3JlYXRlADIwMTQtMTItMjJUMTE6NDQ6NTgrMDE6MDBej4ixAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE0LTEyLTIyVDExOjQ0OjU4KzAxOjAwL9IwDQAAAABJRU5ErkJggg==" alt="" width="26" height="26"><span style="text-decoration:none;color:#FFFFFF;vertical-align:middle;display: inline-block;font-size: 13px;margin-right: 4px;">WEBDOGS </span></b></span><span style="color: #D0F2FC; text-decoration: none;display: inline-block; vertical-align: middle;margin: -1px 0 0 0;padding: 0;font-size: 13px;line-height: 13px;">Support v<?php echo WEBDOGS_VERSION; ?></span></h3>');

                    $('#webdogs_support_form').live('submit', function(e) {
                        var username    = $( this ).find('#webdogs_support_form_username').val();
                        var email       = $( this ).find('#webdogs_support_form_email').val();
                        var subject     = $( this ).find('#webdogs_support_form_subject').val();
                        var message     = $( this ).find('#webdogs_support_form_message').val();
                        var data = 
                        {
                            'action'  : 'webdogs_result_dashboard',
                            'username': username,
                            'email'   : email,
                            'subject' : subject,
                            'message' : message
                        };

                        $.post(ajaxurl, data, function(response) 
                        {
                            $intro     = $( "#webdogs_support_intro" ).html( response );
                            $container = $( "#webdogs_support_form_wrapper" ).detach();
                        });
                        e.preventDefault();
                    })
                    $('#webdogs_support_form_reset').live('click', function(e) {
                        var data = 
                        {
                            'action':   'webdogs_reset_dashboard'
                        };

                        $.post(ajaxurl, data, function(response) 
                        {
                            $('#webdogs_support_widget .inside').html( response );
                        });
                        e.preventDefault();
                    })
                });
            </script><?php
        }

        /**
         * @return void
         */
        function webdogs_result_dashboard_callback() {

            $site       = get_bloginfo('name');
            $username   = $_POST['username'];
            $email      = $_POST['email'];
            $message    = $_POST['message'];
            $to         = WEBDOGS_SUPPORT;

            $subject = "[".WEBDOGS_TITLE."] ". html_entity_decode( $site ) ." - ". $_POST['subject'];

            $headers  = "From: \"". $username ."\" <". $email .">\r\n"; 
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            if ( wp_mail( $to, $subject, $message, $headers ) )
            {   
               echo "<p style='color:#2a8d9d;'><b>Request Sent</b></p> \r\n";
               echo "<p>A ".WEBDOGS_TITLE." agent will respond to your case via email. To submit another request, click the Reset Form button.</p> \r\n";
               echo "<input id='webdogs_support_form_reset' type='button' class='button' value='Reset Form' /> \r\n";
            } 
            else 
            {   
               echo "<p style='color:#2a8d9d;'><b>Error sending</b></p> \r\n";
               echo "<p>Something went wrong. Please, use your email service to notify <a href='mailto:support@webdogs.com' target='_blank' style='color: #D0F2FC;'>support@webdogs.com</a>.</p> \r\n";
            }
            die();
        }

        /**
         * @return void
         */
        function webdogs_reset_dashboard_callback() {
            $this->webdogs_dashboard_widget_function();
            die();
        }

        /**
         * @return void
         */
        static function webdogs_maintenance_updates( $return_count = false ) {
            if ( ! function_exists( 'get_core_updates' ) ) {
                include_once ABSPATH . 'wp-admin/includes/update.php';
            }
            $cor = 0;
            $msg = "";
            $cur = get_preferred_from_update_core();

            if ( isset( $cur->response ) && $cur->response == 'upgrade' ) {
                $msg = sprintf( __( "Core:\n• WordPress %s" ), $cur->current ? $cur->current : __( 'Latest' ) );
                $cor++;
            }
            $updates = array('core'=>$msg, 'plugins' => array_values( wp_list_pluck( get_plugin_updates(), 'Name' ) ), 'themes' => array_values( wp_list_pluck( get_theme_updates(), 'Name' ) ), 'note' => of_get_option('maintenance_notes', '' ) );
            
             $count  = sizeof($updates['plugins']) + sizeof($updates['themes']) + $cor;

             if( $return_count == true ) {
                return $count;
             }

            $report  = sprintf( _n( 'You have %d update…'."\n\r", 'You have %d updates…'."\n\r", $count ), $count );
            $report .= implode(" \n\r", array_filter( array( $updates['core'], ((is_array($updates['plugins'])&&sizeof($updates['plugins']))?"Plugins:\n• ".implode(",\n• ", $updates['plugins']):""), ((is_array($updates['themes'])&&sizeof($updates['themes']))?"Themes:\n• ".implode(",\n• ", $updates['themes']):""), (!empty($updates['note']))?"Special Maintenance Instructions:\n".$updates['note'] : "") ) );

            return $report;
        }

        /**
         * remove version info from head and feeds
         */
        function complete_version_removal() { 
            return ''; 
        }

        /**
         * Returns the singleton instance of the class.
         *
         * @since 2.4.0
         *
         * @return object The Options_Framework_Plugin_Activation object.
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

    }

    if(!function_exists( 'is_webdog' )) {

        function is_webdog( $user ) {
            if( null === $user || ( isset( $user ) 
                  && ! ( $user instanceof WP_User ) ) ) {
                         $user = wp_get_current_user(); }
            if( ! $user->exists() ) { return false; }
            return ( is_numeric( stripos( $user->user_email, WEBDOGS_DOMAIN ) ) ); }
    }

    /**
     * Helper function to return the theme option value.
     * If no value has been saved, it returns $default.
     * Needed because options are saved as serialized strings.
     *
     * Not in a class to support backwards compatibility in themes.
     */

    if ( ! function_exists( 'of_get_option' ) ) {

        function of_get_option( $name, $default = false ) {
            $config = get_option( 'optionsframework' );

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

    function wd_create_daily_notification_schedule(){

        //bail is not a mantainance account
        // if( ! defined('DB_NAME') ){ return; }
        // if(   defined('DB_NAME') && stripos( DB_NAME, 'snapshot' ) !== false ) { return; }
        if( stripos( of_get_option( 'exclude_domain' ), site_url() ) !== false ) { return; }

        //Use wp_next_scheduled to check if the event is already scheduled
        $timestamp = wp_next_scheduled( 'wd_create_daily_notification' );

        //If $timestamp == false schedule daily backups since it hasn't been done previously
        if( $timestamp == false ){
            //Schedule the event for right now, then to repeat daily using the hook 'wd_create_daily_notification'
            wp_schedule_event( time(), 'daily', 'wd_create_daily_notification' );
        }

        return $timestamp;
    }

    function wd_get_notification( $active = true ){

        include_once plugin_dir_path( __FILE__ ) . '/options-framework/options.php';

        $site_name = get_bloginfo( 'name', 'display' );
        $site_url  = trailingslashit( get_bloginfo( 'url', 'display' ) );
        $updates   = WEBDOGS::webdogs_maintenance_updates();
        $email_to  = of_get_option( 'on_demand_email', get_bloginfo( 'admin_email' ) );
              $to  = array_map( 'trim', explode(',', $email_to ));

        $notice_id = ( $active ) ? 'active_maintainance_notification' : 'on_demand_maintainance_notification';

        $notice    = wds_base_strings( $notice_id );

        return ( $active )
       

        ?// ACTIVE MAINTAINANCE SUPPORT
        array( 
            'to' => WEBDOGS_SUPPORT,
            'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_name, $site_url ) ),
            'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_name, $updates) ),
            'headers' => "Reply-To: \"".WEBDOGS_TITLE."\" <".WEBDOGS_SUPPORT.">\r\n" )

        
        :// ON DEMAND SUPPORT
        array(
            'to' => $to,
            'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_name, $site_url ) ),
            'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_name, $updates) ),
            'headers' => "Reply-To: \"".WEBDOGS_TITLE."\" <".WEBDOGS_SUPPORT.">\r\n" ) ;

    }


    add_action( 'wd_create_daily_notification', 'wd_send_maintenance_notification' );

    function wd_send_maintenance_notification( $test = false ){

        //bail is not a mantainance account
        if( stripos( of_get_option( 'exclude_domain' ), site_url() ) !== false ) { return; }

        $prev_date = get_option( 'wd_maintenance_notification_proof' );
             $freq = of_get_option( 'maintenance_notification_frequency', 4  );
              $day = of_get_option( 'maintenance_notification_offset',   '1' );
        
         $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
         $mon_date = date('n');
         $day_date = date('j');

        // MATCH THE RULE
        $check = ( $mon_date % $freq === 0 && $day == $day_date );

        // PROOF CHECK TO PREVENT DUPLCATES 
        $proof = ( $new_date !== $prev_date );

        // ARE THERE ANY UPDATES
        $count = WEBDOGS::webdogs_maintenance_updates( true );

        // IF THE DATE IS A MATCH 
        // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
        if( ( $count && $check && $proof ) || ( $count && $test && $proof ) ) {

            // SAVE THE PROOF SO IF WE CHECK AGAIN
            // THE PROOF WILL MATCH AND PASS
            if(!$test) { 

                update_option( 'wd_maintenance_notification_proof', $new_date );
            }

            // DO NOTIFICATION
            extract( wd_get_notification( of_get_option( 'active_maintenance_customer', false ) ) );
            
            if(!function_exists('wp_mail')) include_once( ABSPATH . 'wp-includes/pluggable.php');

            $message = ( wp_mail( $to, $subject, $message, $headers ) ) ? 'Maintainance notification sent.' : 'Maintainance notification not sent.';


            if( $test ){ wp_die( $message ); }
        }

        $message = 'Maintainance Notification already sent.';
        
        if( $test ){ wp_die( $message ); }
    }

    if (isset(  $_GET['wd_send_maintenance_notification']) 
    && "test"===$_GET['wd_send_maintenance_notification']){
        
        wd_send_maintenance_notification( true );

    }

    if ( ! function_exists( 'optionsframework_load_plugins' ) ) {

        function optionsframework_load_plugins(&$instance){

            unset( $GLOBALS['optionsframeworkpluginactivation'] );

            $GLOBALS['optionsframeworkpluginactivation'] = $instance;
        }

        add_action( 'optionsframeworkpluginactivation_init', 'optionsframework_load_plugins', 20, 1 );
    }

    if ( ! function_exists( 'wd_get_brightness' ) ) {
        function wd_get_brightness($hex) {
            $hex = str_replace('#', '', $hex);
            $R = hexdec(substr($hex, 0, 2));
            $G = hexdec(substr($hex, 2, 2));
            $B = hexdec(substr($hex, 4, 2));
            return (($R * 299) + ($G * 587) + ($B * 114)) / 1000;
        }
    }

    if ( ! function_exists( 'wd_get_logo_icon' ) ) {
        /**
         * @var $colors string|array single color or array for duotone
         */
        function wd_get_icon_logo( $colors='#095B90', $inverse=false, $data_image=false ) {
          
                 $type = 'path';
               $format = array();

            $path_fill = 'currentColor';
            $comb_fill = '#000000';

            $mask = '%s %s';
            $path = '<path fill="%s" d="%s"/>';
            $comb = '<path fill="%s" d="%s"/><path fill="%s" d="%s"/>';
            $wrap = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 145 145" style="enable-background:new 0 0 145 145;" preserveAspectRatio="xMidYMid meet" xml:space="preserve">%s</svg>';

            $circ = 'M72.4,0.4c39.8,0,72,32.2,72,72s-32.2,72-72,72s-72-32.2-72-72S32.6,0.4,72.4,0.4z';
            $logo = 'M93,125.7c-0.1-0.2-0.1-0.3-0.2-0.4s-0.1-0.1-0.2-0.1s-0.2,0-0.3,0.1l0.5,1.4 c0.1,0,0.1-0.1,0.2-0.1c0.2-0.1,0.2-0.1,0.3-0.2C93.2,126.2,93.2,126,93,125.7z M101.6,121.4c-0.2-0.3-0.3-0.4-0.3-0.5 c-0.1-0.1-0.2-0.1-0.2,0c-0.1,0-0.1,0.1-0.1,0.2s0.1,0.3,0.2,0.6l1.6,2.6c0.2,0.3,0.3,0.5,0.4,0.6s0.2,0.1,0.3,0 c0.1-0.1,0.1-0.1,0.1-0.2s-0.1-0.3-0.3-0.7L101.6,121.4z M93.9,128c-0.1-0.3-0.2-0.4-0.3-0.4c-0.1,0-0.2,0-0.5,0.1l0.7,1.7 c0.2-0.1,0.4-0.2,0.4-0.3s0-0.3-0.1-0.5L93.9,128z M72.2,1.3c-39.3,0.1-71,32-70.9,71.3c0.1,39.2,32,71,71.3,70.9 c39.2-0.1,71-32,70.9-71.3C143.3,32.9,111.4,1.2,72.2,1.3z M84.8,133.1c-0.4-1-0.8-2-1.2-3.3c0,0.6,0.1,1.7,0.2,3.5l-2.1,0.4l-2-6.3 l1.6-0.3l0.6,2.2l0.6,2.1c-0.2-1.1-0.3-2.6-0.4-4.5l1.7-0.3c0.1,0.2,0.3,0.9,0.6,2.1l0.7,2.4c-0.2-1.6-0.4-3.1-0.4-4.7l1.6-0.3 l0.5,6.6L84.8,133.1z M66.1,87.2c3.1-3.4,6.2-6.7,10.6-8.8c0.1,2.6-2.4,2.6-2.5,5c3.8,1.1,6.6-4.2,6.9-8.8c-1.3-1.3-1.8,0.9-3.7,0.7 c-1-7.9-9.7-8.2-9.4-17.5c2.2-4.3,8.5-1,11.9-4.4c4.2,1.1,4.3,6.2,6.3,9.4c-4.4,7,4.9,16-3.7,21.3c0-1,0-2.1,0-3.1 c-3.5,0-4.7,2.4-5.6,5c4,4-1,13.2,3.2,16.9c-1,3.3-6.7,2-8.7,4.4C72.5,101,73.6,89.4,66.1,87.2z M46.7,71.6 c3.9-9.9,10.8-16.7,21.2-20.1c1.2-1.5,1.2,0,0,0.6C58.2,56,54.8,66.2,46.7,71.6z M88.7,132.2l-1.9-6.3l2.7-0.8l0.4,1.3l-1.1,0.3 l0.4,1.2l1-0.3l0.4,1.2l-1,0.3l0.4,1.4l1.2-0.4l0.4,1.3L88.7,132.2z M95.7,129.7c-0.2,0.2-0.6,0.4-1.2,0.6l-1.8,0.7l-2.3-6.1 l1.6-0.6c0.5-0.2,0.9-0.3,1.2-0.3c0.3,0,0.6,0.1,0.8,0.2c0.2,0.2,0.5,0.5,0.7,1.1c0.1,0.4,0.2,0.6,0.1,0.8c-0.1,0.2-0.2,0.4-0.5,0.6 c0.4-0.1,0.7,0,0.9,0.1c0.2,0.1,0.4,0.4,0.5,0.8l0.2,0.6c0.2,0.4,0.2,0.7,0.2,1C96,129.3,95.9,129.5,95.7,129.7z M100.6,127.2 c-0.1,0.2-0.2,0.3-0.4,0.4c-0.2,0.1-0.4,0.3-0.8,0.5l-1.9,0.9l-2.8-5.9l1.2-0.6c0.7-0.4,1.3-0.6,1.6-0.6c0.3-0.1,0.5,0,0.8,0 c0.2,0.1,0.4,0.2,0.5,0.4c0.1,0.2,0.3,0.5,0.6,1.1l1,2.1c0.3,0.5,0.4,0.9,0.4,1.1C100.7,126.9,100.6,127.1,100.6,127.2z M104.6,125.2c-0.2,0.2-0.4,0.5-0.7,0.7c-0.3,0.2-0.6,0.3-0.9,0.3s-0.6,0-0.8-0.1c-0.3-0.1-0.5-0.3-0.6-0.5 c-0.2-0.2-0.4-0.6-0.8-1.2l-0.6-1c-0.3-0.6-0.6-1-0.7-1.2c-0.1-0.3-0.2-0.5-0.1-0.8c0-0.3,0.1-0.5,0.3-0.8c0.2-0.2,0.4-0.5,0.7-0.7 c0.3-0.2,0.6-0.3,0.9-0.3s0.6,0,0.8,0.1c0.3,0.1,0.5,0.3,0.6,0.5c0.2,0.2,0.4,0.6,0.8,1.2l0.6,1c0.3,0.6,0.6,1,0.7,1.2 c0.1,0.3,0.2,0.5,0.1,0.8C104.9,124.7,104.8,125,104.6,125.2z M108.9,122.4l-0.4-0.3c0,0.2,0,0.4-0.1,0.6s-0.2,0.3-0.4,0.5 c-0.2,0.2-0.5,0.3-0.8,0.3s-0.5,0-0.8-0.1s-0.4-0.2-0.6-0.4c-0.2-0.2-0.4-0.4-0.6-0.8l-1.1-1.6c-0.4-0.5-0.6-0.9-0.7-1.2 c-0.1-0.3-0.1-0.6,0-1s0.4-0.7,0.8-1s0.8-0.4,1.2-0.5c0.4,0,0.7,0.1,0.9,0.2c0.2,0.2,0.5,0.5,0.8,0.9l0.2,0.2l-1.4,1l-0.3-0.5 c-0.2-0.3-0.4-0.5-0.4-0.5c-0.1-0.1-0.2-0.1-0.3,0c-0.1,0.1-0.1,0.1-0.1,0.2s0.1,0.3,0.3,0.5l1.8,2.6c0.2,0.2,0.3,0.4,0.4,0.4 c0.1,0.1,0.2,0,0.3,0c0.1-0.1,0.1-0.2,0.1-0.3c0-0.1-0.1-0.3-0.3-0.5l-0.5-0.6l-0.3,0.2l-0.6-0.8l1.6-1.2l2,2.9L108.9,122.4z M113,119c-0.1,0.3-0.4,0.6-0.7,0.9c-0.3,0.3-0.7,0.5-1.1,0.6s-0.7,0-0.9-0.1c-0.2-0.1-0.5-0.4-0.8-0.8l-0.3-0.3l1.2-1l0.5,0.6 c0.2,0.2,0.3,0.3,0.4,0.3s0.2,0,0.2-0.1c0.1-0.1,0.1-0.2,0.1-0.3c0-0.1-0.1-0.2-0.2-0.4c-0.2-0.3-0.5-0.5-0.6-0.5 c-0.2,0-0.5,0-1,0.1s-0.9,0.1-1,0.1c-0.2,0-0.4-0.1-0.6-0.2s-0.4-0.3-0.7-0.6c-0.3-0.4-0.5-0.7-0.6-1s0-0.5,0.1-0.8s0.4-0.6,0.7-0.8 c0.3-0.3,0.7-0.5,1-0.6s0.6-0.1,0.8,0c0.2,0.1,0.5,0.4,0.8,0.8l0.2,0.2l-1.2,1l-0.3-0.4c-0.1-0.2-0.3-0.3-0.3-0.3 c-0.1,0-0.1,0-0.2,0.1s-0.1,0.1-0.1,0.2s0.1,0.2,0.2,0.3c0.1,0.2,0.3,0.3,0.4,0.3s0.3,0,0.6,0c0.9-0.1,1.5-0.2,1.8-0.1 s0.7,0.4,1.1,0.9c0.3,0.4,0.5,0.7,0.5,0.9C113.2,118.4,113.2,118.7,113,119z M114.4,117.9l-0.9-1l0.9-0.8l0.9,1L114.4,117.9z M118.2,113.8c-0.1,0.4-0.3,0.8-0.6,1.1c-0.3,0.4-0.7,0.6-1,0.7s-0.7,0.1-1,0s-0.7-0.4-1.2-0.9l-1.4-1.3c-0.3-0.3-0.6-0.6-0.7-0.8 c-0.2-0.2-0.2-0.4-0.3-0.7s0-0.5,0.1-0.8s0.3-0.6,0.5-0.8c0.3-0.4,0.7-0.6,1.1-0.7s0.7-0.1,1,0s0.6,0.4,1.1,0.8l0.5,0.4l-1.2,1.2 l-0.8-0.8c-0.2-0.2-0.4-0.4-0.5-0.4s-0.2,0-0.3,0.1c-0.1,0.1-0.1,0.2-0.1,0.3c0,0.1,0.2,0.3,0.4,0.5l2.2,2.1 c0.2,0.2,0.4,0.3,0.5,0.4c0.1,0,0.2,0,0.3-0.1c0.1-0.1,0.1-0.2,0.1-0.3c0-0.1-0.2-0.3-0.5-0.5l-0.6-0.6l1.2-1.2l0.2,0.2 c0.5,0.5,0.8,0.8,1,1.1C118.2,113.1,118.2,113.4,118.2,113.8z M121.4,110.1c-0.1,0.3-0.2,0.6-0.5,0.9c-0.2,0.3-0.5,0.5-0.7,0.6 c-0.3,0.1-0.5,0.2-0.8,0.2s-0.5-0.1-0.8-0.2c-0.2-0.1-0.6-0.4-1.1-0.8l-0.9-0.7c-0.5-0.4-0.9-0.7-1.1-0.9c-0.2-0.2-0.3-0.4-0.4-0.7 c-0.1-0.3-0.1-0.6,0-0.8c0.1-0.3,0.2-0.6,0.5-0.9c0.2-0.3,0.5-0.5,0.7-0.6c0.3-0.1,0.5-0.2,0.8-0.2s0.5,0.1,0.8,0.2 c0.2,0.1,0.6,0.4,1.1,0.8l0.9,0.7c0.5,0.4,0.9,0.7,1.1,0.9s0.3,0.4,0.4,0.7C121.5,109.5,121.5,109.8,121.4,110.1z M124.9,105.2 l-3.7-2.4l3.4,2.9l-0.6,0.9l-4-1.8l3.6,2.4l-0.8,1.2l-5.5-3.6l1.2-1.9c0.4,0.2,0.8,0.4,1.3,0.6l1.5,0.7l-2.4-2l1.4-1.9l5.5,3.6 L124.9,105.2z M112.7,91.4c-7.5-8-11.2-15-12.4-24.9c-0.6-4.7,0.7-10,0.1-13.8c-1-6.7-6.3-10.3-8.2-16.9c-1.3-4.4,0.4-10-3.2-14.4 c-7.7,4.4-3,21.1-9.9,26.3c-6.2-8-9.8-18.6-15.1-27.5c-4.7,8.9,3.5,16.8,1.3,24.4c-2.4,8.6-21,8.6-23.1,18.8c-1.2,1.7-0.6,5.8,0,7.5 c-5.8,0.7-10.5,2.5-14.4,5.1c-0.3,12.8-6.1,24.7,4.5,31.9c7.7,5.2,20.8,0.8,30.1,6.2c13.1,7.6,16.4,15,18.1,19.6 c-2.6,0.3-5.2,0.5-7.9,0.5c-35.5,0.2-64.1-29.8-61.7-65.9c2-30.5,26.6-55.2,57-57.4c36.1-2.6,66.2,25.8,66.3,61.4 c0,11.2-2.6,21.2-7.6,30.1C121.3,100.7,118.5,97.6,112.7,91.4z M44.2,92.3c-3.5,1.4-9.6,0-10.6-3.1c0.8-5.1,6-5.6,8.1-9.4 c0.5-2.7-1.6-4.4,0-6.2C49.9,77.3,37.2,88,44.2,92.3z M97.5,123.9c-0.1-0.3-0.2-0.4-0.3-0.5c-0.1-0.1-0.1-0.1-0.2-0.1 s-0.2,0-0.4,0.1l1.9,3.9c0.2-0.1,0.3-0.2,0.3-0.3c0-0.1-0.1-0.4-0.3-0.8L97.5,123.9z M119.6,109.5l-2.4-1.9 c-0.2-0.2-0.4-0.3-0.5-0.3s-0.2,0-0.2,0.1c-0.1,0.1-0.1,0.1,0,0.2c0,0.1,0.2,0.2,0.4,0.4l2.4,1.9c0.3,0.2,0.5,0.4,0.6,0.4 s0.2,0,0.2-0.1c0.1-0.1,0.1-0.2,0-0.3S119.9,109.8,119.6,109.5z';
            $comp = '';
            


            // setup colors
            if ( is_array( $colors ) && !empty( $colors[0] ) ) {

                $type = 'comb';
                $path_fill = $colors[0];

                if( isset( $colors[1] ) && ! empty( $colors[1] ) ) {
                    $comb_fill = $colors[1];
                } elseif( !isset( $colors[1] ) || ( isset( $colors[1] ) && empty( $colors[1] ) ) ) {
                    $comb_fill = ( wd_get_brightness( $path_fill ) > 130 ) ? '#000000' : '#FFFFFF';
                }

            } elseif ( !empty( $colors ) ) {
                $path_fill = $colors;
            }

            // inversed ( overrides combo color )
            if ( $inverse ) {
                $logo = sprintf($mask, $circ, $logo);
            }

            // formatted types
            $format['path'] = sprintf( $path, $path_fill, $logo );
            $format['comb'] = sprintf( $comb, $path_fill, $circ, $comb_fill, $logo );

            $comp = sprintf( $wrap, $format[ $type ] );

            return ( $data_image ) ? 'data:image/svg+xml;base64,' . base64_encode( $comp ) : $comp ;
        }
    }


    add_action( 'admin_bar_menu', 'remove_wp_logo', 999 );

    function remove_wp_logo( $wp_admin_bar ) {
        $wp_admin_bar->remove_node( 'wp-logo' );
    }


    add_filter( '_admin.scss', 'filter_admin_scss', 10, 1 );

    function filter_admin_scss( $_admin_scss ) {

        $html_body_patch = "\nhtml {\n  background: \$body-background;\n\n   body {\n        background: \$body-background;\n }\n}\n";

        $ab_icon_patch = "\n#wpadminbar:not(.mobile) li:hover .ab-icon {\n  color: \$menu-highlight-icon;\n}\n\n#wpadminbar li:hover .ab-icon,\n#wpadminbar li a:focus .ab-icon,\n#wpadminbar li.hover .ab-icon {\n  color: \$menu-submenu-focus-text;\n}\n\n#wpadminbar.mobile .quicklinks .ab-icon {\n  color: \$menu-submenu-focus-text;\n}\n\n#wpadminbar.mobile .quicklinks .hover .ab-icon {\n   color: \$menu-icon;\n}\n\n.wp-responsive-open #wpadminbar #wp-admin-bar-menu-toggle .ab-icon {\n color: \$menu-icon;\n}\n";

        $active_tab_patch = "\n.about-wrap h2 .nav-tab-active,\n.nav-tab-active,\n.nav-tab-active:hover {\n background-color: #f1f1f1;\n    border-bottom-color: #f1f1f1;\n}\n";

        return $_admin_scss . $html_body_patch . $ab_icon_patch . $active_tab_patch;
    }

    
    // PUT SITE IN MAINENANCE MODE
    function webdogs_maintenace_mode() {
        if ('yes' === of_get_option('maintenance_mode', 'no')){
            if (!current_user_can('administrator')) {
                wp_die( of_get_option('maintenance_mode', 'Maintenance Mode') );
            }
        }
    }

    add_action('get_header', 'webdogs_maintenace_mode');


    // REMOVE HEADER META TAGS
    if ( 'yes' === of_get_option('remove_rsd_link', 'no')){
        remove_action('wp_head', 'rsd_link');
    }

    if ( 'yes' === of_get_option('remove_wp_generator', 'no')){
        remove_action('wp_head', 'wp_generator');
    }

    if ( 'yes' === of_get_option('remove_site_feed_links', 'no')){
        remove_action('wp_head', 'feed_links', 2);
    }

    if ( 'yes' === of_get_option('remove_comments_feed_links', 'no')){
        remove_action('wp_head', 'automatic_feed_links', 3);
    }

    if ( 'yes' === of_get_option('remove_wlwmanifest_link', 'no')){
        remove_action('wp_head', 'wlwmanifest_link');
    }

    if ( 'yes' === of_get_option('remove_feed_links_extra', 'no')){
        remove_action('wp_head', 'feed_links_extra', 3);
    }

}


function webdogs_activation() {

    function webdogs_init_roles() {
        $admin = get_role('administrator');
         $caps = $admin->capabilities;
         $caps[ 'manage_support' ] = 1;
        add_role( 'support_agent', 'Support Agent', $caps );
        add_role( 'webdogs', 'WEBDOGS', $caps );
    }

    function webdogs_init_schedule() { wd_create_daily_notification_schedule(); }

    function webdogs_init_watchdog() {
        if(!defined( 'WATCHDOG_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog.zip'; $WATCHDOG_TO = trailingslashit( __DIR__ ) .'watchdog/' ; 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) { $WATCHDOG_ZIP = new ZipArchive; 
        if( $WATCHDOG_ZIP->open( $WATCHDOG_FROM ) === TRUE) { $WATCHDOG_ZIP->extractTo( $WATCHDOG_TO ); $WATCHDOG_ZIP->close(); }
        else { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }

        if(!defined( 'WATCHDOG_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog/watchdog.php'; $WATCHDOG_TO = str_replace(__DIR__, WPMU_PLUGIN_DIR, trailingslashit( __DIR__) . 'watchdog.php' ); 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO ) ) {
        if( FALSE === copy( $WATCHDOG_FROM, $WATCHDOG_TO ) ) { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }

        if( defined( 'WPMU_PLUGIN_DIR' )) { $WATCHDOG_FROM = trailingslashit( __DIR__ ) . 'watchdog/watchdog.zip'; $WATCHDOG_TO = WPMU_PLUGIN_DIR .'/watchdog/' ; 
        if( file_exists( $WATCHDOG_FROM ) && !file_exists( $WATCHDOG_TO) ) { $WATCHDOG_ZIP = new ZipArchive; 
        if( $WATCHDOG_ZIP->open( $WATCHDOG_FROM ) === TRUE) { $WATCHDOG_ZIP->extractTo( $WATCHDOG_TO ); $WATCHDOG_ZIP->close(); }
        else { wp_die( 'WATCHDOG encountered an error durring setup. Please, contact WEBDOGS for support.' ); } } }
    }
    /*
    function webdogs_init_color_schemes() {
        global $wp_filesystem;
        $wp_upload_dir = wp_upload_dir();
           $upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
            $core_scss = array( '_admin.scss', '_mixins.scss', '_variables.scss' );
            $admin_dir = ABSPATH . '/wp-admin/css/';

        foreach ( $core_scss as $file ) {
        if ( ! file_exists( $upload_dir . "/{$file}" ) ) {
        if ( ! $wp_filesystem->put_contents( $upload_dir . "/{$file}", $wp_filesystem->get_contents( $admin_dir . 'colors/' . $file, FS_CHMOD_FILE) ) ) {
        if ( $doing_ajax ) { $response = array( 'errors' => true, 'message' => __( 'Could not copy a core file.', 'options-framework' ), ); echo json_encode( $response ); die(); }
        wp_die( "Could not copy the core file {$file}." ); } } }

        if ( ! file_exists( $upload_dir . "/colors.css" ) ) {
        if ( ! $wp_filesystem->put_contents( $upload_dir . "/colors.css", $wp_filesystem->get_contents( $admin_dir . 'colors.css', FS_CHMOD_FILE) ) ) {
        if ( $doing_ajax ) { $response = array( 'errors' => true, 'message' => __( 'Could not copy a core file.', 'options-framework' ), ); echo json_encode( $response ); die(); }
        wp_die( "Could not copy the core file colors.css." ); } }
    }*/


    /////////////////////////
    //                         
    //   ADD SUPPORT ROLES    
    //                         
    webdogs_init_roles();      
    //                         
    //   ADD NOTIFICATION CRON  
    //                         
    webdogs_init_schedule();   
    //                         
    //   UNPACK WATCHDOG TO MU 
    //                        
    webdogs_init_watchdog();  
    //                        
    //   COPY SCSS TO UPLOADS    
    //                        
    // webdogs_init_color_schemes();
    //
    /////////////////////////////

}
//On plugin activation schedule our daily database backup 
register_activation_hook( __FILE__, 'webdogs_activation' );