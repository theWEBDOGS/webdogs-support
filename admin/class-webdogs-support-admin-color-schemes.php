<?php
/*
Admin Color Schemer
http://wordpress.org/plugins/admin-color-schemer/
Create your own admin color schemes, right in the WordPress admin.
Version: 1.0
WordPress Core Team
URI: http://wordpress.org/
admin-color-schemer
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

class Webdogs_Support_Admin_Color_Schemes {
	private static $instance;
	private $base;
	const OPTION = 'admin-color-schemes';
	const NONCE = 'admin-color-schemes_save';

	private $colors;

	public function __construct() {
		Self::$instance = $this;
		$this->base = WEBDOGS_SUPPORT_DIR_PATH . 'admin';
		require_once WEBDOGS_SUPPORT_DIR_PATH . 'includes/options.php';
	}

	public static function get_instance() {
		if ( ! isset( Self::$instance ) ) {
			new Self;
		}
		return Self::$instance;
	}

	public function init() {

		// Set up color arrays - need translations
		$this->colors['basic'] = array(
			'base_color' => __( 'Base', 'webdogs-support' ),
			'icon_color' => __( 'Icon', 'webdogs-support' ),
			'highlight_color' => __( 'Highlight', 'webdogs-support' ),
			'notification_color' => __( 'Notification', 'webdogs-support' ),
			'link' => __( 'Link', 'webdogs-support' ),
		);

		$this->colors['icon'] = array(
			'menu_icon' => __( 'Base icon', 'webdogs-support' ),
			'menu_highlight_icon' => __( 'Focus icon', 'webdogs-support' ),
			'menu_current_icon' => __( 'Current icon', 'webdogs-support' )
		);

		$this->colors['advanced'] = array(
			'button_color' => __( 'Button', 'webdogs-support' ),
			'text_color' => __( 'Text (over Base)', 'webdogs-support' ),
			'body_background' => __( 'Body background', 'webdogs-support' ),
			'link_focus' => __( 'Link interaction', 'webdogs-support' ),
			'form_checked' => __( 'Checked form controls', 'webdogs-support' ),
			'menu_background' => __( 'Menu background', 'webdogs-support' ),
			'menu_text' => __( 'Menu text', 'webdogs-support' ),
			// 'menu_icon' => __( 'Menu icon', 'webdogs-support' ),
			'menu_highlight_background' => __( 'Menu highlight background', 'webdogs-support' ),
			'menu_highlight_text' => __( 'Menu highlight text', 'webdogs-support' ),
			// 'menu_highlight_icon' => __( 'Menu highlight icon', 'webdogs-support' ),
			'menu_current_background' => __( 'Menu current background', 'webdogs-support' ),
			'menu_current_text' => __( 'Menu current text', 'webdogs-support' ),
			// 'menu_current_icon' => __( 'Menu current icon', 'webdogs-support' ),
			'menu_submenu_background' => __( 'Submenu background', 'webdogs-support' ),
			'menu_submenu_text' => __( 'Submenu text', 'webdogs-support' ),
			'menu_submenu_background_alt' => __( 'Submenu alt background', 'webdogs-support' ),
			'menu_submenu_focus_text' => __( 'Submenu text interaction', 'webdogs-support' ),
			'menu_submenu_current_text' => __( 'Submenu current text', 'webdogs-support' ),
			'menu_bubble_background' => __( 'Bubble background', 'webdogs-support' ),
			'menu_bubble_text' => __( 'Bubble text', 'webdogs-support' ),
			'menu_bubble_current_background' => __( 'Bubble current background', 'webdogs-support' ),
			'menu_bubble_current_text' => __( 'Bubble current text', 'webdogs-support' ),
			'menu_collapse_text' => __( 'Menu collapse text', 'webdogs-support' ),
			'menu_collapse_icon' => __( 'Menu collapse icon', 'webdogs-support' ),
			'menu_collapse_focus_text' => __( 'Menu collapse text interaction', 'webdogs-support' ),
			'menu_collapse_focus_icon' => __( 'Menu collapse icon interaction', 'webdogs-support' ),
			'adminbar_avatar_frame' => __( 'Toolbar avatar frame', 'webdogs-support' ),
			'adminbar_input_background' => __( 'Toolbar input background', 'webdogs-support' ),
		);
		
	} 

	public function admin_init() {

		$schemes = apply_filters( 'get_color_scheme_options', $this->get_option( 'schemes', array() ) );

		foreach ( $schemes as $scheme ) {

			if ( is_array( $scheme ) ) {
				$color_scheme = new Admin_Color_Scheme( $scheme );
				$scheme = $color_scheme->to_array();
			}

			wp_admin_css_color(
				$scheme['slug'],
				$scheme['name'],
				esc_url( $this->maybe_ssl( $scheme['uri'] ) ),
				array( $scheme['base_color'], $scheme['icon_color'], $scheme['highlight_color'], $scheme['notification_color'] ),
				array( 'base' => $scheme['menu_icon'], 'focus' => $scheme['menu_highlight_icon'], 'current' => $scheme['menu_current_icon'] )
			);
		}

	}

	private function maybe_ssl( $url ) {
		if ( is_ssl() )
			$url = preg_replace( '#^http://#', 'https://', $url );
		return $url;
	}
	
	/**
	 * Overrides the user's admin color scheme with the forced admin color
	 * scheme, if set.
	 *
	 * @since 1.0
	 *
	 * @param  string $admin_color_scheme The admin color scheme.
	 * @return string
	 */
	public static function filter_color_scheme_options( $color_schemes ) {

		if ( current_user_can( 'manage_support_options' ) ) {

			$color_schemes = array_merge( array_values( $color_schemes ), array_values( wds_admin_color_schemes() ) );
		}

		return $color_schemes;
	}

	/**
	 * Overrides the user's admin color scheme with the forced admin color
	 * scheme, if set.
	 *
	 * @since 1.0
	 *
	 * @param  string $admin_color_scheme The admin color scheme.
	 * @return string
	 */
	public static function must_use_admin_color( $admin_color_scheme ) {

		$user = wp_get_current_user();

		if ( $user->exists() && is_webdog( $user ) ) {

			// $production = ( function_exists( 'is_wpe' ) && is_wpe() ) ? "wpengine_tc" : "webdogs_wpe" ;
			
			// OVERRIDE 
			$production = "webdogs_hs";
			$admin_color_scheme = ( wds_is_production_site() ) ? $production : "webdogs_ds" ;

			// CUSTOM FILTER FOR WEP CUSTOM THEME
			// if ( "wpengine_tc" === $admin_color_scheme && is_admin() ) {
			// 	add_filter('wds_adminbar_sitename', function( $sitename ) { 
			// 		return ( defined('PWP_NAME') ) ? PWP_NAME : $sitename ; }, 10, 1 );
			// }

		} elseif ( wds_must_use_admin_color() ) {

			// If a forced admin color has been configured, use it.
			$scheme = SELF::$instance->get_color_scheme();
			$admin_color_scheme = $scheme->slug;
		}

		return $admin_color_scheme;
	}
	/**
	 * Hides the Admin Color Scheme input and label when appropriate.
	 *
	 * The input is hidden for users who do not have the capability to set the
	 * forced admin color scheme *and* when an admin color scheme hasn't been
	 * set yet (so that user's can still choose until a forced admin color
	 * scheme is chosen).
	 *
	 * @since 1.1
	 */
	public function hide_admin_color_input() {
		$user = wp_get_current_user();
		if ( wds_must_use_admin_color() && ( $user->exists() && ! is_webdog( $user ) ) ) {
			remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
		}
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

	public static function sass_localize_script( $localize_script = array() ) {

		$wp_upload_dir = wp_upload_dir();

		$upload_url = is_dir( $wp_upload_dir['baseurl'] . '/admin-color-scheme' ) ? $upload_url : ABSPATH . '/wp-admin/css/colors/';

		return $localize_script + array(
			'sass' => array(
				'url'       => plugins_url( 'admin/js/sass.worker.js',  dirname( __FILE__ ) ),
				'variables' => json_encode( file_get_contents( $upload_url."/_variables.scss") ),
				'mixins'    => json_encode( file_get_contents( $upload_url."/_mixins.scss") ),
				'admin'     => json_encode( apply_filters( '_admin.scss', file_get_contents( $upload_url."/_admin.scss") ) ),
			)
		);

	}

	public function admin_enqueue_scripts() {
		// Compile and write!
		// wp_enqueue_script( 'webdogs-support', plugins_url( '/js/admin-color-scheme.js', dirname( __FILE__ ) ), array( 'wp-color-picker' ), false, true );
		// wp_enqueue_style( 'webdogs-support', plugins_url( '/css/admin-color-scheme.css', dirname( __FILE__ ) ), array( 'wp-color-picker' ) );
	}

	public function admin_page() {
		// $scheme = $this->get_color_scheme();
		// include( $this->base . '/templates/admin-page.php' );
	}

	public function get_option( $key, $default = null ) {
		$option = get_option( Self::OPTION );
		if ( ! is_array( $option ) || ! isset( $option[$key] ) ) {
			return $default;
		} else {
			return $option[$key];
		}
	}

	public function set_option( $key, $value ) {
		$option = get_option( Self::OPTION );
		is_array( $option ) || $option = array();
		$option[$key] = $value;
		update_option( Self::OPTION, $option );
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
		if( ! is_array( $this->colors ) ) {
			remove_action( 'init', array( $this, 'init' ) );
			$this->init();
		}
		if ( 'basic' === $set ) {
			return array_merge( $this->colors['basic'], $this->colors['icon'] );
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
			return array_merge( $this->colors['basic'], $this->colors['icon'], $this->colors['advanced'] );
		}
	}

	public function admin_url() {
		return admin_url( 'admin.php?page=webdogs-support' );
	}

	public function save() {
		current_user_can( 'manage_support_options' ) || die;

		check_admin_referer( Self::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );
		
		$doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( $doing_ajax ) {
			$scheme = $this->get_color_scheme( 'preview' );
		} else {
			$scheme = $this->get_color_scheme();
		}

		$wds_settings = get_option( 'webdogs_support' );

		// Gets the unique option id
		if ( isset( $wds_settings['id'] ) ) {
			$option_name = $wds_settings['id'];
		}
		else {
			$option_name = 'webdogs-support';
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
					'message' => wds_base_strings( 'acs_selections_preview' )//__( 'Please make more selections to preview the color scheme.', 'webdogs-support' ),
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
		if ( ! $wp_filesystem->put_contents( $scss_file, apply_filters( '_admin.scss', $scss ), FS_CHMOD_FILE) ) {
			if ( $doing_ajax ) {
				$response = array(
					'errors' => true,
					'message' => wds_base_strings( 'acs_write_custom_fail' )
				);

				echo json_encode( $response );
				die();
			}

			// @todo: error that the scheme couldn't be written and redirect
			exit( wds_base_strings( 'acs_write_custom_fail' ) );
		}

		// Compile and write!
		require_once( $this->base . '/lib/phpsass/SassParser.php' );
		$sass = new SassParser();
		$css = $sass->toCss( $scss_file );

		if ( ! $wp_filesystem->put_contents( $css_file, $css, FS_CHMOD_FILE) ) {
			if ( $doing_ajax ) {
				$response = array(
					'errors' => true,
					'message' => wds_base_strings( 'acs_write_compiled_fail' )
				);

				echo json_encode( $response );
				die();
			}

			// @todo: error that the compiled scheme couldn't be written and redirect
			exit( wds_base_strings( 'acs_write_compiled_fail' ) );
		}

		// add the URI of the sheet to the settings array
		$scheme->uri = $uri;

		if ( $doing_ajax ) {
			$response = array(
				'uri' => $this->maybe_ssl( $scheme->uri ), 
				'message' => wds_base_strings( 'acs_previewing_scheme' )
			);

			echo json_encode( $response );
			die();
		}

		$this->set_option( 'schemes', array( $scheme->slug => $scheme->to_array() ) );

		// switch to the scheme
		update_user_meta( get_current_user_id(), 'admin_color', $scheme->slug );

		wp_redirect( $this->admin_url() . '&updated=true' );
		exit;
	}

	public function wds_save() {

		if( ! current_user_can( 'manage_support_options' )){ return; }

		check_admin_referer( Self::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );

		$scheme = $this->get_color_scheme();

		add_filter( 'scss_keys', 'format_scss_keys', 10 , 1 );

		$wds_settings = get_option( 'webdogs_support' );

		// Gets the unique option id
		if ( isset( $wds_settings['id'] ) ) {
			$option_name = $wds_settings['id'];
		}
		else {
			$option_name = 'webdogs-support';
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
			// add_settings_error( 'webdogs-support', 'color_css', 'Using default color scheme.', 'update-nag dismissable' );
			return true;
		}

		$scss .= "\n\n@import 'colors.css';\n@import '_admin.scss';\n";

		// okay, let's see about getting credentials
		// @todo: what to do about preview
		if ( false === ( $creds = request_filesystem_credentials( $this->admin_url() ) ) ) {
			add_settings_error( 'webdogs-support', 'color_css', wds_base_strings( 'acs_write_compiled_fail' ), 'error' );
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
		if ( ! $wp_filesystem->put_contents( $scss_file, apply_filters( 'custom.scss', $scss ), FS_CHMOD_FILE ) ) {

			// @todo: error that the scheme couldn't be written and redirect
			add_settings_error( 'webdogs-support', 'color_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
			return true;
		}

		// Compile and write!
		require_once( $this->base . '/lib/phpsass/SassParser.php' );
		$sass = new SassParser();
		$css = $sass->toCss( $scss_file );

		if ( ! $wp_filesystem->put_contents( $css_file, $css, FS_CHMOD_FILE) ) {

			add_settings_error( 'webdogs-support', 'color_css', wds_base_strings( 'acs_write_compiled_fail' ), 'update-nag dismissable' );

			return true;
			// @todo: error that the compiled scheme couldn't be written and redirect
			// exit( 'Could not write compiled CSS file.' );
		}

		// add the URI of the sheet to the settings array
		$scheme->uri =  $this->maybe_ssl($uri);

		$this->set_option( 'schemes', array( $scheme->slug => $scheme->to_array() ) );

		// switch to the scheme
		// update_user_meta( get_current_user_id(), 'admin_color', $scheme->slug );

		// add_settings_error( 'webdogs-support', 'color_css', 'the admin color scheme has been updated.', 'update-nag' );
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
							'message' => wds_base_strings( 'acs_copy_file_fail' )
						);

						echo json_encode( $response );
						die();
					}

					// @todo: error that the scheme couldn't be written and redirect
					exit( wds_base_strings( 'acs_copy_file_fail' ) );
				}
			}
		}

		if ( ! file_exists( $upload_dir . "/colors.css" ) ) {
			if ( ! $wp_filesystem->put_contents( $upload_dir . "/colors.css", $wp_filesystem->get_contents( $admin_dir . 'colors.css', FS_CHMOD_FILE) ) ) {
				if ( $doing_ajax ) {
					$response = array(
						'errors' => true,
						'message' => wds_base_strings( 'acs_copy_file_fail' )
					);

					echo json_encode( $response );
					die();
				}

				// @todo: error that the scheme couldn't be written and redirect
				exit( wds_base_strings( 'acs_copy_file_fail' ) );
			}
		}
	}
}


