<?php
/**
 * Options Framework
 *
 * @package   Options Framework
 * @author    Devin Price <devin@wptheming.com>
 * @license   GPL-2.0+
 * @link      http://wptheming.com
 * @copyright 2010-2016 WP Theming
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
function wds_init() {

	// Instantiate the login logo plugin class.
	// Loads the required Options Framework classes.
	require_once plugin_dir_path( __FILE__ ) . 'options.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-endpoint.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-login-logo.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-admin-color-schemes.php';

	// Pull in the plugin classes and initialize
	$options_framework_endpoint = Webdogs_Endpoint::register();

	// Pull in the plugin classes and initialize
	$options_framework_login_logo = new Webdogs_Login_Logo;
	$options_framework_login_logo->init();

	// Pull in the plugin classes and initialize
	$options_framework_admin_color_schemes = Webdogs_Admin_Color_Schemes::get_instance();

	// Pull in the version checker and initialize
	$options_framework_admin_color_schemes_version_check = Webdogs_Admin_Color_Schemes_Version_Check::get_instance();

	//  If user can't edit theme options, exit
	if ( ! current_user_can( 'manage_options' ) ) return;

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-plugin-activation.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-framework.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-framework-admin.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-interface.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-media-uploader.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-options-sanitization.php';

	// Instantiate the plugin activation class.
	// Ensure only one instance of the class is ever invoked.
	$options_framework_plugin_activation = Webdogs_Plugin_Activation::get_instance();


	// Instantiate the main plugin class.
	$options_framework = new Webdogs_Options;
	$options_framework->init();

	// Instantiate the options page.
	$options_framework_admin = new Webdogs_Admin;
	$options_framework_admin->init();

	// Instantiate the media uploader class
	$options_framework_media_uploader = new Webdogs_Media_Uploader;
	$options_framework_media_uploader->init();

	add_action( 'init', function(){
		// Load translation files
		load_plugin_textdomain( 'webdogs-support', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	} );

}

if ( did_action( 'plugins_loaded' ) ) {
	wds_init();
} else {
	add_action( 'plugins_loaded', 'wds_init' );
}
