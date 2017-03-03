<?php
/**
 * @package   Webdogs
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

/**
 * Sanitization for text input
 *
 * @link http://developer.wordpress.org/reference/functions/sanitize_text_field/
 */
add_filter( 'wds_sanitize_text', 'sanitize_text_field' );

/**
 * Sanitization for password input
 *
 * @link http://developer.wordpress.org/reference/functions/sanitize_text_field/
 */
add_filter( 'wds_sanitize_password', 'sanitize_text_field' );

/**
 * Sanitization for select input
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'wds_sanitize_select', 'wds_sanitize_enum', 10, 2 );

/**
 * Sanitization for radio input
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'wds_sanitize_radio', 'wds_sanitize_enum', 10, 2 );

/**
 * Sanitization for image selector
 *
 * Validates that the selected option is a valid option.
 */
add_filter( 'wds_sanitize_images', 'wds_sanitize_enum', 10, 2 );

/**
 * Sanitization for textarea field
 *
 * @param $input string
 * @return $output sanitized string
 */
function wds_sanitize_textarea( $input ) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags );
	return $output;
}
add_filter( 'wds_sanitize_textarea', 'wds_sanitize_textarea' );

/**
 * @since 1.0
 *
 * @param  string $logo_icon_css The logo icon css.
 * @return string
 */
function wds_sanitize_logo_icon_css( $logo_icon_css ) {
	return str_replace('f102', '\f102', html_entity_decode( $logo_icon_css ) );
}
add_filter( 'wds_logo_icon_css', 'wds_sanitize_logo_icon_css' );

/**
 * Sanitization for checkbox input
 *
 * @param $input string (1 or empty) checkbox state
 * @return $output '1' or false
 */
function wds_sanitize_checkbox( $input ) {
	if ( $input ) {
		$output = '1';
	} else {
		$output = false;
	}
	return $output;
}
add_filter( 'wds_sanitize_checkbox', 'wds_sanitize_checkbox' );

/**
 * Sanitization for multicheck
 *
 * @param array of checkbox values
 * @return array of sanitized values ('1' or false)
 */
function wds_sanitize_multicheck( $input, $option ) {
	$output = '';
	if ( is_array( $input ) ) {
		foreach( $option['options'] as $key => $value ) {
			$output[$key] = false;
		}
		foreach( $input as $key => $value ) {
			if ( array_key_exists( $key, $option['options'] ) && $value ) {
				$output[$key] = '1';
			}
		}
	}
	return $output;
}
add_filter( 'wds_sanitize_multicheck', 'wds_sanitize_multicheck', 10, 2 );

/**
 * File upload sanitization.
 *
 * Returns a sanitized filepath if it has a valid extension.
 *
 * @param string $input filepath
 * @returns string $output filepath
 */
function wds_sanitize_upload( $input ) {
	$output = '';
	$filetype = wp_check_filetype( $input );
	if ( $filetype["ext"] ) {
		$output = esc_url( $input );
	}
	return $output;
}
add_filter( 'wds_sanitize_upload', 'wds_sanitize_upload' );

/**
 * Sanitization for editor input.
 *
 * Returns unfiltered HTML if user has permissions.
 *
 * @param string $input
 * @returns string $output
 */
function wds_sanitize_editor( $input ) {
	if ( current_user_can( 'unfiltered_html' ) ) {
		$output = $input;
	}
	else {
		global $allowedposttags;
		$output = wp_kses( $input, $allowedposttags );
	}
	return $output;
}
add_filter( 'wds_sanitize_editor', 'wds_sanitize_editor' );

/**
 * Sanitization of input with allowed tags and wpautotop.
 *
 * Allows allowed tags in html input and ensures tags close properly.
 *
 * @param string $input
 * @returns string $output
 */
function wds_sanitize_allowedtags( $input ) {
	global $allowedtags;
	$output = wpautop( wp_kses( $input, $allowedtags ) );
	return $output;
}

/**
 * Sanitization of input with allowed post tags and wpautotop.
 *
 * Allows allowed post tags in html input and ensures tags close properly.
 *
 * @param string $input
 * @returns string $output
 */
function wds_sanitize_allowedposttags( $input ) {
	global $allowedposttags;
	$output = wpautop( wp_kses( $input, $allowedposttags) );
	return $output;
}

/**
 * Validates that the $input is one of the avilable choices
 * for that specific option.
 *
 * @param string $input
 * @returns string $output
 */
function wds_sanitize_enum( $input, $option ) {
	$output = '';
	if ( array_key_exists( $input, $option['options'] ) ) {
		$output = $input;
	}
	return $output;
}

/**
 * Sanitization for background option.
 *
 * @returns array $output
 */
