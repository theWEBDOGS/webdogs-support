<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !function_exists('wds_locate_template') ) {
	/**
	 * Locate template.
	 *
	 * Locate the called template.
	 * Search Order:
	 * 1. /themes/theme/camp/$template_name
	 * 2. /themes/theme/$template_name
	 * 3. /plugins/camp/templates/$template_name.
	 *
	 * @since 1.0.0
	 *
	 * @param   string  $template_name  Template to load.
	 * @param   string  $template_path  Path to templates.
	 * @param   string  $default_path   Default path to template files.
	 * @return  string                  Path to the template file.
	 */
	function wds_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		// Set variable to search in camp folder of theme.
		if ( ! $template_path ) :
			$template_path = WEBDOGS_SUPPORT_DIR;
		endif;

		// Set default plugin templates path.
		if ( ! $default_path ) :
			$default_path = WEBDOGS_SUPPORT_DIR_PATH . 'includes/templates/'; // Path to the template folder
		endif;

		// Search template file in theme folder.
		$template = wds_locate_template( array(
			$template_path . $template_name,
			$template_name
		) );

		// Get plugins template file.
		if ( ! $template ) :
			$template = $default_path . $template_name;
		endif;

		return apply_filters( 'wds_locate_template', $template, $template_name, $template_path, $default_path );

	}
}


if( !function_exists('wds_get_template') ) {
	/**
	 * Get template.
	 *
	 * Search for the template and include the file.
	 *
	 * @since 1.0.0
	 *
	 * @see wcpt_locate_template()
	 *
	 * @param  string  $template_name  Template to load.
	 * @param  array   $args           Args passed for the template file.
	 * @param  string  $template_path  Path to templates.
	 * @param  string  $default_path   Default path to template files.
	 */
	function wds_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

		if ( is_array( $args ) && isset( $args ) ) :
			extract( $args );
		endif;

		$template_file = wds_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $template_file ) ) :
			return _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		endif;

		include $template_file;

	}
}


if( !function_exists('wds_get_template_html') ) {
	/**
	 * Get template HTML.
	 *
	 * Same as get_template only this returns the HTML.
	 *
	 * @since 1.0.0
	 *s
	 * @see wcpt_locate_template()
	 *
	 * @param   string  $template_name  Template to load.
	 * @param   array   $args           Args passed for the template file.
	 * @param   string  $template_path  Path to templates.
	 * @param   string  $default_path   Default path to template files.
	 * @return  string                  The HTML of the template.
	 */
	function wds_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

		ob_start();
			wds_get_template( $template_name, $args, $template_path, $default_path );
		$html = ob_get_clean();

		return $html;

	}
}


if( !function_exists('wds_template_hooks') ) {
	/**************************************************************
	 * Template hooks
	 *************************************************************/

	/**
	 * Add template hooks.
	 *
	 * Add the hooks for the set actions in the template files and attach
	 * the related template parts to the templates.
	 *
	 * @since 1.0.0
	 */
	function wds_template_hooks() {

		// Email
		add_action( 'wds_email_footer', 'wds_default_email_footer', 10 );
		add_action( 'wds_email_header', 'wds_default_email_header', 10 );

	}
	add_action( 'init', 'wds_template_hooks' );
}

if( !function_exists('wds_default_email_header') ) {

	function wds_default_email_header( $args ) {

		// $template_args = wp_parse_args( $args, array(
		// 	'show_settings_message' => get_option( 'camp_notification_settings_message', __( 'no', 'camp' ) ),
		// 	'subtitle' => get_option( 'camp_notification_subtitle', __( 'A friendly reminder for the freshness of your content.', 'camp' ) ),
		// 	'img' => get_option( 'camp_notification_logo_url', esc_url_raw( site_url( 'logo.png' ) ) ),
		// ) );
		// wds_get_template( 'emails/parts/header.php', $template_args );
	}
}

if( !function_exists('wds_default_email_footer') ) {

	function wds_default_email_footer( $args ) {
		// $white_label = camp_string('plugin_white_label')->get();
		// $plugin_name = camp_string('plugin_name')->get();
		// $template_args = wp_parse_args( $args, array(
		// 	'show_settings_message' => get_option( 'camp_notification_settings_message', __( 'no', 'camp' ) ),
		// 	'footer' => camp_string('email_footer_message')->format( array( $white_label, $plugin_name ) )->get(),
		// ) );
		// wds_get_template( 'emails/parts/footer.php', $template_args  );
	}
}
