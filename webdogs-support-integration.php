<?php
/*
Plugin Name: WEBDOGS Support + Maintenance
Plugin URI: https://github.com/theWEBDOGS/webdogs-support-integration
Description: WEBDOGS Support + Maintenance Configuration Tools: scheduled maintenance notifications, login page customizations, base plugin recommendations and more.
Version: 2.0.2
Author: WEBDOGS Support Team
Author URI: http://WEBDOGS.COM
License: GPLv2
*/


if (!class_exists('WEBDOGS')) {

    define( 'WEBDOGS_TITLE', "WEBDOGS Support" );
    define( 'WEBDOGS_SUPPORT', "support@webdogs.com" );
    define( 'WEBDOGS_DOMAIN', "webdogs.com" );
    define( 'WEBDOGS_VERSION', "2.0.2" );

    /////////////////////////////////////////////////
    //
    // The class is useless if we do not start it, 
    // when plugins are loaded let's start the class.
    //
    add_action ('plugins_loaded', 'WEBDOGS');

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
                    
            require_once plugin_dir_path( __FILE__ ) . '/options-framework/options-framework.php';

            add_action( 'set_current_user',                     array(&$this,'webdogs_add_user_capability'          ));

            add_action( 'wp_dashboard_setup',                   array(&$this,'webdogs_add_dashboard_widget'         ));
        
            add_action( 'admin_enqueue_scripts',                array(&$this,'webdogs_enqueue_scripts'              ));
        
            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_result_dashboard',     array(&$this,'webdogs_result_dashboard_callback'    )); 

            add_action( 'wp_ajax_webdogs_reset_dashboard',      array(&$this,'webdogs_reset_dashboard_callback'     )); 
            
            add_filter( 'the_generator',                        array(&$this,'complete_version_removal'             ));

        }

        /**
         * Register user capability
         * @return void
         */
        function webdogs_add_user_capability() {
            $user = wp_get_current_user();
            if( ! current_user_can( 'manage_support' ) && is_webdog( $user ) ){
                $user->add_cap( 'manage_support' );
            }
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
        static function webdogs_maintenance_updates() {
            if ( ! function_exists( 'get_core_updates' ) ) {
                require_once ABSPATH . 'wp-admin/includes/update.php';
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

    if(!function_exists('is_webdog')){

        function is_webdog( $user ){

            if( ! isset($user) || ( isset($user) && ! ( $user instanceof WP_User ) ) ){
                $user = wp_get_current_user();
            }
            if( ! $user->exists() ) return false;

            return ( stripos( $user->user_email, WEBDOGS_DOMAIN ) !== false );
        }

    }

    //On plugin activation schedule our daily database backup 

    register_activation_hook( __FILE__, 'wd_create_daily_notification_schedule' );


    /**
     * Helper function to return the theme option value.
     * If no value has been saved, it returns $default.
     * Needed because options are saved as serialized strings.
     *
     * Not in a class to support backwards compatibility in themes.
     */

    if ( ! function_exists( 'of_get_option' ) ) :

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

        $site_name = get_bloginfo( 'name', 'display' );
        $site_url  = trailingslashit( get_bloginfo( 'url', 'display' ) );
        $updates   = WEBDOGS::webdogs_maintenance_updates();
        $email_to  = of_get_option( 'on_demand_email', get_bloginfo( 'admin_email' ) );
              $to  = array_map( 'trim', explode(',', $email_to ));

        return ( $active )
       

        ?// ACTIVE MAINTAINANCE SUPPORT
        array( 
            'to' => WEBDOGS_SUPPORT,
            'subject' => wp_specialchars_decode( "Scheduled Maintenance for {$site_name} | {$site_url}"),
            'message' => wp_specialchars_decode( "The following updates are available for {$site_name} website: \n\r{$updates}

 "),
            'headers' => "Reply-To: \"".WEBDOGS_TITLE."\" <".WEBDOGS_SUPPORT.">\r\n" )


        
        :// ON DEMAND SUPPORT
        array(
            'to' => $to,
            'subject' => wp_specialchars_decode( "WordPress Updates are Available for {$site_name} | {$site_url}"),
            'message' => wp_specialchars_decode( "The following updates are available for {$site_name} website. \n\rIf you would like WEBDOGS to install these updates, please reply to this email. \n\r{$updates} \n\rIf you would like WEBDOGS to install these updates, please reply to this email.

 "),
            'headers' => "Reply-To: \"".WEBDOGS_TITLE."\" <".WEBDOGS_SUPPORT.">\r\n" ) ;


    }


    add_action( 'wd_create_daily_notification', 'wd_send_maintenance_notification' );

    function wd_send_maintenance_notification( $test = false ){

    	wd_create_daily_notification_schedule();
        //bail is not a mantainance account
        // if( ! defined('DB_NAME') ){ return; }
        // if(   defined('DB_NAME') && stripos( DB_NAME, 'snapshot' ) !== false ) { return; }
        if( stripos( of_get_option( 'exclude_domain' ), site_url() ) !== false ) { return; }


        $freq = of_get_option( 'maintenance_notification_frequency', 4  );
         $day = of_get_option( 'maintenance_notification_offset',   '1' );

        $mon_date = date('n');
        $day_date = date('j');

        // MATCH THE RULE
        $check = ( $mon_date % $freq === 0 && $day == $day_date );

        // PROOF CHECK TO PREVENT DUPLCATES 
        // SAVE THE LAST MATCHING DATE AND CHECK TO SEE IF WE ALREADY SENT THE NOTIFICATION
         $new_proof = $mon_date ."%" .$freq . "=" . ($mon_date % $freq) ."|". $day ."=". $day_date;
        $prev_proof = get_option( 'wd_maintenance_notification_proof' );
		
		$text = "";
        $text .= "prev:" . $prev_proof . "<br>";
        $text .= "new: " . $new_proof . "<br>";

        $proof = ( $new_proof !== $prev_proof );
        // echo 'Maintainance Notification Sent.';

        // IF THE DATE IS A MATCH 
        // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
        if( $check && $proof ) {

            // SAVE THE PROOF SO IF WE CHECK AGAIN
            // THE PROOF WILL MATCH AND PASS
            update_option( 'wd_maintenance_notification_proof', $new_proof );

            // DO NOTIFICATION
            extract( wd_get_notification( of_get_option( 'active_maintenance_customer', false ) ) );
            
			if(!function_exists('wp_mail')) include_once( ABSPATH . 'wp-includes/pluggable.php');

            $text .= ( wp_mail( $to, $subject, $message, $headers ) ) ? 'Maintainance Notification SENT.' : 'Maintainance Notification NOT SENT.';

            add_settings_error( 'options-framework', 'notification_sent', 'Maintainance Notification Sent.', 'webdogs-nag' );

            // wp_die($text);
        }

        $text .= 'Maintainance Notification ALREADY SENT.';
        // wp_die($text);
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

    endif;

}