function wds_sanitize_background( $input ) {

	$output = wp_parse_args( $input, array(
		'color' => '',
		'image'  => '',
		'repeat'  => 'repeat',
		'position' => 'top center',
		'attachment' => 'scroll'
	) );

	$output['color'] = apply_filters( 'wds_sanitize_hex', $input['color'] );
	$output['image'] = apply_filters( 'wds_sanitize_upload', $input['image'] );
	$output['repeat'] = apply_filters( 'wds_background_repeat', $input['repeat'] );
	$output['position'] = apply_filters( 'wds_background_position', $input['position'] );
	$output['attachment'] = apply_filters( 'wds_background_attachment', $input['attachment'] );

	return $output;
}
add_filter( 'wds_sanitize_background', 'wds_sanitize_background' );

/**
 * Sanitization for scheme option.
 *
 * @returns array $output
 */
function wds_sanitize_scheme( $input ) {

	$admin_schemes = Webdogs_Support_Admin_Color_Schemes::get_instance();

	$output = array();

	if( empty( $input['must_use'] ) ) {
		$input['must_use'] = "";
	}

	$output['must_use'] = apply_filters( 'wds_sanitize_checkbox(', $input['must_use'] );

	$loops = $admin_schemes->get_colors( 'basic' );
	foreach ( $loops as $handle => $nicename ):

		$output[$handle] = apply_filters( 'wds_sanitize_hex', $input[ $handle ] );

	endforeach;

	$loops = $admin_schemes->get_colors( 'advanced' );
	foreach ( $loops as $handle => $nicename ):

		$output[$handle] = apply_filters( 'wds_sanitize_hex', $input[ $handle ] );

	endforeach;

	return $output;
}
add_filter( 'wds_sanitize_scheme', 'wds_sanitize_scheme' );

/**
 * Sanitization for background repeat
 *
 * @returns string $value if it is valid
 */
