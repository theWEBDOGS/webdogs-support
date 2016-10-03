<?php
/*
Package: Login Logo - SVG
Description: Drop a SVG file named <code>login-logo.svg</code> into your <code>wp-content</code> directory. This simple plugin takes care of the rest, with zero configuration. Transparent backgrounds work best. Crop it tight, with a width of 300x300 pixels or 1:1, for best results.
Version: 0.7 a.1WD
License: GPL
Plugin URI: 
Author: Adapted for SVG: JVC WEBDOGS
Author URI: 

==========================================================================

Copyright 2011-2012  Mark Jaquith

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

class Options_Framework_Login_Logo {
	static $instance;
	const CUTOFF = 312;
	var $logo_locations;
	var $logo_location;
	var $width = 0;
	var $height = 0;
	var $original_width;
	var $original_height;
	var $logo_size;
	var $logo_file_exists;

	public function __construct() {
		self::$instance = $this;
		add_action( 'login_head', array( $this, 'login_head' ) );
	}

	public function init() {
		global $blog_id;
		$this->logo_locations = array();
		if ( is_multisite() && function_exists( 'get_current_site' ) ) {
			// First, see if there is one for this specific site (blog)
			$this->logo_locations['site'] = array(
				'path' => WP_CONTENT_DIR . '/login-logo-site-' . $blog_id . '.png',
				'url' => $this->maybe_ssl( content_url( 'login-logo-site-' . $blog_id . '.png' ) )
			);

			// Next, we see if there is one for this specific network
			$site = get_current_site(); // Site = Network? Ugh.
			if ( $site && isset( $site->id ) ) {
				$this->logo_locations['network'] = array(
					'path' => WP_CONTENT_DIR . '/login-logo-network-' . $site->id . '.png',
					'url' => $this->maybe_ssl( content_url( 'login-logo-network-' . $site->id . '.png' ) )
					);
			}
		} elseif( ! is_null( of_get_option('login_logo_css', null ) ) && is_array( of_get_option('login_logo_css', null ) ) ) {
			
			$background = of_get_option('login_logo_css', null );
			$image = (isset($background['image'])) ? $background['image'] : "";
			$uploads = wp_upload_dir();
			$path = (isset($image)) ? str_replace( $this->maybe_ssl( WP_CONTENT_URL ), WP_CONTENT_DIR, $this->maybe_ssl( $image ) ) : "";
			
			$this->logo_locations['option'] =  array(
				'path' => $this->maybe_ssl( $path ),
				'url' => $this->maybe_ssl( $image )
			);
			// var_export($this->logo_locations['option']);
		}

		// Finally, we do a global lookup
		$this->logo_locations['global'] =  array(
			'path' => WP_CONTENT_DIR . '/login-logo.svg',
			'url' => $this->maybe_ssl( content_url( 'login-logo.svg' ) )
		);

	}

	private function maybe_ssl( $url ) {
		if ( is_ssl() )
			$url = preg_replace( '#^http://#', 'https://', $url );
		return $url;
	}

	private function logo_file_exists() {
		if ( ! isset( $this->logo_file_exists ) ) {
			foreach ( $this->logo_locations as $location ) {
				if ( file_exists( $location['path'] ) ) {
					$this->logo_file_exists = true;
					$this->logo_location = $location;
					break;
				} else {
					$this->logo_file_exists = false;
				}
			}
		}
		return !! $this->logo_file_exists;
	}

	private function get_location( $what = '' ) {
		if ( $this->logo_file_exists() ) {
			if ( 'path' == $what )
				return $this->logo_location[$what];
			elseif ( 'url' == $what )
				return $this->logo_location[$what] . '?v=' . filemtime( $this->logo_location['path'] );
			else
				return $this->logo_location;
		}
		return false;
	}

	private function css3( $rule, $value ) {
		foreach ( array( '', '-o-', '-webkit-', '-khtml-', '-moz-', '-ms-' ) as $prefix ) {
			echo $prefix . $rule . ': ' . $value . '; ';
		}
	}

	public function login_headerurl() {
		return esc_url( trailingslashit( get_bloginfo( 'url' ) ) );
	}

	public function login_headertitle() {
		return esc_attr( get_bloginfo( 'name' ) );
	}

	public function login_head() {

		// $this->init();

		if ( ! $this->logo_file_exists() ) return;

		add_filter( 'login_headerurl', array( $this, 'login_headerurl' ) );
		add_filter( 'login_headertitle', array( $this, 'login_headertitle' ) );

		$background_defaults = array(
		'color' => '',
		'image' => $this->get_location('url'),
		'repeat' => 'no-repeat',
		'position' => 'bottom center',
		'attachment'=>'scroll' );

		$format = array(
			'image' => "background:url(%s) %s %s %s;%s",
			'color' => "background-color:%s;",
			'none' => "background:none;",
			'height' => "height:%dpx;",
			'margin-bottom' => "margin:-10px 10px %spx;"
		);

		$login_background  = $body_background = "";

		$background        = of_get_option('login_logo_css', array( 'color' => '', 'image' => '', 'repeat' => 'no-repeat', 'position' => 'bottom center', 'attachment' => 'scroll' ) );
		$background_height = of_get_option('login_logo_height', 100 );
		$margin_bottom     = of_get_option('login_logo_bottom_margin', '10' );

        if ($background) :
	    	$background = wp_parse_args( $background, $background_defaults );

			$background_color  = empty($background['color']) ? "" : sprintf( $format['color'], esc_attr( $background['color' ] ) );
			$background_repeat = empty($background['repeat']) ? "" : $background['repeat'];

			$login_background = sprintf( $format['image'], esc_url_raw( $this->get_location('url') ), esc_attr( $background_defaults['repeat'] ), esc_attr( $background_defaults['position'] ), esc_attr( $background_defaults['attachment'] ), "" );
	        $body_background  = esc_attr( $background_color );
			/*switch ($background_repeat) {
				case 'no-repeat':
					$login_background = sprintf( $format['image'], esc_url_raw( $this->get_location('url') ), esc_attr( $background['repeat'] ), esc_attr( $background['position'] ), esc_attr( $background['attachment'] ), "" );
			        $body_background  = esc_attr( $background_color );
	        
					break;
				
				case 'repeat':
				default:
					$login_background = esc_attr( $format['none'] );
			        $body_background  = sprintf( $format['image'], esc_url_raw( $this->get_location('url') ), esc_attr( $background['repeat'] ), esc_attr( $background['position'] ), esc_attr( $background['attachment'] ), esc_attr( $background_color ) );
	        
					break;
			}*/
	        $background_height = sprintf( $format['height'], esc_attr( $background_height ) ); 
	        $margin_bottom = sprintf( $format['margin-bottom'], esc_attr( $margin_bottom ) ); 
	?>
	<!-- Login Logo plugin for WordPress: http://txfx.net/wordpress-plugins/login-logo/ -->
	<style type="text/css">
		body, html {
			<?php echo $body_background; ?>
		}
		.login h1 a {
			<?php echo $login_background; ?> 
			width: 300px;
			<?php echo $margin_bottom; ?> 
			<?php echo $background_height; ?> 
			background-size: contain;
		}
	</style>

	<?php endif;

	}
}

// Bootstrap
// new Options_Framework_Login_Logo;
