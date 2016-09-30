<?php
/*
Plugin Name: Admin Color Schemer
Plugin URI: http://wordpress.org/plugins/admin-color-schemer/
Description: Create your own admin color schemes, right in the WordPress admin.
Version: 1.0
Author: WordPress Core Team
Author URI: http://wordpress.org/
Text Domain: admin-color-schemer
Domain Path: /languages
*/

/*
Copyright 2013 Helen Hou-Sandí, Mark Jaquith

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

defined( 'WPINC' ) or die;

class Options_Framework_Admin_Color_Schemes {
	private static $instance;
	private $base;
	const OPTION = 'admin-color-schemes';
	const NONCE = 'admin-color-schemes_save';

	private $colors;

	protected function __construct() {
		self::$instance = $this;
		$this->base = WEBDOGS_SUPPORT_DIR . '/options-framework';
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			new self;
		}
		return self::$instance;
	}

	public function init() {

		// Set up color arrays - need translations
		$this->colors['basic'] = array(
			'base_color' => __( 'Base', 'options-framework' ),
			'icon_color' => __( 'Icon', 'options-framework' ),
			'highlight_color' => __( 'Highlight', 'options-framework' ),
			'notification_color' => __( 'Notification', 'options-framework' ),
		);

		$this->colors['advanced'] = array(
			'button_color' => __( 'Button', 'options-framework' ),
			'text_color' => __( 'Text (over Base)', 'options-framework' ),
			'body_background' => __( 'Body background', 'options-framework' ),
			'link' => __( 'Link', 'options-framework' ),
			'link_focus' => __( 'Link interaction', 'options-framework' ),
			'form_checked' => __( 'Checked form controls', 'options-framework' ),
			'menu_background' => __( 'Menu background', 'options-framework' ),
			'menu_text' => __( 'Menu text', 'options-framework' ),
			'menu_icon' => __( 'Menu icon', 'options-framework' ),
			'menu_highlight_background' => __( 'Menu highlight background', 'options-framework' ),
			'menu_highlight_text' => __( 'Menu highlight text', 'options-framework' ),
			'menu_highlight_icon' => __( 'Menu highlight icon', 'options-framework' ),
			'menu_current_background' => __( 'Menu current background', 'options-framework' ),
			'menu_current_text' => __( 'Menu current text', 'options-framework' ),
			'menu_current_icon' => __( 'Menu current icon', 'options-framework' ),
			'menu_submenu_background' => __( 'Submenu background', 'options-framework' ),
			'menu_submenu_text' => __( 'Submenu text', 'options-framework' ),
			'menu_submenu_background_alt' => __( 'Submenu alt background', 'options-framework' ),
			'menu_submenu_focus_text' => __( 'Submenu text interaction', 'options-framework' ),
			'menu_submenu_current_text' => __( 'Submenu current text', 'options-framework' ),
			'menu_bubble_background' => __( 'Bubble background', 'options-framework' ),
			'menu_bubble_text' => __( 'Bubble text', 'options-framework' ),
			'menu_bubble_current_background' => __( 'Bubble current background', 'options-framework' ),
			'menu_bubble_current_text' => __( 'Bubble current text', 'options-framework' ),
			'menu_collapse_text' => __( 'Menu collapse text', 'options-framework' ),
			'menu_collapse_icon' => __( 'Menu collapse icon', 'options-framework' ),
			'menu_collapse_focus_text' => __( 'Menu collapse text interaction', 'options-framework' ),
			'menu_collapse_focus_icon' => __( 'Menu collapse icon interaction', 'options-framework' ),
			'adminbar_avatar_frame' => __( 'Toolbar avatar frame', 'options-framework' ),
			'adminbar_input_background' => __( 'Toolbar input background', 'options-framework' ),
		);

		// Hooks
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_post_admin-color-schemes-save', array( $this, 'save' ) );
		add_action( 'wp_ajax_admin-color-schemes-save',    array( $this, 'save' ) );
		add_action( 'optionsframework_after_validate',     array( $this, 'of_save' ) );
	}

	public function admin_init() {

		$schemes = $this->get_option( 'schemes', array() );

		foreach ( $schemes as $scheme ) {
			wp_admin_css_color(
				$scheme['slug'],
				get_bloginfo( 'name' ),
				esc_url( $scheme['uri'] ),
				array( $scheme['base_color'], $scheme['icon_color'], $scheme['highlight_color'], $scheme['notification_color'] ),
				array( 'base' => $scheme['icon_color'], 'focus' => $scheme['icon_focus'], 'current' => $scheme['icon_current'] )
			);
		}
	}

	public function admin_menu() {
		$hook = add_management_page( 'Admin Color Scheme', 'Admin Colors', 'manage_options', 'options-framework', array( $this, 'admin_page' ) );
		add_action( 'load-' . $hook, array( $this, 'load' ) );
	}

	public function load() {
		if ( isset( $_GET['updated'] ) ) {
			add_action( 'admin_notices', array( $this, 'updated' ) );
		} elseif ( isset( $_GET['empty_scheme'] ) ) {
			add_action( 'admin_notices', array( $this, 'empty_scheme' ) );
		}

		add_action ( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function updated() {
		include( $this->base . '/templates/updated.php' );
	}

	public function empty_scheme() {
		include( $this->base . '/templates/empty-scheme.php' );
	}

	public function admin_enqueue_scripts() {
		// wp_enqueue_script( 'options-framework', plugins_url( '/js/admin-color-scheme.js', dirname( __FILE__ ) ), array( 'wp-color-picker' ), false, true );
		// wp_enqueue_style( 'options-framework', plugins_url( '/css/admin-color-scheme.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ) );
	}

	public function admin_page() {
		// $scheme = $this->get_color_scheme();
		// include( $this->base . '/templates/admin-page.php' );
	}

	public function get_option( $key, $default = null ) {
		$option = get_option( self::OPTION );
		if ( ! is_array( $option ) || ! isset( $option[$key] ) ) {
			return $default;
		} else {
			return $option[$key];
		}
	}

	public function set_option( $key, $value ) {
		$option = get_option( self::OPTION );
		is_array( $option ) || $option = array();
		$option[$key] = $value;
		update_option( self::OPTION, $option );
	}

	public function get_color_scheme( $id = null ) {
		$scheme = null;

		// special handling for preview
		if ( 'preview' === $id ) {
			$preview_defaults = array(
				'id' => 'preview',
				'name' => 'preview',
			);

			$scheme = $this->get_option( 'preview', $preview_defaults );
		} else {
			// otherwise ignoring $id right now during development
			$schemes = $this->get_option( 'schemes', array() );
			$scheme = array_shift( $schemes );
		}

		if ( $scheme ) {
			return new Admin_Color_Scheme( $scheme );
		} else {
			return new Admin_Color_Scheme();
		}
	}

	public function get_colors( $set = null ) {
		if ( 'basic' === $set ) {
			return $this->colors['basic'];
		} elseif ( 'advanced' === $set ) {
			return $this->colors['advanced'];
		} elseif( 'keys' === $set ) {
			// special handling for dashes to underscores, because PHP
			$keys = array_keys( $this->get_colors() );
			$scss_keys = array();
			foreach ( $keys as $key ) {
				$scss_keys[] = str_replace( '-', '_', $key );
			}

			// the naming of keys is kind of backward here
			return array_combine( $keys, $scss_keys );
		} else {
			return array_merge( $this->colors['basic'], $this->colors['advanced'] );
		}
	}

	public function admin_url() {
		return admin_url( 'admin.php?page=options-framework' );
	}

	public function save() {
		current_user_can( 'manage_options' ) || die;
		check_admin_referer( self::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );
		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( $doing_ajax ) {
			$scheme = $this->get_color_scheme( 'preview' );
		} else {
			$scheme = $this->get_color_scheme();
		}

		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		if ( isset( $optionsframework_settings['id'] ) ) {
			$option_name = $optionsframework_settings['id'];
		}
		else {
			$option_name = 'optionsframework';
		};

		$colors = $this->get_colors( 'keys' );

		// @todo: which, if any, of these are required?
		foreach ( $colors as $key => $scss_key ) {
			// really, these are always set, but always check, too!
			if ( isset( $_post[ $option_name ][ 'admin_color_scheme' ][ $key ] ) ) {
				$scheme->{$key} = $_post[ $option_name ][ 'admin_color_scheme' ][ $key ];
			}
		}

		$scss = '';

		foreach( $colors as $key => $scss_key ) {
			if ( ! empty( $scheme->{$key} ) ) {
				$scss .= "\${$scss_key}: {$scheme->$key};\n";
			}
		}

		if ( empty( $scss ) ) {
			// bail if this gets emptied out
			if ( $doing_ajax ) {
				$response = array(
					'errors' => true,
					'message' => __( 'Please make more selections to preview the color scheme.', 'options-framework' ),
				);

				echo json_encode( $response );
				die();
			}

			// reset color scheme object
			$scheme = $this->get_color_scheme();
			wp_redirect( $this->admin_url() . '&empty_scheme=true' );
			exit;
		}

		$scss .= "\n\n@import 'colors.css';\n@import '_admin.scss';\n";

		// okay, let's see about getting credentials
		// @todo: what to do about preview
		if ( false === ( $creds = request_filesystem_credentials( $this->admin_url() ) ) ) {
			return true;
		}

		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $this->admin_url(), '', true );
			return true;
		}

		global $wp_filesystem;

		$wp_upload_dir = wp_upload_dir();
		$upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
		$upload_url = $wp_upload_dir['baseurl'] . '/admin-color-scheme';

		// @todo: error handling if this can't be made - needs to be differentiated from already there
		$wp_filesystem->mkdir( $upload_dir );

		if ( $doing_ajax ) {
			$scss_file = $upload_dir . '/preview.scss';
			$css_file = $upload_dir . '/preview.css';
			// use a modified query arg to avoid caching problems
			// @todo: chances are we'll need to do this for the saved scheme as well.
			$uri = $upload_url . '/preview.css?m=' . microtime();
		} else {
			// @todo: save into another subdirectory for multiple scheme handling
			$scss_file = $upload_dir . '/scheme.scss';
			$css_file = $upload_dir . '/scheme.css';
			$uri = $upload_url . '/scheme.css';
		}

		$this->maybe_copy_core_files( $upload_dir );

		// write the custom.scss file
		if ( ! $wp_filesystem->put_contents( $scss_file, $scss, FS_CHMOD_FILE) ) {
			if ( $doing_ajax ) {
				$response = array(
					'errors' => true,
					'message' => __( 'Could not write custom SCSS file.', 'options-framework' ),
				);

				echo json_encode( $response );
				die();
			}

			// @todo: error that the scheme couldn't be written and redirect
			exit( 'Could not write custom SCSS file.' );
		}

		// Compile and write!
		require_once( $this->base . '/lib/phpsass/SassParser.php' );
		$sass = new SassParser();
		$css = $sass->toCss( $scss_file );

		if ( ! $wp_filesystem->put_contents( $css_file, $css, FS_CHMOD_FILE) ) {
			if ( $doing_ajax ) {
				$response = array(
					'errors' => true,
					'message' => __( 'Could not write compiled CSS file.', 'options-framework' ),
				);

				echo json_encode( $response );
				die();
			}

			// @todo: error that the compiled scheme couldn't be written and redirect
			exit( 'Could not write compiled CSS file.' );
		}

		// add the URI of the sheet to the settings array
		$scheme->uri = $uri;

		if ( $doing_ajax ) {
			$response = array(
				'uri' => $scheme->uri,
				'message' => __( 'Previewing. Be sure to save if you like the result.', 'options-framework' ),
			);

			echo json_encode( $response );
			die();
		}

		$this->set_option( 'schemes', array( $scheme->id => $scheme->to_array() ) );

		// switch to the scheme
		update_user_meta( get_current_user_id(), 'admin_color', $scheme->slug );

		wp_redirect( $this->admin_url() . '&updated=true' );
		exit;
	}

	public function of_save() {

		if( ! current_user_can( 'manage_options' )){ return; }

		check_admin_referer( self::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );
		
		$scheme = $this->get_color_scheme();

		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		if ( isset( $optionsframework_settings['id'] ) ) {
			$option_name = $optionsframework_settings['id'];
		}
		else {
			$option_name = 'optionsframework';
		};

		$colors = $this->get_colors( 'keys' );

		// @todo: which, if any, of these are required?
		foreach ( $colors as $key => $scss_key ) {
			// really, these are always set, but always check, too!
			if ( isset( $_post[ $option_name ][ 'admin_color_scheme' ][ $key ] ) ) {
				$scheme->{$key} = $_post[ $option_name ][ 'admin_color_scheme' ][ $key ];
			}
		}

		$scss = '';

		foreach( $colors as $key => $scss_key ) {
			if ( ! empty( $scheme->{$key} ) ) {
				$scss .= "\${$scss_key}: {$scheme->$key};\n";
			}
		}

		if ( empty( $scss ) ) {

			// reset color scheme object
			$scheme = $this->get_color_scheme();
			// add_settings_error( 'options-framework', 'color_css', 'Using default color scheme.', 'update-nag dismissable' );
			return true;
		}

		$scss .= "\n\n@import 'colors.css';\n@import '_admin.scss';\n";

		// okay, let's see about getting credentials
		// @todo: what to do about preview
		if ( false === ( $creds = request_filesystem_credentials( $this->admin_url() ) ) ) {
			add_settings_error( 'options-framework', 'color_css', 'Could not write CSS file.', 'error' );
			return true;
		}

		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $this->admin_url(), '', true );

			return true;
		}

		global $wp_filesystem;

		$wp_upload_dir = wp_upload_dir();
		$upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
		$upload_url = $wp_upload_dir['baseurl'] . '/admin-color-scheme';

		// @todo: error handling if this can't be made - needs to be differentiated from already there
		$wp_filesystem->mkdir( $upload_dir );

		// @todo: save into another subdirectory for multiple scheme handling
		$scss_file = $upload_dir . '/scheme.scss';
		$css_file = $upload_dir . '/scheme.css';
		$uri = $upload_url . '/scheme.css';

		$this->maybe_copy_core_files( $upload_dir );

		// write the custom.scss file
		if ( ! $wp_filesystem->put_contents( $scss_file, $scss, FS_CHMOD_FILE) ) {

			// @todo: error that the scheme couldn't be written and redirect
			add_settings_error( 'options-framework', 'color_css', 'Could not write custom SCSS file.', 'error' );
			return true;
		}

		// Compile and write!
		require_once( $this->base . '/lib/phpsass/SassParser.php' );
		$sass = new SassParser();
		$css = $sass->toCss( $scss_file );

		if ( ! $wp_filesystem->put_contents( $css_file, $css, FS_CHMOD_FILE) ) {
			
			$message = __( 'Could not write compiled CSS file.', 'options-framework' );

			add_settings_error( 'options-framework', 'color_css', $message, 'update-nag dismissable' );
		

			return true;
			// @todo: error that the compiled scheme couldn't be written and redirect
			// exit( 'Could not write compiled CSS file.' );
		}

		// add the URI of the sheet to the settings array
		$scheme->uri = $uri;

		$this->set_option( 'schemes', array( $scheme->id => $scheme->to_array() ) );

		// switch to the scheme
		update_user_meta( get_current_user_id(), 'admin_color', $scheme->slug );

		// add_settings_error( 'options-framework', 'color_css', 'the admin color scheme has been updated.', 'webdogs-nag' );
	}

	public function maybe_copy_core_files( $upload_dir ) {
		global $wp_filesystem;

		// pull in core's default colors.css and scss files if they're not there already
		$core_scss = array( '_admin.scss', '_mixins.scss', '_variables.scss' );
		$admin_dir = ABSPATH . '/wp-admin/css/';

		foreach ( $core_scss as $file ) {
			if ( ! file_exists( $upload_dir . "/{$file}" ) ) {
				if ( ! $wp_filesystem->put_contents( $upload_dir . "/{$file}", $wp_filesystem->get_contents( $admin_dir . 'colors/' . $file, FS_CHMOD_FILE) ) ) {
					if ( $doing_ajax ) {
						$response = array(
							'errors' => true,
							'message' => __( 'Could not copy a core file.', 'options-framework' ),
						);

						echo json_encode( $response );
						die();
					}

					// @todo: error that the scheme couldn't be written and redirect
					exit( "Could not copy the core file {$file}." );
				}
			}
		}

		if ( ! file_exists( $upload_dir . "/colors.css" ) ) {
			if ( ! $wp_filesystem->put_contents( $upload_dir . "/colors.css", $wp_filesystem->get_contents( $admin_dir . 'colors.css', FS_CHMOD_FILE) ) ) {
				if ( $doing_ajax ) {
					$response = array(
						'errors' => true,
						'message' => __( 'Could not copy a core file.', 'options-framework' ),
					);

					echo json_encode( $response );
					die();
				}

				// @todo: error that the scheme couldn't be written and redirect
				exit( "Could not copy the core file colors.css." );
			}
		}
	}
}



class Admin_Color_Scheme {
	// possibly-temporary default
	protected $id = 1;
	protected $slug;
	protected $name;
	protected $uri;
	protected $accessors = array( 'id', 'slug', 'name', 'uri', 'icon_focus', 'icon_current' );

	// Icon colors for SVG painter - likely temporary placement, as it will need some more special handling
	protected $icon_color = '#fff';
	protected $icon_focus = '#fff';
	protected $icon_current = '#fff';

	public function __construct( $attr = NULL ) {
		// extend accessors
		$admin_scheme = Options_Framework_Admin_Color_Schemes::get_instance();
		$this->accessors = array_merge( $this->accessors, array_keys( $admin_scheme->get_colors() ) );

		// set slug
		$this->slug = 'admin_color_scheme_' . $this->id;

		if ( is_array( $attr ) ) {
			foreach ( $this->accessors as $thing ) {
				if ( isset( $attr[$thing] ) && ! empty( $attr[$thing] ) ) {
					$this->{$thing} = $attr[$thing];
				}
			}
		} else {
			// set defaults
			// @todo: make this really set defaults for the items that must have a color - what are those?
			$this->name = get_bloginfo( 'name' );
		}
	}

	public function __get( $key ) {
		if ( in_array( $key, $this->accessors ) ) {
			if ( isset( $this->{$key} ) ) {
				return $this->sanitize( $this->{$key}, $key, 'out' );
			} else {
				return false;
			}
		}
	}

	public function __set( $key, $value ) {
		if ( in_array( $key, $this->accessors ) ) {
			$this->{$key} = $this->sanitize( $value, $key, 'in' );
		}
	}

	public function __isset( $key ) {
		return isset( $this->$key );
	}

	private function sanitize( $value, $key, $direction ) {
		switch ( $key ) {
			case 'id':
				$value = absint( $value );
				break;
			case 'slug':
				$value = sanitize_key( $value );
			case 'name':
				$value = esc_html( $value );
				break;
			case 'uri':
				$value = esc_url_raw( $value );
				break;
			default:
				// everything else should be a hex value
				// regex copied from core's sanitize_hex_value()
				if ( ! preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $value ) ) {
					$value = '';
				}
				break;
		}
		return $value;
	}

	public function to_array() {
		$return = array();
		foreach ( $this->accessors as $thing ) {
			$return[$thing] = $this->{$thing};
		}
		return $return;
	}
}


class Options_Framework_Admin_Color_Schemes_Version_Check {
	private static $instance;

	protected function __construct() {
		self::$instance = $this;
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			new self;
		}
		return self::$instance;
	}

	public function passes() {
		return version_compare( get_bloginfo( 'version' ), '3.8-beta', '>' );
	}

	public function plugins_loaded() {
		if ( ! $this->passes() ) {
			remove_action( 'init', array( Options_Framework_Admin_Color_Schemes::get_instance(), 'init' ) );
  		if ( current_user_can( 'activate_plugins' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			}
		}
	}

	public function admin_notices() {
		echo '<div class="updated error"><p>' . __('<strong>Admin Color Scheme</strong> requires WordPress 3.8 or higher, and has thus been <strong>deactivated</strong>. Please update your install and then try again!', 'options-framework' ) . '</p></div>';
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
