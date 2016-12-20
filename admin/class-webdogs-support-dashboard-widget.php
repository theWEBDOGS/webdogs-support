<?php 

defined( 'WPINC' ) or die;

/**
 * Webdogs Support Dashboard Widget
 */

class Webdogs_Support_Dashboard_Widget
{

    /**
     * Register dashboard widget
     * @return void
     */
    public static function add_dashboard_widget() {
        wp_add_dashboard_widget( 'webdogs_support_widget', WEBDOGS_TITLE, array( __CLASS__, 'dashboard_widget' ));
    }

    /**
     * Display widget
     * @return void
     */
    public static function dashboard_widget() { 
         if(!function_exists('wp_get_current_user') ) include_once( ABSPATH . 'wp-includes/pluggable.php' );

        $current_user = wp_get_current_user(); 

        ?><div style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;text-size-adjust:100%;-webkit-text-size-adjust:100%;min-width:100%;width:auto;position:relative;" id="webdogs_support_intro">
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
        </div><?php
    }

    /**
     * load JS and the data (admin dashboard only)
     * @param  string $hook current page
     * @return void
     */
    public static function enqueue_scripts( $hook ) {

        if( 'index.php' === $hook ) {
            add_action( 'admin_footer', array( __CLASS__, 'webdogs_dashboard_javascript'));
        }
    }