function wds_sanitize_background_repeat( $value ) {
	$recognized = wds_recognized_background_repeat();
	if ( array_key_exists( $value, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_background_repeat', current( $recognized ) );
}
add_filter( 'wds_background_repeat', 'wds_sanitize_background_repeat' );

/**
 * Sanitization for background position
 *
 * @returns string $value if it is valid
 */
function wds_sanitize_background_position( $value ) {
	$recognized = wds_recognized_background_position();
	if ( array_key_exists( $value, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_background_position', current( $recognized ) );
}
add_filter( 'wds_background_position', 'wds_sanitize_background_position' );

/**
 * Sanitization for background attachment
 *
 * @returns string $value if it is valid
 */
function wds_sanitize_background_attachment( $value ) {
	$recognized = wds_recognized_background_attachment();
	if ( array_key_exists( $value, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_background_attachment', current( $recognized ) );
}
add_filter( 'wds_background_attachment', 'wds_sanitize_background_attachment' );

/**
 * Sanitization for typography option.
 */
function wds_sanitize_typography( $input, $option ) {

	$output = wp_parse_args( $input, array(
		'size'  => '',
		'face'  => '',
		'style' => '',
		'color' => ''
	) );

	if ( isset( $option['options']['faces'] ) && isset( $input['face'] ) ) {
		if ( !( array_key_exists( $input['face'], $option['options']['faces'] ) ) ) {
			$output['face'] = '';
		}
	}
	else {
		$output['face']  = apply_filters( 'wds_font_face', $output['face'] );
	}

	$output['size']  = apply_filters( 'wds_font_size', $output['size'] );
	$output['style'] = apply_filters( 'wds_font_style', $output['style'] );
	$output['color'] = apply_filters( 'wds_sanitize_color', $output['color'] );
	return $output;
}
add_filter( 'wds_sanitize_typography', 'wds_sanitize_typography', 10, 2 );

/**
 * Sanitization for font size
 */
function wds_sanitize_font_size( $value ) {
	$recognized = wds_recognized_font_sizes();
	$value_check = preg_replace('/px/','', $value);
	if ( in_array( (int) $value_check, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_font_size', $recognized );
}
add_filter( 'wds_font_size', 'wds_sanitize_font_size' );

/**
 * Sanitization for font style
 */
function wds_sanitize_font_style( $value ) {
	$recognized = wds_recognized_font_styles();
	if ( array_key_exists( $value, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_font_style', current( $recognized ) );
}
add_filter( 'wds_font_style', 'wds_sanitize_font_style' );

/**
 * Sanitization for font face
 */
function wds_sanitize_font_face( $value ) {
	$recognized = wds_recognized_font_faces();
	if ( array_key_exists( $value, $recognized ) ) {
		return $value;
	}
	return apply_filters( 'wds_default_font_face', current( $recognized ) );
}
add_filter( 'wds_font_face', 'wds_sanitize_font_face' );

/**
 * Get recognized background repeat settings
 *
 * @return   array
 */
function wds_recognized_background_repeat() {
	$default = array(
		'no-repeat' => __( 'No Repeat', 'webdogs-support' ),
		'repeat-x'  => __( 'Repeat Horizontally', 'webdogs-support' ),
		'repeat-y'  => __( 'Repeat Vertically', 'webdogs-support' ),
		'repeat'    => __( 'Repeat All', 'webdogs-support' ),
		);
	return apply_filters( 'wds_recognized_background_repeat', $default );
}

/**
 * Get recognized background positions
 *
 * @return   array
 */
function wds_recognized_background_position() {
	$default = array(
		'top left'      => __( 'Top Left', 'webdogs-support' ),
		'top center'    => __( 'Top Center', 'webdogs-support' ),
		'top right'     => __( 'Top Right', 'webdogs-support' ),
		'center left'   => __( 'Middle Left', 'webdogs-support' ),
		'center center' => __( 'Middle Center', 'webdogs-support' ),
		'center right'  => __( 'Middle Right', 'webdogs-support' ),
		'bottom left'   => __( 'Bottom Left', 'webdogs-support' ),
		'bottom center' => __( 'Bottom Center', 'webdogs-support' ),
		'bottom right'  => __( 'Bottom Right', 'webdogs-support')
		);
	return apply_filters( 'wds_recognized_background_position', $default );
}

/**
 * Get recognized background attachment
 *
 * @return   array
 */
function wds_recognized_background_attachment() {
	$default = array(
		'scroll' => __( 'Scroll Normally', 'webdogs-support' ),
		'fixed'  => __( 'Fixed in Place', 'webdogs-support')
		);
	return apply_filters( 'wds_recognized_background_attachment', $default );
}

/**
 * Sanitize a color represented in hexidecimal notation.
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @param    string    The value that this function should return if it cannot be recognized as a color.
 * @return   string
 */

function wds_sanitize_hex( $hex, $default = '' ) {
	if ( wds_validate_hex( $hex ) ) {
		return $hex;
	}
	return $default;
}
add_filter( 'wds_sanitize_color', 'wds_sanitize_hex' );

/**
 * Get recognized font sizes.
 *
 * Returns an indexed array of all recognized font sizes.
 * Values are integers and represent a range of sizes from
 * smallest to largest.
 *
 * @return   array
 */

function wds_recognized_font_sizes() {
	$sizes = range( 9, 71 );
	$sizes = apply_filters( 'wds_recognized_font_sizes', $sizes );
	$sizes = array_map( 'absint', $sizes );
	return $sizes;
}

/**
 * Get recognized font faces.
 *
 * Returns an array of all recognized font faces.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 */
function wds_recognized_font_faces() {
	$default = array(
		'arial'     => 'Arial',
		'verdana'   => 'Verdana, Geneva',
		'trebuchet' => 'Trebuchet',
		'georgia'   => 'Georgia',
		'times'     => 'Times New Roman',
		'tahoma'    => 'Tahoma, Geneva',
		'palatino'  => 'Palatino',
		'helvetica' => 'Helvetica*'
		);
	return apply_filters( 'wds_recognized_font_faces', $default );
}

/**
 * Get recognized font styles.
 *
 * Returns an array of all recognized font styles.
 * Keys are intended to be stored in the database
 * while values are ready for display in in html.
 *
 * @return   array
 */
function wds_recognized_font_styles() {
	$default = array(
		'normal'      => __( 'Normal', 'webdogs-support' ),
		'italic'      => __( 'Italic', 'webdogs-support' ),
		'bold'        => __( 'Bold', 'webdogs-support' ),
		'bold italic' => __( 'Bold Italic', 'webdogs-support' )
		);
	return apply_filters( 'wds_recognized_font_styles', $default );
}

/**
 * Is a given string a color formatted in hexidecimal notation?
 *
 * @param    string    Color in hexidecimal notation. "#" may or may not be prepended to the string.
 * @return   bool
 */
function wds_validate_hex( $hex ) {
	$hex = trim( $hex );
	/* Strip recognized prefixes. */
	if ( 0 === strpos( $hex, '#' ) ) {
		$hex = substr( $hex, 1 );
	}
	elseif ( 0 === strpos( $hex, '%23' ) ) {
		$hex = substr( $hex, 3 );
	}
	/* Regex match. */
	if ( 0 === preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) ) {
		return false;
	}
	else {
		return true;
	}
}