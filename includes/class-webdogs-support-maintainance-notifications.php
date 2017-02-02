<?php

defined( 'WPINC' ) or die;

/**
 * WEBDOGS Maintenance Notification
 */

class Webdogs_Support_Maintenance_Notifications {

    public static $deactivated;

    public static $freq;
    public static $offset;
    public static $previous;
    public static $scheduled;

    public static $count;
    public static $updates;

    protected static $test;
    protected static $force;
    protected static $notice;


    /**
     * Initialize globals and magic hooks.
     */
    public static function init() {

        Self::$test  = ! empty( $_GET['wds_send_maintenance_notification'] ) && $_GET['wds_send_maintenance_notification'] === "test";
        Self::$force = ! empty( $_GET['force_send'] );

        //notice and deactivated
        Self::set_notice();

        //bail if deactivated and not a test
        if( Self::$deactivated && ! Self::$test ) return;

        //freq, offset, previous and scheduled
        Self::set_next_schedule(); 

        if( ( defined('DOING_CRON') && DOING_CRON ) || Self::$test ) {
    
            if ( ! function_exists( 'wp_version_check' ) )         include_once ABSPATH . 'wp-includes/update.php'; 
            if ( ! function_exists( 'wp_prepare_themes_for_js' ) ) include_once ABSPATH . 'wp-admin/includes/theme.php';
            if ( ! function_exists( 'get_core_updates' ) )         include_once ABSPATH . 'wp-admin/includes/update.php';
            if ( ! function_exists( 'wp_mail' ) )                  include_once ABSPATH . 'wp-includes/pluggable.php';
            if ( ! function_exists( 'wds_domain_exculded' ) )      include_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/functions-webdogs-support-common.php';
            if ( ! function_exists( 'wds_base_strings' ) )         include_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/options.php';
            
            //count and updates
            Self::set_maintenance_updates();

            if( Self::$test ) 
                do_action( 'wds_test_maintenance_notification' );
        }
    }

    public static function create_daily_notification_schedule( $clear = false ){

        if( ! wp_next_scheduled( 'wds_scheduled_notification' ) || $clear ){ 
            // wp_clear_scheduled_hook( 'wds_scheduled_notification' );

            $timestamp = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

            //Schedule the event for right now, then to repeat daily using the hook 'wds_scheduled_notification'
            wp_schedule_event( $timestamp, 'daily', 'wds_scheduled_notification' );

            return $timestamp;
        }
    }
    
    /**
     * @return void
     */
    private static function set_maintenance_updates( $return_count = false ) {
        
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
            'note'    => wds_get_option('maintenance_notes', '' ) );
        
         Self::$count  = sizeof($updates['plugins']) + sizeof($updates['themes']) + $cor;