    /**
     * @return void
     */
    public static function webdogs_dashboard_javascript() { ?>

        <script type="text/javascript">
            jQuery(function($) {

                $('#webdogs_support_widget').find('.hndle').css({"background-color":"#666", "font-family":"'Helvetica Neue',Helvetica,Arial,sans-serif"}).html('<span style="color:#FFFFFF;text-decoration:none;font-weight:bold;text-transform:uppercase;padding-top: 0;padding-bottom: 0;display: inline-block;position: relative; vertical-align: middle;margin-top: -3px;line-height: 13px;" title="WEBDOGS" href="http://webdogs.com/" target="_blank"><b>'
                    +'<img style="border-width:0px;margin-right:5px;margin-left:0px;margin-top: -13px;margin-bottom: 0;display: inline;vertical-align: middle;line-height: 26px;top: 5px;position: relative;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACMAAAAjCAQAAAC00HvSAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAAAmJLR0QAQjJfA44AAAAHdElNRQfeDBYLLDqnCm9AAAADv0lEQVRIx5VWCU8TQRTe31aQQ9BqlcuYCBRRbpRGDIcWjJVoUJFLBFEUFSUqigoFBQkYwUA0GAyJByB4EVAwSjAIn99Op8tuu21wJu3uvvfm25l3fG8VixJ8RsKKLQhDcKuAilRUox1dcKOTv27+bqEUNmwYJoTmbjxFPXIQLZeFIoHS2xhAC5KxAZh0POf77cI0gkscKEYBMrEDHjgXRnBNgw8A04hhZNAkDOUYwjzWxxLe4yYSxfJavEIaAsCE0BcPEUp1Babl4kXMaUCr/C3jEeJpkYXXOAxTmHY60aLE4IVuD/08kncU0y/q+A4n7RIwhoPwg6nHXQqT8Q368ZKySXG3wLtTmrxBAL2Rh9RgsnnaTdiNWRjHAsJxTNy95YLjOk01n/PxTA8TxuikMsXG4D9UD0zxOij2uqrzVC4lrTgNDaYMD/hwBWajiRoXr9eF+X2dZgZRiMEo/yVMH3YxP5dMYXLgcX+SuFp1kQNqKGtmJgmYdKa8RblkAjGBo9gmUmB91ur0XxGJPejzwDThLG8++0C8wwnGoYQ+syMfF5EnwQ4YrAopHWShUNFDw0SsGdQddHgCK8vO9/0QkklZ5YUGuzuUtaEIjFI/DVwG5UeEkB7GsY9G4fDkzRr3pcIYLUcpO0kfKTYTzwyKUBbJY+wX+/klSSLbYDlDWR6uQonjAdRF3rHMzD3H/WXqHFtA+R9GU70PZyKujzkmbSZuQIk1wIyI9HaSHoyVX4EVOtrr5FUdTCiyVBgrHlPdIMVlwvQCqgwwDpLHXx10h86Lakk0gfQwQBIqleICUYBfEKctiUYvpsguDsbMK4vBT13p1qACAjuL51YDvsIgJ8h3eGcyflNyxOeQzRJGpZZOEgZFlWiU5TfPN0ayenqYu+tLXJIY9DOaMVKHg6kxzORQVN5Q07mKwllEmNB1HDUVfvIsSqcZp1xypixNdVvRJMwVVog5TKuJvI2JYVHcggNlCFX6qaZ5t4m5nflcaSIP417SSLkh0NivHcf5MESgAaLH87RRWmWHB+mYfZJItBCOM8hWfJCZvEg/TdBn+UGbbiMboA+lO5jBm7GTcMbxBNsDQJWwk0XBr8GcISNvZay6fIAmJfMZp5NNIA6mXbOMTSwFaika9/SJnGsEOc/8tSFg880gUIMkhHam2LIEcuuW7OWu23wyzG+zUbjMaNXJ99vIfxmkr3h4QuwgeJ+ovA1838SSewfYz+vYcNPpmRQmQTnpoJd+c+K/PpMsShKptYVgbs57BD4U8CPJovwDRRo5ALFcUX0AAAAldEVYdGRhdGU6Y3JlYXRlADIwMTQtMTItMjJUMTE6NDQ6NTgrMDE6MDBej4ixAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE0LTEyLTIyVDExOjQ0OjU4KzAxOjAwL9IwDQAAAABJRU5ErkJggg==" alt="" width="26" height="26">'
                    +'<span style="text-decoration:none;color:#FFFFFF;vertical-align:middle;display: inline-block;font-size: 13px;margin-right: 4px;">WEBDOGS </span></b></span>'
                    +'<span style="color: #D0F2FC; text-decoration: none;display: inline-block; vertical-align: middle;margin: -1px 0 0 0;padding: 0;font-size: 13px;line-height: 13px;">Support v<?php echo WEBDOGS_VERSION; ?></span></h3>');
                $('#webdogs_support_form').live('submit', function(e) {
                    var username    = $( this ).find('#webdogs_support_form_username').val();
                    var email       = $( this ).find('#webdogs_support_form_email').val();
                    var subject     = $( this ).find('#webdogs_support_form_subject').val();
                    var message     = $( this ).find('#webdogs_support_form_message').val();
                    var data = {
                        'action'  : 'webdogs_result_dashboard',
                        'username': username,
                        'email'   : email,
                        'subject' : subject,
                        'message' : message };

                    $.post(ajaxurl, data, function(response) {
                        $intro     = $( "#webdogs_support_intro" ).html( response );
                        $container = $( "#webdogs_support_form_wrapper" ).detach();
                    });
                    e.preventDefault();
                })
                $('#webdogs_support_form_reset').live('click', function(e) {
                    var data = {'action': 'webdogs_reset_dashboard'};
                    $.post(ajaxurl, data, function(response) {
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
    public static function result_dashboard() {

        $site       = get_bloginfo('name');
        $username   = sanitize_text_field( $_POST['username'] );
        $email      = sanitize_email( $_POST['email'] );
        $message    = sanitize_text_field( $_POST['message'] );
        $to         = WEBDOGS_SUPPORT;

        $subject = html_entity_decode( sprintf( "[%s] %s - %s", WEBDOGS_TITLE, $site, sanitize_text_field( $_POST['subject'] ) ) );

        $headers  = "From: \"". $username ."\" <". $email .">\r\n"; 
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        if(!function_exists('wp_mail')) include_once( ABSPATH . 'wp-includes/pluggable.php');

        if ( wp_mail( $to, $subject, $message, $headers ) ) {
           echo "<p style='color:#2a8d9d;'><b>Request Sent</b></p> \r\n";
           echo "<p>A ".WEBDOGS_TITLE." agent will respond to your case via email. To submit another request, click the Reset Form button.</p> \r\n";
           echo "<input id='webdogs_support_form_reset' type='button' class='button' value='Reset Form' /> \r\n";
        } else {
           echo "<p style='color:#2a8d9d;'><b>Error sending</b></p> \r\n";
           echo "<p>Something went wrong. Please, use your email service to notify <a href='mailto:support@webdogs.com' target='_blank' style='color: #D0F2FC;'>support@webdogs.com</a>.</p> \r\n";
        }
        die();
    }

    /**
     * @return void
     */
    public static function reset_dashboard() {
        Self::dashboard_widget();
        die();
    }

}