<?php

defined( 'WPINC' ) or die;


if(!function_exists( 'is_webdog' )) {

    function is_webdog( $user ) {
        if( null === $user || ( isset( $user ) 
              && ! ( $user instanceof WP_User ) ) ) {
                     $user = wp_get_current_user(); }
        if( ! $user->exists() ) { return false; }
        return ( is_numeric( stripos( $user->user_email, WEBDOGS_DOMAIN ) ) ); }
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



if ( ! function_exists( 'format_scss_keys' ) ) {

    function format_scss_keys( $array ){ 
        return array_values( json_decode( str_replace("-", "_", json_encode( $array ) ), true ) ); 
    }
}




if ( ! function_exists( 'wds_strip_nonalpha' ) ) {
    /**
     * Remove all characters except letters.
     *
     * @param string $string
     * @return string
     */
    function wds_strip_nonalpha( $string ) {
        return preg_replace( "/[^a-z]/i", "", $string );
    }
}



if ( ! function_exists( 'wds_must_use_admin_color' ) ) {

    function wds_must_use_admin_color(){
        $scheme = wds_get_option( 'admin_color_scheme', array() );
        return ( isset( $scheme['must_use'] ) && 'on' === $scheme['must_use'] );
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



if ( ! function_exists( 'wds_domain_exculded' ) ) {

    function wds_domain_exculded(){
        $exclude = array();
        
        $domain_string = wds_get_option( 'exclude_domain', false );
        
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

        if(!function_exists('wds_extra_domain_strings')) require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/options.php';

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



if ( ! function_exists( 'wds_show_domain_flags' ) ) {

    function wds_show_domain_flags(){

        $domain_flags      = wds_get_domain_flags();
        $show_when         = wds_get_option('show_domain_flags', 'yes');
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
            $domain_flags = array_map( 'wds_strip_nonalpha', $domain_flags );
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

        $clear = ( 'toplevel_page_webdogs-support' !== $current_screen->base || did_action( 'webdogs_do_clear_cache' ) || wds_is_staging_site() ) ? FALSE : TRUE ;
        
        if( $clear ){

            add_settings_error( 'webdogs-support', 'clear_cache', __( 'HTML-page-caching, CDN (statics), and WordPress Object/Transient Caches have been cleared.', 'webdogs-support' ), 'updated fade' ); 
            add_action( 'shutdown', 'webdogs_clear_cache' );
        }
    }
}



if ( ! function_exists( 'webdogs_clear_cache' ) ) {
    // CLEAR WPE CACHE
    function webdogs_clear_cache() {

        if( wds_is_production_site() ) {

            wp_clear_scheduled_hook( 'wds_scheduled_notification' );

            Webdogs_Support_Maintenance_Notifications::init();

            if( ! Webdogs_Support_Maintenance_Notifications::$deactivated ){
                $next_send = Webdogs_Support_Maintenance_Notifications::$scheduled;
                wp_schedule_single_event( $next_send, 'wds_scheduled_notification' ); 
            }

            // Refresh our own cache 
            // (after CDN purge, in case that needed to clear before we access new content)
            if( class_exists( 'WpeCommon' ) ) {
                WpeCommon::purge_memcached();
                WpeCommon::clear_maxcdn_cache();
                WpeCommon::purge_varnish_cache();
            }

            $prev_date = wp_next_scheduled( 'wds_scheduled_notification' );

        }
    }
}

?>