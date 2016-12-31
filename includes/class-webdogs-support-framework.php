<?php
/**
 * @package   Options Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

class Webdogs_Options {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.7.0
	 * @type string
	 */
	const VERSION = '1.8.5';

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.7.0
	 */
	public function init() {

		// Needs to run every time in case theme has been changed
		add_action( 'admin_init', array( $this, 'set_support_option' ) );

	}

	/**
	 * Sets option defaults
	 *
	 * @since 1.7.0
	 */
	function set_support_option() {

		// Load settings
        $wds_settings = get_option( 'webdogs_support' );

        // Updates the unique option id in the database if it has changed
        if ( function_exists( 'wds_option_name' ) ) {
			wds_option_name();
        }
        elseif ( has_action( 'wds_option_name' ) ) {
			do_action( 'wds_option_name' );
        }
	}

	/**
	 * Wrapper for wds_options()
	 *
	 * Allows for manipulating or setting options via 'wds_options' filter
	 * For example:
	 *
	 * <code>
	 * add_filter( 'wds_options', function( $options ) {
	 *     $options[] = array(
	 *         'name' => 'Input Text Mini',
	 *         'desc' => 'A mini text input field.',
	 *         'id' => 'example_text_mini',
	 *         'std' => 'Default',
	 *         'class' => 'mini',
	 *         'type' => 'text'
	 *     );
	 *
	 *     return $options;
	 * });
	 * </code>
	 *
	 * Also allows for setting options via a return statement in the
	 * options.php file.  For example (in options.php):
	 *
	 * <code>
	 * return array(...);
	 * </code>
	 *
	 * @return array (by reference)
	 */
	static function &_wds_options() {
		static $options = null;

		if ( !$options ) {
	        // Load options from options.php file (if it exists)
	        $location = apply_filters( 'wds_options_location', array('options.php') );
	        if ( function_exists( 'wds_options' ) ) {
					$options = wds_options();
			} elseif ( $optionsfile = locate_template( $location ) ) {
	            $maybe_options = require_once $optionsfile;
	            if ( is_array( $maybe_options ) ) {
					$options = $maybe_options;
	            }
	        }
	        
	        // Allow setting/manipulating options via filters
	        $options = apply_filters( 'wds_options', $options );
		}

		return $options;
	}

}