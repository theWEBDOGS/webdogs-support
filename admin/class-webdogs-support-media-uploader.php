<?php
/**
 * @package   Webdogs
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

class Webdogs_Media_Uploader {

	/**
	 * Initialize the media uploader class
	 *
	 * @since 1.7.0
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'wds_media_scripts' ) );

		add_filter( 'upload_mimes',  array( $this, 'wds_svgs_upload_mimes' ) );
	}

	/**
	 * Media Uploader Using the WordPress Media Library.
	 *
	 * Parameters:
	 *
	 * string $_id - A token to identify this field (the name).
	 * string $_value - The value of the field, if present.
	 * string $_desc - An optional description of the field.
	 *
	 */

	static function wds_uploader( $_id, $_value, $_desc = '', $_name = '' ) {

		$wds_settings = get_option( 'webdogs_support' );

		// Gets the unique option id
		$option_name = $wds_settings['id'];

		$output = '';
		$id = '';
		$class = '';
		$int = '';
		$value = '';
		$name = '';

		$id = strip_tags( strtolower( $_id ) );

		// If a value is passed and we don't have a stored value, use the value that's passed through.
		if ( $_value != '' && $value == '' ) {
			$value = $_value;
		}

		if ($_name != '') {
			$name = $_name;
		}
		else {
			$name = $option_name.'['.$id.']';
		}

		if ( $value ) {
			$class = ' has-file';
		}
		$output .= '<input id="' . $id . '" class="upload' . $class . '" type="text" name="'.$name.'" value="' . $value . '" placeholder="' . __('No file chosen', 'webdogs-support') .'" />' . "\n";
		if ( function_exists( 'wp_enqueue_media' ) ) {
			if ( ( $value == '' ) ) {
				$output .= '<input id="upload-' . $id . '" class="upload-button button" type="button" value="' . __( 'Upload', 'webdogs-support' ) . '" />' . "\n";
			} else {
				$output .= '<input id="remove-' . $id . '" class="remove-file button" type="button" value="' . __( 'Remove', 'webdogs-support' ) . '" />' . "\n";
			}
		} else {
			$output .= '<p><i>' . __( 'Upgrade your version of WordPress for full media support.', 'webdogs-support' ) . '</i></p>';
		}

		if ( $_desc != '' ) {
			$output .= '<span class="of-metabox-desc">' . $_desc . '</span>' . "\n";
		}

		$output .= '<div class="screenshot" id="' . $id . '-image">' . "\n";

		if ( $value != '' ) {
			$remove = '<a class="remove-image">Remove</a>';
			$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico|svg*)/i', $value );
			/*$image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $value );
			$object = preg_match( '/(^.*\.svg*)/i', $value );*/
			if ( $image ) {
				$output .= '<img src="' . $value . '" alt="" />' . $remove;
			} 
			// elseif ( $object ) {
			// 	$output .= '<object data="' . $value . '" type="image/svg+xml" class="screenshot-svg" width="100%" height="100%"></object>' . $remove;
			// } 
			else {
				$parts = explode( "/", $value );
				for( $i = 0; $i < sizeof( $parts ); ++$i ) {
					$title = $parts[$i];
				}

				// No output preview if it's not an image.
				$output .= '';

				// Standard generic output if it's not an image.
				$title = __( 'View File', 'webdogs-support' );
				$output .= '<div class="no-image"><span class="file_link"><a href="' . $value . '" target="_blank" rel="external">'.$title.'</a></span></div>';
			}
		}
		$output .= '</div>' . "\n";
		return $output;
	}

	/**
	 * Enqueue scripts for file uploader
	 */
	function wds_media_scripts( $hook ) {

		$menu = Webdogs_Admin::menu_settings();

        if ( substr( $hook, -strlen( $menu['menu_slug'] ) ) !== $menu['menu_slug'] )
	        return;

		if ( function_exists( 'wp_enqueue_media' ) )
			wp_enqueue_media();

		wp_register_script( 'of-media-uploader', plugin_dir_url( dirname(__FILE__) ) .'admin/js/media-uploader.js', array( 'jquery' ), Webdogs_Options::VERSION );
		wp_enqueue_script( 'of-media-uploader' );
		wp_localize_script( 'of-media-uploader', 'wds_l10n', array(
			'upload' => __( 'Upload', 'webdogs-support' ),
			'remove' => __( 'Remove', 'webdogs-support' )
		) );
	}

	/**
	 * Add SVG support
	 */
	function wds_svgs_upload_mimes($mimes = array()) {
		// support for bodhi plugin.
		global $bodhi_svgs_options;

		if( empty( $bodhi_svgs_options['restrict'] ) || current_user_can( 'administrator' ) ) {
			// allow SVG file upload
			$mimes['svg'] = 'image/svg+xml';
			$mimes['svgz'] = 'image/svg+xml';
			return $mimes;
		} else {
			return $mimes;
		}

	}
}