         if( $return_count == true ) {
            return Self::$count;
        }
        $report  = sprintf( 
            translate_nooped_plural( wds_base_strings( 'maintainance_updates' ), Self::$count, 'webdogs-support' ),
             Self::$count );
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
        Self::$updates = $report;
        return $report;
    }

    public static function report_schedule_changes( $clean_options = array() ) {

        if( empty( $clean_options ) ) return;

        Self::$freq   = $clean_options['maintenance_notification_frequency'];
        Self::$offset = $clean_options['maintenance_notification_offset'];
        Self::$notice = $clean_options['active_maintenance_customer'];

        Self::$deactivated = ( 'deactivated' === Self::$notice );

        /////////////////////////////
        // REPORT SCHEDULE CHANGES //
        /////////////////////////////

        $next_send = Self::get_next_schedule( FALSE, FALSE ); 

        $timezone = date_default_timezone_get() . wp_timezone_override_offset();

         $message = "";
          $report = "\n\nNotice: %s\nStatus: %s\nTimezone: %s\nNow: %s\nNext: %s";

        $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

        $next_scheduled = wp_next_scheduled( 'wds_scheduled_notification' );

        if( $next_scheduled ) {

            if( $next_scheduled !== $next_send && ! Self::$deactivated ) {

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );
                wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

                $message = sprintf( wds_base_strings( 'maintainance_reschedule' ), date('F j, Y', $next_scheduled ), date('F j, Y', $next_send ) );
            }
            elseif( Self::$deactivated ) {

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );

                $message = wds_base_strings( 'maintainance_deactivate' );
            }
        }
        elseif( ! Self::$deactivated ) {

            wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

            $message = sprintf( wds_base_strings( 'maintainance_schedule' ), date('F j, Y', $next_send ) );
        }  

        if( ! empty( $message ) ){

            $message = nl2br( sprintf( $message.$report, ( Self::$deactivated ? 'None' : Self::$notice ), ( Self::$deactivated ? 'Deactivated':'Scheduled'), $timezone, date('F j, Y', $new_date ), ( Self::$deactivated ? 'Unscheduled': date('F j, Y', $next_send ) ) ) );
            
            add_settings_error( 'webdogs-support', 'maintenance-notification-changes', $message, 'update-nag fade' );
        }
    }


    public static function send_test_maintenance_notification(){
        
        if( ! is_admin() || ! Self::$test ) return;

            Self::set_notice();
            Self::set_next_schedule();
            Self::set_maintenance_updates();

             $timezone = date_default_timezone_get() . wp_timezone_override_offset();
              $message = "";
               $report = "\n\nNotice: %s\nStatus: %s\nTimezone: %s\nNow: %s\nNext: %s\nUpdates: %s";

             $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
            $next_send = Self::$scheduled;

            // ARE THERE ANY UPDATES
            $count = Self::$count;

        if( wds_domain_exculded() && ! Self::$force ) { 

            $message = sprintf( wds_base_strings( 'maintainance_excluded' ), implode(',', wds_domain_exculded() ) );

        } elseif( Self::$deactivated ) { 

            $message = wds_base_strings( 'maintainance_deactivated' );

        } else {

            // PROOF CHECK TO PREVENT DUPLCATES 
            $proof = ( $new_date >= $next_send );

            // IF THE DATE IS A MATCH 
            // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
            if( ( $count && $proof && !Self::$force ) || ( $count && Self::$force ) && ! Self::$deactivated ) {

                $passed = "";
                // $passed = $new_date.'.'.$next_send.'.'.$count;

                // DO NOTIFICATION
                extract( Self::get_notification() );

                $message = ( wp_mail( $to, $subject, $message.$passed, $headers ) ) ? wds_base_strings( 'maintainance_send' ) : wds_base_strings( 'maintainance_fail' );

            } else {

                $prev_date = get_option( 'wds_maintenance_notification_proof', false );

                $message = ( $prev_date ) ? sprintf( wds_base_strings( 'maintainance_sent' ), date('F j, Y', $prev_date ) ) : sprintf( wds_base_strings( 'maintainance_scheduled' ), date('F j, Y', $next_send ) );
            }
        }  

        /////////////////////////////
        // REPORT SCHEDULE CHANGES //
        /////////////////////////////

        $next_scheduled = wp_next_scheduled( 'wds_scheduled_notification' );

        if( $next_scheduled ) {

            if( $next_scheduled !== $next_send && ! Self::$deactivated ) {

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );
                wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

                $message .= "\r\n";
                $message .= sprintf( wds_base_strings( 'maintainance_reschedule' ), date('F j, Y', $next_scheduled ), date('F j, Y', $next_send ) );
            }
            elseif( Self::$deactivated ) {

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );

                $message .= "\r\n";
                $message .= wds_base_strings( 'maintainance_deactivate' );
            }
        }
        elseif( ! Self::$deactivated ) {

            wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

            $message .= "\r\n";
            $message .= sprintf( wds_base_strings( 'maintainance_schedule' ), date('F j, Y', $next_send ) );
        }  

        $message = nl2br( sprintf( $message.$report, ( Self::$deactivated ? 'None' : Self::$notice ), ( Self::$deactivated ? 'Deactivated':'Scheduled'), $timezone, date('F j, Y', $new_date ), ( Self::$deactivated ? 'Unscheduled': date('F j, Y', $next_send ) ), $count ) );
        
        update_option( 'wds_maintenance_notification_test', $message ); 

        wp_safe_redirect( admin_url( 'admin.php?page=webdogs-support' ) );
        
        exit;

    }

    /**
     * MAIN SEND METHOD
     */
    public static function send_maintenance_notification(){

        Self::init();

        // Bail if excloded or deactivated
        if( wds_domain_exculded() || Self::$deactivated ) {

            return;
        
        } else {
            
              $new_date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
             $next_send = Self::get_next_schedule( FALSE, TRUE );

            // PROOF CHECK TO PREVENT DUPLCATES 
            $proof = ( $new_date <= $next_send );

            // ARE THERE ANY UPDATES
            $count = Self::$count;

            // IF THE DATE IS A MATCH 
            // AND THE PROOFS DO NOT ->> SEND NOTIFICATION EMAIL
            if( $count && $proof ) {

                // SAVE THE PROOF SO IF WE CHECK AGAIN
                // THE PROOF WILL MATCH AND PASS
                update_option( 'wds_maintenance_notification_proof', $new_date );

                wp_clear_scheduled_hook( 'wds_scheduled_notification' );

                wp_schedule_single_event( $next_send, 'wds_scheduled_notification' );

                // DO NOTIFICATION
                extract( Self::get_notification() );

                wp_mail( $to, $subject, $message, $headers );
            } 
        }
    }

    private static function get_notification(){

        $home_url  = parse_url( home_url() );
        $site_url  = $home_url["host"];
        $email_to  = wds_get_option( 'on_demand_email', get_bloginfo( 'admin_email' ) );
              $to  = array_map( 'trim', explode(',', $email_to ));

        $notice    = wds_base_strings( Self::$notice );

        switch( Self::$notice ) {

            // ACTIVE MAINTAINANCE SUPPORT
            case 'active_maintainance_notification':
            return array( 
                'to' => WEBDOGS_SUPPORT,
                'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_url ) ),
                'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_url, Self::$updates ) ),
                'headers' => "Reply-To: ".WEBDOGS_TITLE." <".WEBDOGS_SUPPORT.">\r\n" );
                 break;

            // ON DEMAND SUPPORT
            case 'on_demand_maintainance_notification':
            default:
            return array(
                'to' => $to,
                'subject' => wp_specialchars_decode( sprintf( $notice['subject'], $site_url ) ),
                'message' => wp_specialchars_decode( sprintf( $notice['message'], $site_url, Self::$updates ) ),
                'headers' => "Reply-To: ".WEBDOGS_TITLE." <".WEBDOGS_SUPPORT.">\r\n" );
                 break;
         }
    }

    private static function set_notice(){

        Self::$notice = wds_get_option( 'active_maintenance_customer', 'on_demand_maintainance_notification' );

        Self::$deactivated = ( 'deactivated' === Self::$notice );

    }

    private static function set_next_schedule(){

           $year = date('Y');
          $month = date('n');
            $day = date('j');

           $freq = absint( wds_get_option( 'maintenance_notification_frequency', 3 ) );
         $offset = absint( wds_get_option( 'maintenance_notification_offset',    1 ) );
           
           $time = absint( $day ) > $offset 

                ? mktime(0, 0, 0, $month, $offset, $year ) + wp_timezone_override_offset()
                : mktime(0, 0, 0, date('n', strtotime('first day of previous month') ), $offset, date('Y', strtotime('first day of previous month') ) ) + wp_timezone_override_offset();

         $prev_optn = get_option( 'wd_maintenance_notification_proof', false ); delete_option( 'wd_maintenance_notification_proof' );
         $prev_sent = ( $prev_optn ) ? $prev_optn : get_option( 'wds_maintenance_notification_proof', $time );
        
        $prev_month = date( 'n', $prev_sent );
         $prev_year = date( 'Y', $prev_sent );
         $next_send = "";

        $month = ( $year === $prev_year 
               && $month === $prev_month 
               && absint( $day ) > $offset ) 

                    ?   $month 
                    : --$month ;

        $freq = ( 0 === $freq ) ? 3 : $freq ;
        
        $active_this_year = Self::get_active_dates( $freq, $offset, $month, $year );
        $active_next_year = Self::get_active_dates( $freq, $offset, 1,  1 + $year );

        if( sizeof($active_this_year) > 0 ) $next_send = $active_this_year[0];
        elseif( sizeof($active_next_year) > 0 ) $next_send = $active_next_year[0];

        $schedule = array( 
            'scheduled' => $next_send, 
            'previous'  => $prev_sent,  
            'previous_month' => $prev_month,  
            'previous_year'  => $prev_year, 
            'offset' => $offset,  
            'freq'   => $freq,  
            'active_this_year' => $active_this_year, 
            'active_next_year' => $active_next_year );

        Self::$freq      = $schedule['freq'];
        Self::$offset    = $schedule['offset'];
        Self::$previous  = $schedule['previous'];
        Self::$scheduled = $schedule['scheduled'];
    }

    private static function get_next_schedule( $return_array = false, $clear = false ){

              $year = date('Y');
             $month = date('n');
               $day = date('j');

              $freq = Self::$freq;
            $offset = Self::$offset;
         $prev_sent = Self::$previous;

        $prev_month = date( 'n', $prev_sent );
         $prev_year = date( 'Y', $prev_sent );
         $next_send = "";

        $month = ( $year === $prev_year 
               && $month === $prev_month 
               && absint( $day ) > absint( $offset ) ) 
                    ?   $month 
                    : --$month ;

        $active_this_year = Self::get_active_dates( $freq, $offset, $month, $year );
        $active_next_year = Self::get_active_dates( $freq, $offset, 1,  1 + $year );

        if( sizeof($active_this_year) > 0 ) $next_send = $active_this_year[0];
        elseif( sizeof($active_next_year) > 0 ) $next_send = $active_next_year[0];

        ////////////////////////
        // CLEAR NOTIFICATION //
        ////////////////////////
        if ( $clear ) wp_clear_scheduled_hook( 'wds_scheduled_notification' ); 

        ////////////////////////
        //   RETURN SCHEDULE  //
        ////////////////////////
        if( $return_array ) return array( 'scheduled' => $next_send, 'previous' => $prev_sent,  'previous_month' => $prev_month,  'previous_year' => $prev_year, 'offset' => $offset,  'freq' => $freq,  'active_this_year' => $active_this_year, 'active_next_year' => $active_next_year );
        else return $next_send;

    }

    /**
     * wds_l10n
     */
    public static function maintenance_l10n( $l10n = array() ) {
        return $l10n + array(
            'notification_deactivated' => __( 'Notification deactivated', 'webdogs-support' ),
            'next_notification' => __( 'Next notification', 'webdogs-support' )
        );
    }

    public static function admin_notices() {

        $message = get_option( 'wds_maintenance_notification_test', false );

        if ( $message ) {

            delete_option( 'wds_maintenance_notification_test' );
            add_settings_error( 'webdogs-support', 'maintenance-notification-test', $message, 'update-nag fade' );
        }
    }

    public static function get_active_dates( $freq, $day, $month, $year ) {
        
        $date = mktime(0, 0, 0, date("n"), date("j"), date("Y"));

        $active = array();

        $n = 0;
        for ($i = $month; $i <= 12; $i++) {

            $parsed = mktime(0, 0, 0, $i, $day, $year );

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