/**
 * Admin Bar Color class
 */
class Webdogs_Support_Admin_Bar {

	private $base;

	public function __construct() {
		$this->base = WEBDOGS_SUPPORT_DIR_PATH . 'admin';
		// add_action( 'wp_before_admin_bar_render', array( $this, 'save_wp_admin_color_schemes_list' ) );

		// add_action( 'admin_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );
		// add_action( 'wp_enqueue_scripts',    array( $this, 'wp_enqueue_style' ) );
		// add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_admin_bar_color' ) );

		// add_action( 'wds_after_validate',     array( $this, 'save_logo_icon_css_file' ), 100 );
	}

	public function wp_enqueue_style(){ ?>
		<style type="text/css" id="logo_icon_style"></style><?php

		if( empty( wds_get_option('logo_icon_css','') ) ) { return; }

		$wp_upload_dir = wp_upload_dir();
		$upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
		$upload_url = $wp_upload_dir['baseurl'] . '/admin-color-scheme';

		// @todo: error handling if this can't be made - needs to be differentiated from already there
		if(!is_dir( $upload_dir )){ return; }

		// @todo: save into another subdirectory for multiple scheme handling
		$css_file = $upload_dir . '/logo-icon.css';
		$uri      = $upload_url . '/logo-icon.css';

		if(!is_file( $css_file )){ return; }

		wp_enqueue_style( 'logo-icon', $this->maybe_ssl( $uri ) );

	}
	
