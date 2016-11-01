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
Version:     2.2.11
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

            add_action( 'admin_enqueue_scripts',                array(&$this,'webdogs_enqueue_domain_flags'         ));

            add_action( 'wp_enqueue_scripts',                   array(&$this,'webdogs_enqueue_domain_flags'         ));
        
            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_reset_dashboard',      array(&$this,'webdogs_reset_dashboard_callback'     )); 
            
            add_filter( 'the_generator',                        array(&$this,'complete_version_removal'             ));

            add_filter( 'admin_bar_menu',                       array(&$this,'webdogs_howdy'), 25                    );

            if( ( defined('DOING_CRON') && DOING_CRON ) || wd_test_send_maintenance_notification() ) {

                if ( ! function_exists( 'wp_version_check' ) ) include_once ABSPATH . 'wp-includes/update.php'; 

                if ( ! function_exists( 'wp_prepare_themes_for_js' ) ) include_once ABSPATH . 'wp-admin/includes/theme.php';

                if ( ! function_exists( 'get_core_updates' ) ) include_once ABSPATH . 'wp-admin/includes/update.php';
            }
            
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
        function webdogs_init() { 
            load_plugin_textdomain( 'webdogs-support', false,  dirname( plugin_basename( __FILE__ ) )  . '/languages' );
           
            // ARE WE TESTING THE NOTIFICAITON? 
            if( wd_test_send_maintenance_notification() ) {
                
                // IS IT FORCED SEND?
                $force = ('force' === wd_test_send_maintenance_notification() );

                // SEND TEST IN 5 SECONDS
                do_action('wds_test_maintenance_notification', $force );
            }
            
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
            if( is_webdog( $user ) ) {
                $admin = get_role( 'administrator' );
                $admin->add_cap( 'manage_support_options' );
                $user->add_role( 'administrator' );
                $user->set_role( 'administrator' );
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

            #webdogs_flags_wrap {
                right: 0;
                left: 0;
                height: 0px;
                position: fixed;
                top: auto;
                bottom: 0;
                z-index: 100000000000;
                display: block;
                opacity: 1;
                overflow: visible;
                border-bottom: 5px solid #9bb567;
                opacity: 0.334;
            }

            #webdogs_flags {
                color: #FFF;
                border: 22px solid transparent;
                border-bottom-color: #9bb567;
                line-height: 12px;
                font-size: 9px;
                text-transform: uppercase;
                font-family: sans-serif;
                letter-spacing: 1px;
                position: relative;
                display: block;
                float: right;
                height: 0;
                width: auto;
                top: -44px;
                right: -100%;
                z-index: 10000;
                overflow: visible;
                -webkit-transition: all 1.4s ease-in-out;
                -moz-transition: all 1.4s ease-in-out;
                transition: all 1.4s ease-in-out;
            }

            #webdogs_flags_list {
                position: relative;
                display: block;
                top: 8px;
                padding: 0 26px 0 9px;
                margin: 0 auto;
                min-width: 120px;
                text-align: center;
            }

            #webdogs_flags_wrap .wds-domain-flag {
                margin: 0 0 0 0;
                right: -500%;
                width: auto;
                padding: 0 44px 0 0;
                position: relative;
                display: block;
                float: right;
            }

            #webdogs_flags_wrap .wds-domain-flag:before {
                content: " ";
                border: 27px solid transparent;
                border-bottom-color: rgba(55, 122, 159, 0.36);
                position: absolute;
                z-index: -1;
                top: -35px;
                left: -36px;
                right: -1000%;
            }

            #webdogs_flags_wrap .wds-domain-flag > span {
                display: block;
                text-align: center;
            }

            #webdogs_flags_wrap.active, 
            #webdogs_flags_wrap.hover, 
            #webdogs_flags_wrap:hover {
                opacity: 1;
                -webkit-transition: all .23s linear;
                -moz-transition: all .23s linear;
                transition: all .23s linear;
            }

            #webdogs_flags_wrap.active #webdogs_flags,
            #webdogs_flags_wrap.hover #webdogs_flags,
            #webdogs_flags_wrap:hover #webdogs_flags {
                right: -64px;
                -webkit-transition: all .23s ease;
                -moz-transition: all .23s ease;
                transition: all .23s ease;
            }

            #webdogs_flags_wrap.active .wds-domain-flag,
            #webdogs_flags_wrap.hover .wds-domain-flag,
            #webdogs_flags_wrap:hover .wds-domain-flag {
                right: 0%;
            }

            #webdogs_flags_wrap .notice-dismiss .screen-reader-text { display:none ;}
            #webdogs_flags_wrap .notice-dismiss {
                top: -4px;
                right: auto;
                z-index: 10000;
                position: relative;
                display: inline-block;
                float: left;
                margin: 0 42px 0 0;
                opacity: 1;
                border: none !important;
                padding: 0;
                background: 0 0;
                cursor: pointer
                color:rgba(255, 255, 255, 0.3);
                -webkit-border-radius:50%;
                border-radius:50%;
            }

            #webdogs_flags_wrap .notice-dismiss:before {
                background: 0 0;
                color:rgba(255, 255, 255, 0.3);
                content: "\f153";
                display: block;
                font: 400 16px/20px dashicons;
                speak: none;
                height: 20px;
                text-align: center;
                margin: -1px -1px -1px 0px;
                width: 20px;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            #webdogs_flags_wrap .notice-dismiss:active:before,
            #webdogs_flags_wrap .notice-dismiss:focus:before,
            #webdogs_flags_wrap .notice-dismiss:hover:before {
                color:rgba(255, 255, 255, 0.5);
            }

            #webdogs_flags_wrap .notice-dismiss:focus {
                outline: 0;
                -webkit-box-shadow: 0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8);
                box-shadow: 0 0 0 1px #5b9dd9,0 0 2px 1px rgba(30,140,190,.8);
            }
            .ie8 #webdogs_flags_wrap .notice-dismiss:focus {
                outline: #5b9dd9 solid 1px
            }
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
            .plugins_page_optionsframework-install-plugins iframe[title="Update progress"] {
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
            
            <?php /* <script type="text/javascript" id="webdogs_maintenance_report"> var wds_site_data = <?php echo WEBDOGS::webdogs_maintenance_report(); ?>; </script>*/
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
            
            wp_version_check(  array(), true );
            wp_update_plugins( array()       );
            wp_update_themes(  array()       );
            
            $cor = 0;
            $msg = "";
            $cur = get_preferred_from_update_core();

            if ( isset( $cur->response ) && $cur->response == 'upgrade' ) {
                $msg = sprintf( __( "Core:\n• WordPress %s" ), $cur->current ? $cur->current : __( 'Latest' ) );
                $cor++;
            }
            $updates = array( 
                'core'    => $msg, 
                'plugins' => array_values( wp_list_pluck( get_plugin_updates(), 'Name' ) ), 
                'themes'  => array_values( wp_list_pluck( get_theme_updates(), 'Name' ) ), 
                'note'    => of_get_option('maintenance_notes', '' ) );
            
             $count  = sizeof($updates['plugins']) + sizeof($updates['themes']) + $cor;

             if( $return_count == true ) {
                return $count;
             }

            $report  = sprintf( _n( 'You have %d update…'."\n\r", 'You have %d updates…'."\n\r", $count ), $count );
            $report .= implode(" \n\r", 
                array_filter( 
                    array(
                        $updates['core'], 

                        ((is_array($updates['plugins'])&&sizeof($updates['plugins']))
                            ?"Plugins:\n• ".implode(",\n• ", $updates['plugins']):""), 

                        ((is_array($updates['themes']) &&sizeof($updates['themes'])) 
                            ?"Themes:\n• ". implode(",\n• ", $updates['themes']) :""), 

                        (!empty($updates['note']))
                            ?"Special Maintenance Instructions:\n".$updates['note'] :""
                    ) 
                ) 
            );

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

if ( ! function_exists( 'wds_create_daily_notification_schedule' ) ) {
    
    function wds_create_daily_notification_schedule( $clear = false ){

        if( ! wp_next_scheduled( 'wds_scheduled_notification' ) || $clear ){ 
            // wp_clear_scheduled_hook( 'wds_scheduled_notification' );

            $timestamp = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

            //Schedule the event for right now, then to repeat daily using the hook 'wds_scheduled_notification'
            wp_schedule_event( $timestamp, 'daily', 'wds_scheduled_notification' );

            return $timestamp;
        }
    }
}
add_action( 'wd_create_daily_notification', 'wds_create_daily_notification_schedule' );

if ( ! function_exists( 'wd_get_notification' ) ) {

    function wd_get_notification( $active = true ){

        include_once plugin_dir_path( __FILE__ ) . '/options-framework/options.php';

        $home_url  = parse_url( home_url() );
        $site_url  = $home_url["host"];
        $updates   = WEBDOGS::webdogs_maintenance_updates(false);
        $email_to  = of_get_option( 'on_demand_email', get_bloginfo( 'admin_email' ) );
              $to  = array_map( 'trim', explode(',', $email_to ));

        $notice_id = ( $active ) ? 'active_maintainance_notification' : 'on_demand_maintainance_notification';

        $notice    = wds_base_strings( $notice_id );

        return ( $active )

        ?
        // ACTIVE MAINTAINANCE SUPPORT
        array( 
            'to' => WEBDOGS_SUPPORT,
            'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_url ) ),
            'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_url, $updates) ),
            'headers' => "Reply-To: ".WEBDOGS_TITLE." <".WEBDOGS_SUPPORT.">\r\n" )

        
        :
        // ON DEMAND SUPPORT
        array(
            'to' => $to,
            'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_url ) ),
            'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_url, $updates) ),
            'headers' => "Reply-To: ".WEBDOGS_TITLE." <".WEBDOGS_SUPPORT.">\r\n" ) ;

    }
}

if ( ! function_exists( 'wd_get_active_dates' ) ) {

    function wd_get_active_dates( $freq, $day, $month, $year ) {
        
        $date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

        $active = array();

        $n = 0;
        for ($i = $month; $i <= 12; $i++) {

            $parsed = mktime(0, 0, 0, $i, $day, $year  );

            if( $i % $freq === 0 && $parsed > $date ) {
                $m = ( $i < 12 ) ? $i+1 : 1 ;
                $y = ( $i < 12 ) ? $year : $year + 1 ;


                $parsed = ( $freq > 1 ) ? mktime(0, 0, 0, $m, $day, $y  ) : $parsed;

                $active[$n] = $parsed;
                $n++;
            }

        }
        return $active;
    }
}

if ( ! function_exists( 'wd_get_next_schedule' ) ) {

    function wd_get_next_schedule( $return_array = false, $clear = true ){

           $year = date('Y');
          $month = date('n');
            $day = date('j');

        if ( $clear ) { wp_clear_scheduled_hook( 'wds_scheduled_notification' ); }  // delete_option( 'wd_maintenance_notification_proof' );
        if( wp_next_scheduled( 'wds_scheduled_notification' ) && wp_next_scheduled( 'wds_scheduled_notification' ) > mktime(0, 0, 0, $month, $day, $year ) ) {
            return wp_next_scheduled( 'wds_scheduled_notification' );
        }   

           $freq = of_get_option( 'maintenance_notification_frequency', 3  );
         $offset = of_get_option( 'maintenance_notification_offset',   '1' );
           
           $time = absint( $day ) > absint( $offset ) 

                ? mktime(0, 0, 0, $month, $offset, $year ) + wp_timezone_override_offset()

                : mktime(0, 0, 0, date('n', strtotime('first day of previous month') ), $offset, date('Y', strtotime('first day of previous month') ) ) + wp_timezone_override_offset();

         $prev_sent = get_option( 'wd_maintenance_notification_proof', $time );

        $prev_month = date( 'n', $prev_sent );
         $prev_year = date( 'Y', $prev_sent );
         $next_send = "";

        $month = ( $year === $prev_year 
               && $month === $prev_month 
               && absint( $day ) > absint( $offset ) ) 

                    ? $month 
                    : --$month ;

        $active_this_year = wd_get_active_dates( $freq, $offset, $month, $year );
        $active_next_year = wd_get_active_dates( $freq, $offset, 1,  1 + $year );

        if( sizeof($active_this_year) > 0 ) {
            $next_send = $active_this_year[0];
        } elseif( sizeof($active_next_year) > 0 ){
            $next_send = $active_next_year[0];
        }

        return ( $return_array ) 
            ? array( $next_send, $prev_month, $prev_year, $offset, $freq, $active_this_year, $active_next_year )
            : $next_send ;

    }
}


if ( ! function_exists( 'optionsframework_load_plugins' ) ) {
    function optionsframework_load_plugins(&$instance){
        unset( $GLOBALS['optionsframeworkpluginactivation'] );
        $GLOBALS['optionsframeworkpluginactivation'] = $instance; } 
}
add_action( 'optionsframeworkpluginactivation_init', 'optionsframework_load_plugins', 20, 1 );


if ( ! function_exists( 'wd_get_brightness' ) ) {

    function wd_get_brightness($hex) {
        $hex = str_replace('#', '', $hex);
        $R = hexdec(substr($hex, 0, 2));
        $G = hexdec(substr($hex, 2, 2));
        $B = hexdec(substr($hex, 4, 2));
        return (($R * 299) + ($G * 587) + ($B * 114)) / 1000;
    }
}

if ( ! function_exists( 'wd_get_icon_logo' ) ) {
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

    
if ( ! function_exists( 'wds_remove_wp_logo' ) ) {
    
    function wds_remove_wp_logo( $wp_admin_bar ) {

        $wp_admin_bar->remove_node( 'wp-logo' );
    }

}
add_action( 'admin_bar_menu', 'wds_remove_wp_logo', 999 );


if ( ! function_exists( 'filter_admin_scss' ) ) {

    function filter_admin_scss( $_admin_scss ) {

          $patch = "";
        $patches = apply_filters( 'scss_patches', array(

         array(
            'keys'   => array( 'body-background','body-background' ), 
            'format' =>"\nhtml {\n  background: \$%s;\n\n   body {\n        background: \$%s;\n }\n}\n" ),
         
         // array(
         //    'keys'   => array( 'menu-highlight-icon','menu-submenu-focus-text','menu-submenu-focus-text','menu-icon','menu-icon' ), 
         //    'format' => "\n#wpadminbar:not(.mobile) li:hover .ab-icon {\n  color: \$%s;\n}\n\n#wpadminbar li:hover .ab-icon,\n#wpadminbar li a:focus .ab-icon,\n#wpadminbar li.hover .ab-icon {\n  color: \$%s;\n}\n\n#wpadminbar.mobile .quicklinks .ab-icon {\n  color: \$%s;\n}\n\n#wpadminbar.mobile .quicklinks .hover .ab-icon {\n   color: \$%s;\n}\n\n.wp-responsive-open #wpadminbar #wp-admin-bar-menu-toggle .ab-icon {\n color: \$%s;\n}\n" ),
        
         array(
            'keys'   => array( ), 
            'format' => "\n.about-wrap h2 .nav-tab-active,\n.nav-tab-active,\n.nav-tab-active:hover {\n background-color: #f1f1f1;\n    border-bottom-color: #f1f1f1;\n}\n" )
        
        ) );

        foreach ($patches as $args ) {
            
            $patch .= vsprintf( $args['format'], apply_filters( 'scss_keys', $args['keys'] ) );

        }
        return $_admin_scss . $patch;
    }

}
add_filter( '_admin.scss', 'filter_admin_scss', 10, 1 );
add_filter( 'custom.scss', 'filter_admin_scss', 10, 1 );


if ( ! function_exists( 'format_scss_keys' ) ) {

    function format_scss_keys( $array ){ 
        return array_values( json_decode( str_replace("-", "_", json_encode( $array ) ), true ) ); 
    }
}

if ( ! function_exists( 'wds_must_use_admin_color' ) ) {

    function wds_must_use_admin_color(){
        $scheme = of_get_option( 'admin_color_scheme', array() );
        return ( isset( $scheme['must_use'] ) && 'on' === $scheme['must_use'] );
    }
}

if ( ! function_exists( 'stripNonAlpha' ) ) {
    /**
     * Remove all characters except letters.
     *
     * @param string $string
     * @return string
     */
    function stripNonAlpha( $string ) {
        return preg_replace( "/[^a-z]/i", "", $string );
    }
}

if ( ! function_exists( 'wds_domain_exculded' ) ) {

    function wds_domain_exculded(){
        $exclude = array();
        
        $domain_string = of_get_option( 'exclude_domain', false );
        
        if( $domain_string ) {
            $domain_string = array_map( 'trim', explode(',', $domain_string ) ) ;

            foreach ($domain_string as $term) {

                if ( stripos( site_url(), $term ) !== FALSE ) { 
                    $exclude[] =  $term;
                }
            }
        }
        if( wds_is_staging_site() ){
            $exclude[] = 'snapshot';
        }
        return ( empty( $exclude ) ) ? FALSE : $exclude ;
    }
}

if ( ! function_exists( 'wds_extra_domain_flags' ) ) {

    function wds_extra_domain_flags(){

        if(!function_exists('wds_extra_domain_strings')) include_once plugin_dir_path( __FILE__ ) . '/options-framework/options.php';

        $domain_strings = apply_filters('wds_extra_domain_flags', wds_extra_domain_strings() );

        $extra = array();

        foreach ($domain_strings as $term) {

            if ( stripos( site_url(), $term ) !== FALSE ) { 
                $extra[] = $term;
            }
        }
        return ( empty( $extra ) ) ? FALSE : $extra ;
    }
}

if ( ! function_exists( 'wds_is_production_site' ) ) {

    function wds_is_production_site(){
        if ( function_exists( 'is_wpe_snapshot' ) ) {
            // True if we're running inside a staging-area snapshot in WPEngines
            return ( FALSE === is_wpe_snapshot() );
        }
        return TRUE;
    }
}

if ( ! function_exists( 'wds_is_staging_site' ) ) {

    function wds_is_staging_site(){
        if ( function_exists( 'is_wpe_snapshot' ) ) {
            // True if we're running inside a staging-area snapshot in WPEngines
            return ( FALSE !== is_wpe_snapshot() );
        }
        return fALSE;
    }
}

if ( ! function_exists( 'wds_show_domain_flags' ) ) {

    function wds_show_domain_flags(){

        $domain_flags      = wds_get_domain_flags();
        $show_when         = of_get_option('show_domain_flags', 'yes');
        $show_domain_flags = FALSE;

        switch ( strval( $show_when ) ) {
            // ALWAYS   
            case 'yes':
                $show_domain_flags = ( wds_domain_exculded() || ( is_user_logged_in() && current_user_can( 'manage_support_options' ) ) ) ? TRUE : FALSE ;
                break;
            // NEVER
            case 'no':
            default:
                $show_domain_flags = FALSE ;
                break;
        }

        if( ! $show_domain_flags && $domain_flags ){
            $domain_flags = FALSE;
        } elseif( is_array( $domain_flags )) {
            $domain_flags = array_map( 'stripNonAlpha', $domain_flags );
        }

        return apply_filters('wds_show_domain_flags', $domain_flags );   
    }
}

if ( ! function_exists( 'wds_get_domain_flags' ) ) {

    function wds_get_domain_flags(){

        $exclutions = wds_domain_exculded();
            $extras = wds_extra_domain_flags();

        $flags = array();

        if( $exclutions ) {
           $flags  = $exclutions; 
        }
        if( wds_is_staging_site() ) {
           $flags[] = 'staging'; 
        }
        if( wds_is_production_site() ) {
           $flags[] = 'production'; 
        }
        if ( $extras ) { 
           $flags = array_merge( $flags, $extras ); 
        }

        $flags = empty( $flags ) ? FALSE : array_unique( array_values( $flags ) );

        return apply_filters('wds_get_domain_flags', $flags );
    }
}


if ( ! function_exists( 'wds_maybe_clear_cache' ) ) {

    function wds_maybe_clear_cache( $current_screen ){

        $clear = ( 'toplevel_page_options-framework' !== $current_screen->base || did_action( 'webdogs_do_clear_cache' ) || wds_is_staging_site() ) ? FALSE : TRUE ;
        
        if( $clear ){

            add_settings_error( 'options-framework', 'clear_cache', __( 'HTML-page-caching, CDN (statics), and WordPress Object/Transient Caches have been cleared.', 'options-framework' ), 'updated fade' ); 
            add_action( 'shutdown', 'webdogs_clear_cache' );
        }
    }
}

if ( ! function_exists( 'webdogs_clear_cache' ) ) {
    // CLEAR WPE CACHE
    function webdogs_clear_cache() {
        if( wds_is_production_site() ) {

            $next_send = wd_get_next_schedule();

            wp_clear_scheduled_hook( 'wds_scheduled_notification' );

            wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

            // refresh our own cache (after CDN purge, in case that needed to clear before we access new content)

            WpeCommon::purge_memcached();
            WpeCommon::clear_maxcdn_cache();
            WpeCommon::purge_varnish_cache();

            flush_rewrite_rules();
        }
    }
}

if ( ! function_exists( 'webdogs_maintenace_mode' ) ) {

    // PUT SITE IN MAINENANCE MODE
    function webdogs_maintenace_mode() {
        if ('yes' === of_get_option('maintenance_mode', 'no')){
            if (! current_user_can('administrator')) {
                wp_die( of_get_option('maintenance_message', 'Maintenance Mode') );
            }
        }
    }

}
add_action('get_header', 'webdogs_maintenace_mode');


if ( ! function_exists( 'wd_test_send_maintenance_notification' ) ) {
    //////////////////////////////////////////////////////
    // INLINE TEST////////////////////////////////////////
    function wd_test_send_maintenance_notification(){

        if (isset(  $_GET['wd_send_maintenance_notification'])
        && "test"===$_GET['wd_send_maintenance_notification'] 
        && isset(   $_GET['force_send'])
        && ""  !==  $_GET['force_send'] ) {
            return 'force';
        }
        return isset(   $_GET['wd_send_maintenance_notification'])
            && "test"===$_GET['wd_send_maintenance_notification'];
    }
}


if ( ! function_exists( 'wds_admin_notices' ) ) {

    function wds_admin_notices() {

        $message = get_option( 'maintenance_notification_test', false );

        if ( $message ) {

            delete_option( 'maintenance_notification_test' );
            add_settings_error( 'options-framework', 'maintenance-notification-test', $message, 'update-nag fade' );
            // settings_errors( 'maintenance-notification-test' );
        }
    }

}
add_action( 'admin_notices', 'wds_admin_notices' );


if ( ! function_exists( 'wd_send_maintenance_notification' ) ) {

    function wd_send_maintenance_notification(){

        if( wds_domain_exculded() ) {
            // Bail if excloded
            return;
        
        } else {
            
              $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
             $next_send = wd_get_next_schedule();

            // PROOF CHECK TO PREVENT DUPLCATES 
            $proof = ( $new_date <= $next_send );

            // ARE THERE ANY UPDATES
            $count = WEBDOGS::webdogs_maintenance_updates( true );

            // IF THE DATE IS A MATCH 
            // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
            if( $count && $proof ) {

                // SAVE THE PROOF SO IF WE CHECK AGAIN
                // THE PROOF WILL MATCH AND PASS
                update_option( 'wd_maintenance_notification_proof', $new_date );

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );

                wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

                // DO NOTIFICATION
                extract( wd_get_notification( of_get_option( 'active_maintenance_customer', false ) ) );
                
                if(!function_exists('wp_mail')) include_once( ABSPATH . 'wp-includes/pluggable.php');

                wp_mail( $to, $subject, $message, $headers );
            } 
        }
    }
}
add_action( 'wds_scheduled_notification', 'wd_send_maintenance_notification' );

if ( ! function_exists( 'wd_send_test_maintenance_notification' ) ) {

    function wd_send_test_maintenance_notification( $force = false ){
        
        if( ! is_admin() ) return;

            $timezone = date_default_timezone_get();

           $report = "\n\nTimezone: %s\nNow: %s\nNext: %s\nUpdates: %s";

         $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
        $next_send = wd_get_next_schedule();

            // ARE THERE ANY UPDATES
            $count = WEBDOGS::webdogs_maintenance_updates( true );

        if( wds_domain_exculded() && ! $force ) { 

            $message = "Maintainance Notification are excluded for this enviroment: %s";
            $message = nl2br( sprintf( $message.$report, implode(',', wds_domain_exculded() ), $timezone, date('F j, Y', $new_date ), date('F j, Y', $next_send ), $count ) );
            // wp_die( sprintf( $message, implode(',', wds_domain_exculded() ) ) ); 
        } else {

            // PROOF CHECK TO PREVENT DUPLCATES 
            $proof = ( $new_date >= $next_send );

            // IF THE DATE IS A MATCH 
            // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
            if( ( $count && $proof && !$force ) || ( $count && $force ) ) {

                $passed = "";
                // $passed = $new_date.'.'.$next_send.'.'.$count;

                // DO NOTIFICATION
                extract( wd_get_notification( of_get_option( 'active_maintenance_customer', false ) ) );
                
                if(!function_exists('wp_mail')) include_once( ABSPATH . 'wp-includes/pluggable.php');

                $message = ( wp_mail( $to, $subject, $message.$passed, $headers ) ) ? 'Maintainance notification sent.' : 'Maintainance notification not sent.';

                $message = nl2br( sprintf( $message.$report, $timezone, date('F j, Y', $new_date ), date('F j, Y', $next_send ), $count ) );

            } else {

                $prev_date = get_option( 'wd_maintenance_notification_proof', false );

                $message = ( $prev_date ) ? sprintf( "Maintainance Notification last sent: %s.", date('F j, Y', $prev_date ) ) : "Maintainance Notification pending." ;
                $message = nl2br( sprintf( $message.$report, $timezone, date('F j, Y', $new_date ), date('F j, Y', $next_send ), $count ) );
            }
        }  

        wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

        update_option( 'maintenance_notification_test', $message ); 

        wp_safe_redirect( admin_url( 'admin.php?page=options-framework' ) );
        
        exit;

    }

}
add_action( 'wds_test_maintenance_notification', 'wd_send_test_maintenance_notification', 10, 1 );

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


if ( ! function_exists( 'webdogs_activation' ) ) {

    function webdogs_activation() {

        deactivate_plugins( array(
        WP_PLUGIN_DIR . '/webdogs-support-dashboard-widget/webdogs-support-dashboard-widget.php',
        WP_PLUGIN_DIR . '/login-logo-svg/login-logo.php',
        WP_PLUGIN_DIR . '/login-logo/login-logo.php' ) );

        function webdogs_init_roles() {
            $admin = get_role('administrator');
            $admin->add_cap('manage_support_options');
        }

        function webdogs_init_schedule() { wds_create_daily_notification_schedule(); }

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
        //   ADD SUPPORT ROLES       
        //                           
        /////////////////////////////
        webdogs_init_roles();////////
        /////////////////////////////
        //                           
        //   ADD NOTIFICATION CRON   
        //                           
        /////////////////////////////
        webdogs_init_schedule();/////
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

        wp_clear_scheduled_hook( 'wd_create_daily_notification');
        wp_clear_scheduled_hook( 'wds_scheduled_notification'  );
    }
}
register_deactivation_hook(__FILE__, 'webdogs_deactivation');