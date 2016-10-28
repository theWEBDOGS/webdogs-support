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
			'link' => __( 'Link', 'options-framework' ),
		);

		$this->colors['icon'] = array(
			'menu_icon' => __( 'Base icon', 'options-framework' ),
			'menu_highlight_icon' => __( 'Focus icon', 'options-framework' ),
			'menu_current_icon' => __( 'Current icon', 'options-framework' )
		);

		$this->colors['advanced'] = array(
			'button_color' => __( 'Button', 'options-framework' ),
			'text_color' => __( 'Text (over Base)', 'options-framework' ),
			'body_background' => __( 'Body background', 'options-framework' ),
			'link_focus' => __( 'Link interaction', 'options-framework' ),
			'form_checked' => __( 'Checked form controls', 'options-framework' ),
			'menu_background' => __( 'Menu background', 'options-framework' ),
			'menu_text' => __( 'Menu text', 'options-framework' ),
			// 'menu_icon' => __( 'Menu icon', 'options-framework' ),
			'menu_highlight_background' => __( 'Menu highlight background', 'options-framework' ),
			'menu_highlight_text' => __( 'Menu highlight text', 'options-framework' ),
			// 'menu_highlight_icon' => __( 'Menu highlight icon', 'options-framework' ),
			'menu_current_background' => __( 'Menu current background', 'options-framework' ),
			'menu_current_text' => __( 'Menu current text', 'options-framework' ),
			// 'menu_current_icon' => __( 'Menu current icon', 'options-framework' ),
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


		add_filter( 'get_color_scheme_options',    array( __CLASS__, 'filter_color_scheme_options'), 10, 1 );

		// Override the user's admin color scheme.
		add_filter( 'get_user_option_admin_color', array( __CLASS__, 'must_use_admin_color'           ),10, 1 );

		// Hide the Admin Color Scheme field from users who can't set a forced color scheme.
		add_action( 'admin_color_scheme_picker',   array( $this, 'hide_admin_color_input'      ), 8 );

		/*
		 * Note: not bothering with preventing users from being able to save a value for admin_color
		 * since it's not really a big deal if they do.
		 */
		
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

			$production = ( function_exists( 'is_wpe' ) && is_wpe() ) ? "wpengine_tc" : "webdogs_wpe" ;
			$production = "webdogs_wpe";
			$admin_color_scheme = ( wds_is_production_site() ) ? $production : "webdogs_ds" ;

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


	public function admin_menu() {
		// $hook = add_management_page( 'Admin Color Scheme', 'Admin Colors', 'manage_support_options', 'options-framework', array( $this, 'admin_page' ) );
		// add_action( 'load-' . $hook, array( $this, 'load' ) );
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

	public function get_Sass_JS() {
		if( ! is_admin() || ! current_user_can('manage_support_options' ) ) return;

		$wp_upload_dir = wp_upload_dir();

		$upload_url = is_dir( $wp_upload_dir['baseurl'] . '/admin-color-scheme' ) ? $upload_url : ABSPATH . '/wp-admin/css/colors/';
	 ?>
<script type="text/javascript">



/*! sass.js - v0.9.13 (236485c) - built 2016-09-25
  providing libsass 3.3.6 (3ae9a20)
  via emscripten 1.36.5 (8e29cf1)
 */

(function (root, factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    define([], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    root.Sass = factory();
  }
}(this, function () {
/*global document*/
// identify the path sass.js is located at in case we're loaded by a simple
// <script src="path/to/sass.js">
// this path can be used to identify the location of
// * sass.worker.js from sass.js
// * libsass.js.mem from sass.sync.js
// see https://github.com/medialize/sass.js/pull/32#issuecomment-103142214
// see https://github.com/medialize/sass.js/issues/33
var SASSJS_RELATIVE_PATH = (function() {
  'use strict';

  // in Node things are rather simple
  if (typeof __dirname !== 'undefined') {
    return __dirname;
  }

  // we can only run this test in the browser,
  // so make sure we actually have a DOM to work with.
  if (typeof document === 'undefined' || !document.getElementsByTagName) {
    return null;
  }

  // http://www.2ality.com/2014/05/current-script.html
  var currentScript = document.currentScript || (function() {
    var scripts = document.getElementsByTagName('script');
    return scripts[scripts.length - 1];
  })();

  var path = currentScript && currentScript.src;
  if (!path) {
    return null;
  }

  // [worker] make sure we're not running in some concatenated thing
  if (path.slice(-8) === '/sass.js') {
    return path.slice(0, -8);
  }

  // [sync] make sure we're not running in some concatenated thing
  if (path.slice(-13) === '/sass.sync.js') {
    return path.slice(0, -13);
  }

  return null;
})() || '.';

/*global Worker, SASSJS_RELATIVE_PATH*/
'use strict';

var noop = function(){};
var slice = [].slice;
// defined upon first Sass.initialize() call
var globalWorkerUrl;

function Sass(workerUrl) {
  if (!workerUrl && !globalWorkerUrl) {
    /*jshint laxbreak:true */
    throw new Error(
      'Sass needs to be initialized with the URL of sass.worker.js - '
      + 'either via Sass.setWorkerUrl(url) or by new Sass(url)'
    );
    /*jshint laxbreak:false */
  }

  if (!globalWorkerUrl) {
    globalWorkerUrl = workerUrl;
  }

  // bind all functions
  // we're doing this because we used to have a single hard-wired instance that allowed
  // [].map(Sass.removeFile) and we need to maintain that for now (at least until 1.0.0)
  for (var key in this) {
    if (typeof this[key] === 'function') {
      this[key] = this[key].bind(this);
    }
  }

  this._callbacks = {};
  this._worker = new Worker(workerUrl || globalWorkerUrl);
  this._worker.addEventListener('message', this._handleWorkerMessage, false);
}

// allow setting the workerUrl before the first Sass instance is initialized,
// where registering the global workerUrl would've happened automatically
Sass.setWorkerUrl = function(workerUrl) {
  globalWorkerUrl = workerUrl;
};

Sass.style = {
  nested: 0,
  expanded: 1,
  compact: 2,
  compressed: 3
};

Sass.comments = {
  'none': 0,
  'default': 1
};

Sass.prototype = {
  style: Sass.style,
  comments: Sass.comments,

  destroy: function() {
    this._worker && this._worker.terminate();
    this._worker = null;
    this._callbacks = {};
    this._importer = null;
  },

  _handleWorkerMessage: function(event) {
    if (event.data.command) {
      this[event.data.command](event.data.args);
    }

    this._callbacks[event.data.id] && this._callbacks[event.data.id](event.data.result);
    delete this._callbacks[event.data.id];
  },

  _dispatch: function(options, callback) {
    if (!this._worker) {
      throw new Error('Sass worker has been terminated');
    }

    options.id = 'cb' + Date.now() + Math.random();
    this._callbacks[options.id] = callback;
    this._worker.postMessage(options);
  },

  _importerInit: function(args) {
    // importer API done callback pushing results
    // back to the worker
    var done = function done(result) {
      this._worker.postMessage({
        command: '_importerFinish',
        args: [result]
      });
    }.bind(this);

    try {
      this._importer(args[0], done);
    } catch(e) {
      done({ error: e.message });
      throw e;
    }
  },

  importer: function(importerCallback, callback) {
    if (typeof importerCallback !== 'function' && importerCallback !== null) {
      throw new Error('importer callback must either be a function or null');
    }

    // callback is executed in the main EventLoop
    this._importer = importerCallback;
    // tell worker to activate importer callback
    this._worker.postMessage({
      command: 'importer',
      args: [Boolean(importerCallback)]
    });

    callback && callback();
  },
};

var commands = 'writeFile readFile listFiles removeFile clearFiles lazyFiles preloadFiles options compile compileFile';
commands.split(' ').forEach(function(command) {
  Sass.prototype[command] = function() {
    var callback = slice.call(arguments, -1)[0];
    var args = slice.call(arguments, 0, -1);
    if (typeof callback !== 'function') {
      args.push(callback);
      callback = noop;
    }

    this._dispatch({
      command: command,
      args: args
    }, callback);
  };
});

// automatically set the workerUrl in case we're loaded by a simple
// <script src="path/to/sass.js">
// see https://github.com/medialize/sass.js/pull/32#issuecomment-103142214
Sass.setWorkerUrl('<?php echo plugins_url( 'js/sass.worker.js',  dirname( __FILE__ ) ) ?>');
return Sass;
}));
var SassWorker = new Sass();
    SassWorker.options('defaults');
	SassWorker.options({ style: SassWorker.style.compressed });

SassWorker.writeFile('variables', <?php echo json_encode( file_get_contents($upload_url."/_variables.scss") ); ?>);
SassWorker.writeFile('mixins', <?php echo json_encode( file_get_contents($upload_url."/_mixins.scss") ) ; ?>);
SassWorker.writeFile('_admin.scss', <?php echo json_encode( apply_filters( '_admin.scss', file_get_contents( $upload_url."/_admin.scss") ) ); ?>);

</script>
	<?php }




	public function admin_enqueue_scripts() {
		// Compile and write!
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
		return admin_url( 'admin.php?page=options-framework' );
	}

	public function save() {
		current_user_can( 'manage_support_options' ) || die;
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
					'message' => wds_base_strings( 'acs_selections_preview' )//__( 'Please make more selections to preview the color scheme.', 'options-framework' ),
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

	public function of_save() {

		if( ! current_user_can( 'manage_support_options' )){ return; }

		check_admin_referer( self::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );

		$scheme = $this->get_color_scheme();

		add_filter( 'scss_keys', 'format_scss_keys', 10 , 1 );

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
			add_settings_error( 'options-framework', 'color_css', wds_base_strings( 'acs_write_compiled_fail' ), 'error' );
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
			add_settings_error( 'options-framework', 'color_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
			return true;
		}

		// Compile and write!
		require_once( $this->base . '/lib/phpsass/SassParser.php' );
		$sass = new SassParser();
		$css = $sass->toCss( $scss_file );

		if ( ! $wp_filesystem->put_contents( $css_file, $css, FS_CHMOD_FILE) ) {

			add_settings_error( 'options-framework', 'color_css', wds_base_strings( 'acs_write_compiled_fail' ), 'update-nag dismissable' );

			return true;
			// @todo: error that the compiled scheme couldn't be written and redirect
			// exit( 'Could not write compiled CSS file.' );
		}

		// add the URI of the sheet to the settings array
		$scheme->uri =  $this->maybe_ssl($uri);

		$this->set_option( 'schemes', array( $scheme->slug => $scheme->to_array() ) );

		// switch to the scheme
		// update_user_meta( get_current_user_id(), 'admin_color', $scheme->slug );

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
class Admin_Bar_Color {

	private $base;

	function __construct() {
		$this->base = WEBDOGS_SUPPORT_DIR . '/options-framework';
		add_action( 'wp_before_admin_bar_render', array( $this, 'save_wp_admin_color_schemes_list' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'wp_enqueue_style' ) );
		add_action( 'wp_enqueue_scripts',    array( $this, 'wp_enqueue_style' ) );
		add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_admin_bar_color' ) );

		add_action( 'optionsframework_after_validate',     array( $this, 'save_logo_icon_css_file' ), 100 );
	}

	public function wp_enqueue_style(){

		if( empty( of_get_option('logo_icon_css','') ) ) { return; }

		$wp_upload_dir = wp_upload_dir();
		$upload_dir = $wp_upload_dir['basedir'] . '/admin-color-scheme';
		$upload_url = $wp_upload_dir['baseurl'] . '/admin-color-scheme';

		// @todo: error handling if this can't be made - needs to be differentiated from already there
		if(!is_dir( $upload_dir )){ return; }

		// @todo: save into another subdirectory for multiple scheme handling
		$css_file = $upload_dir . '/logo-icon.css';
		$uri      = $upload_url . '/logo-icon.css';

		if(!is_file( $css_file )){ return; }

		wp_enqueue_style( 'logo-icon', $uri );

	}

	public function admin_url() {
		return admin_url( 'admin.php?page=options-framework' );
	}

	public function save_logo_icon_css_file() {

		if( ! current_user_can( 'manage_support_options' )){ return; }

		check_admin_referer( Options_Framework_Admin_Color_Schemes::NONCE, '_acs_ofnonce' );
		$_post = stripslashes_deep( $_POST );

		$optionsframework_settings = get_option( 'optionsframework' );

		// Gets the unique option id
		if ( isset( $optionsframework_settings['id'] ) ) {
			$option_name = $optionsframework_settings['id'];
		}
		else {
			$option_name = 'optionsframework';
		};

		$logo_icon_font_css = ( isset( $_post[ $option_name ][ 'logo_icon_css' ] ) ) ? $_post[ $option_name ][ 'logo_icon_css' ] : false ;

		// No content Bail
		if(!$logo_icon_font_css){return;}


		// okay, let's see about getting credentials
		// @todo: what to do about preview
		if ( false === ( $creds = request_filesystem_credentials( $this->admin_url() ) ) ) {
			add_settings_error( 'options-framework', 'logo_icon_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
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
			add_settings_error( 'options-framework', 'color_css', wds_base_strings( 'acs_write_custom_fail' ), 'error' );
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
		/*$custom_logo_icon = of_get_option( 'logo_icon', false );


	    if( $custom_logo_icon ) {
	    	// not an svg
	    	// use image
	    	if( stripos( $custom_logo_icon, '.svg') === false ) {
	    		// return;
	    		// $image = $custom_logo_icon;

    		//eles use SVG
	    	} else {

		    	$data = file_get_contents( $custom_logo_icon );
		    	// return $data;`

		    	if( ! $data ) { return false; }

				$iconName = 'new-icon';
				$iconCode = 'xf226';

				require_once( $this->base . '/lib/svg-icon-font-generator/IconFontGenerator.php' );

				$generator = new IconFontGenerator;
				$generator->generateFromString( $data, $iconName, $iconCode, array(), FALSE );
				var_export($generator->getFont()->getXML());
			}
		}*/

		if ( ! is_admin_bar_showing() ) {
			return;
		}
		$user_color = get_user_option( 'admin_color' );
		if ( isset( $user_color ) ) {
			$admin_scheme = Options_Framework_Admin_Color_Schemes::get_instance();
			$schemes = apply_filters( 'get_color_scheme_options', $admin_scheme->get_option( 'schemes', array() ) );
			$schemes_slugs = wp_list_pluck( $schemes, 'slug' );
			$schemes = array_combine( array_values( $schemes_slugs ), array_values( $schemes ) );
			// wp_enqueue_style( $user_color, $schemes[$user_color]['uri'] );
			/*print_r($user_color);
			var_export($schemes[$user_color]['uri']);*/
		}


		//<style type="text/css" id="logo_icon_style">
		// echo html_entity_decode(of_get_option('logo_icon_css',''));
		//	</style> -->
	}
}

$admin_bar_color = new Admin_Bar_Color();


class Admin_Color_Scheme {
	// possibly-temporary default
	protected $id = 1;
	protected $slug;
	protected $name;
	protected $uri;
	protected $accessors = array( 'id', 'slug', 'name', 'uri', 'icon_focus', 'icon_current' );

	// Icon colors for SVG painter - likely temporary placement, as it will need some more special handling
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