	private function maybe_ssl( $url ) {
		if ( is_ssl() )
			$url = preg_replace( '#^http://#', 'https://', $url );
		return $url;
	}

	public function admin_url() {
		return admin_url( 'admin.php?page=webdogs-support' );
	}

	public function save_logo_icon_css_file() {

		if( ! current_user_can( 'manage_support_options' )){ return; }

		check_admin_referer( Webdogs_Support_Admin_Color_Schemes::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );

		$wds_settings = get_option( 'webdogs_support' );

		// Gets the unique option id
		if ( isset( $wds_settings['id'] ) ) {
			$option_name = $wds_settings['id'];
		}
		else {
			$option_name = 'webdogs-support';
		};

		$logo_icon_font_css = ( isset( $_post[ $option_name ][ 'logo_icon_css' ] ) ) ? $_post[ $option_name ][ 'logo_icon_css' ] : false ;

		// No content Bail
		if(!$logo_icon_font_css){return;}


		// okay, let's see about getting credentials
		// @todo: what to do about preview
		if ( false === ( $creds = request_filesystem_credentials( $this->admin_url() ) ) ) {
			add_settings_error( 'webdogs-support', 'logo_icon_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
			return true;
		}

		// now we have some credentials, try to get the wp_filesystem running
		if ( ! WP_Filesystem( $creds ) ) {
			// our credentials were no good, ask the user for them again
			request_filesystem_credentials( $this->admin_url(), '', true );

			return true;
		}

		/**
		 * Uplaod / male file
		 */

		global $wp_filesystem;

		$wp_upload_dir = wp_upload_dir();
		$upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
		$upload_url = $wp_upload_dir['baseurl'] . '/admin-color-scheme';

		// @todo: error handling if this can't be made - needs to be differentiated from already there
		if(!is_dir( $upload_dir )){
			$wp_filesystem->mkdir( $upload_dir );
		}

		// @todo: save into another subdirectory for multiple scheme handling
		$css_file = $upload_dir . '/logo-icon.css';
		$uri      = $upload_url . '/logo-icon.css';


		// write the custom.scss file
		if ( ! $wp_filesystem->put_contents( $css_file, $logo_icon_font_css, FS_CHMOD_FILE ) ) {

			// @todo: error that the scheme couldn't be written and redirect
			add_settings_error( 'webdogs-support', 'color_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
			return true;
		}
	}

	/**
	 * Save the color schemes list into wp_options table
	 */
	function save_wp_admin_color_schemes_list() {
		global $_wp_admin_css_colors;
		if ( count( $_wp_admin_css_colors ) > 1 && has_action( 'admin_color_scheme_picker' ) ) {
			update_option( 'wp_admin_color_schemes', $_wp_admin_css_colors );
		}
	}
	
	/**
	 * Enqueue the registered color schemes on the front end
	 */
	function enqueue_admin_bar_color() {

		if ( ! is_admin_bar_showing() ) {
			return;
		}
		$user_color = get_user_option( 'admin_color' );
		if ( isset( $user_color ) ) {
			$admin_scheme = Webdogs_Support_Admin_Color_Schemes::get_instance();
			$schemes = apply_filters( 'get_color_scheme_options', $admin_scheme->get_option( 'schemes', array() ) );
			$schemes_slugs = array();

			foreach (array_values( $schemes ) as $scheme ) {
				if( empty( $scheme['slug'] ) ) 
					continue;
				else
					$schemes_slugs[] = $scheme['slug'];
			}
			$schemes_slugs = ( sizeof( $schemes_slugs ) !== sizeof( $schemes ) ) ? wp_list_pluck( $schemes, 'slug' ) : $schemes_slugs;
			$schemes = array_combine( array_values( $schemes_slugs ), array_values( $schemes ) );
			// wp_enqueue_style( $user_color, $schemes[$user_color]['uri'] );
		}
	}
}


class Admin_Color_Scheme {

	protected $id = 1;
	protected $slug;
	protected $name;
	protected $uri;
	protected $accessors = array( 'id', 'slug', 'name', 'uri', 'icon_focus', 'icon_current' );

	// Icon colors for SVG painter:
	// likely temporary placement, 
	// as it will need some more 
	// special handling

	protected $icon_color = '#ffffff';
	protected $icon_base = '#0073aa';
	protected $icon_focus = '#ffffff';
	protected $icon_current = '#ffffff';

	protected $accessor_map = array(
					             'menu_icon'           => 'icon_base',
					             'icon_color'          => 'icon_base',
								 'menu_highlight_icon' => 'icon_focus',
								 'menu_current_icon'   => 'icon_current' );

	public function __construct( $attr = NULL ) {
		// extend accessors
		$admin_scheme = Webdogs_Support_Admin_Color_Schemes::get_instance();
		$this->accessors = array_merge( $this->accessors, array_keys( $admin_scheme->get_colors() ) );

		// set slug
		$this->slug = 'admin_color_scheme_' . $this->id;

		if ( is_array( $attr ) ) {
			foreach ( $this->accessors as $thing ) {
				if ( isset( $attr[$thing] ) && ! empty( $attr[$thing] ) ) {
					$this->{$thing} = $attr[$thing];
				}
			}
			foreach ($this->accessor_map as $thing => $accessor ) {
				if ( isset( $attr[$thing] ) && ! empty( $attr[$thing] ) ) {
					$this->{$accessor} = $attr[$thing];
				}
			}
			if(empty($this->name)){
				$this->name = get_bloginfo( 'name' );
			}
		} else {
			// set defaults
			// @todo: make this really set defaults 
			// for the items that must have a color - what are those?
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
				$value = esc_url_raw( $this->maybe_ssl( $value ) );
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

	private function maybe_ssl( $url ) {
		if ( is_ssl() )
			$url = preg_replace( '#^http://#', 'https://', $url );
		return $url;
	}
}

class Webdogs_Admin_Color_Schemes_Version_Check {
	private static $instance;

	protected function __construct() {
		Self::$instance = $this;
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	public static function get_instance() {
		if ( ! isset( Self::$instance ) ) {
			new self;
		}
		return Self::$instance;
	}

	public function passes() {
		return version_compare( get_bloginfo( 'version' ), '3.8-beta', '>' );
	}

	public function plugins_loaded() {
		if ( ! $this->passes() ) {
			remove_action( 'init', array( Webdogs_Support_Admin_Color_Schemes::get_instance(), 'init' ) );
  		if ( current_user_can( 'activate_plugins' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			}
		}
	}

	public function admin_notices() {
		echo '<div class="updated error"><p>' . __('<strong>Admin Color Scheme</strong> requires WordPress 3.8 or higher, and has thus been <strong>deactivated</strong>. Please update your install and then try again!', 'webdogs-support' ) . '</p></div>';
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
}
