<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       WEBDOGS.COM
 * @since      1.0.0
 *
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 */
defined('WEBDOGS_VERSION') and 
defined('WEBDOGS_SUPPORT_SLUG') or die; 

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Webdogs_Support
 * @subpackage Webdogs_Support/includes
 * @author     WEBDOGS Support Team <thedogs@webdogs.com>
 */
class Webdogs_Support {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Webdogs_Support_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->plugin_name = WEBDOGS_SUPPORT_SLUG;
        $this->version     = WEBDOGS_VERSION;

        $this
             ->load_dependencies()
             ->set_locale()
             ->define_hostname_markers_hooks()
             ->define_maintainance_notification_hooks()
             ->define_endpoint_hooks()
             ->define_login_logo_hooks()
             ->define_admin_color_scheme_hooks()
             ->define_plugin_activation_hooks()
             ->define_media_uploader_hooks()
             ->define_dashboard_widget_hooks()
                ->loader
                ->add_action('plugins_loaded', $this, 'init', 0);

    }

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function init(){
        return $this
             ->define_framework_hooks()
             ->define_admin_hooks()
             ->define_public_hooks()
             ->run();

    }


    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Webdogs_Support_Loader. Orchestrates the hooks of the plugin.
     * - Webdogs_Support_i18n. Defines internationalization functionality.
     * - Webdogs_Support_Admin. Defines all hooks for the admin area.
     * - Webdogs_Support_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {


        /**
         * Template functions for outputting 
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions-webdogs-support-template.php';

        /**
         * Common functions for transforming data and plugin   
         * context/state reporting.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions-webdogs-support-common.php';

        /**
         * Common functions for transforming data and plugin   
         * context/state reporting.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/options.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-i18n.php';

        /**
         * The class responsible for the hostname markers.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-hostname-markers.php';

        /**
         * The class responsible for scheduling and sending maintainance notifications.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-maintainance-notifications.php';

        /**
         * The class responsible for providing a JSON data endpoint.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-endpoint.php';

        /**
         * The class responsible for customization of the login page logo and background.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-webdogs-support-login-logo.php';

        /**
         * The class responsible for customization of the admin color schemes.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-admin-color-schemes.php';

        /**
         * The class responsible for plugin installation, activation and upkeep.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-plugin-activation.php';

        /**
         * The class responsible for setting option defaults.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-webdogs-support-framework.php';
        
        /**
         * The class responsible for the options page, admin menu items, and options regisration.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-framework-admin.php';

        /**
         * The class responsible for composing option screens and forms.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-interface.php';

        /**
         * The class responsible for handleing media upload support for the interface.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-media-uploader.php';

        /**
         * The class responsible for the sanitization of posted oprion values.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-sanitization.php';

        /**
         * The class responsible for displaying and handeling interactions with support.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-dashboard-widget.php';
        
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-webdogs-support-admin.php';

        /**
         * The class responsible for defining all actions that occur in the frontend.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-webdogs-support-public.php';


        $this->loader = new Webdogs_Support_Loader();


        return $this;
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Webdogs_Support_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {

        $plugin_i18n = new Webdogs_Support_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

        return $this;
    }

    /**
     * Register all of the hooks related to hostname markers.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_hostname_markers_hooks() {

        $plugin_hostname_markers = new Webdogs_Support_Hosetname_Markers();

        $this->loader->add_action( 'set_current_user', $plugin_hostname_markers, 'webdogs_user_capability' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_hostname_markers, 'webdogs_enqueue_domain_flags' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_hostname_markers, 'webdogs_enqueue_domain_flags' );
        $this->loader->add_filter( 'admin_bar_menu', $plugin_hostname_markers, 'webdogs_howdy', 25, 1 );
    
        return $this;
    }

    /**
     * Register all of the hooks related to maintainance notifications.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_maintainance_notification_hooks() {

        $plugin_maintainance_notifications = get_class( new Webdogs_Support_Maintenance_Notifications );

        $this->loader->add_action( 'init', $plugin_maintainance_notifications, 'init' );
        $this->loader->add_action( 'admin_notices', $plugin_maintainance_notifications, 'admin_notices' );
        $this->loader->add_action( 'wds_after_validate', $plugin_maintainance_notifications, 'report_schedule_changes' );
        $this->loader->add_action( 'wds_scheduled_notification', $plugin_maintainance_notifications, 'send_maintenance_notification' );
        $this->loader->add_action( 'wds_test_maintenance_notification', $plugin_maintainance_notifications, 'send_test_maintenance_notification', 10, 1 );

        // wds_l10n
        $this->loader->add_filter( 'wds_l10n', $plugin_maintainance_notifications, 'maintenance_l10n', 12, 1 );

        return $this;

    }

    /**
     * Register all of the hooks for providing a JSON data endpoint.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_endpoint_hooks() {

        $plugin_endpoint = new Webdogs_Support_Endpoint();

        $this->loader->add_action( 'init', $plugin_endpoint, 'add_endpoint' );
        $this->loader->add_action( 'template_redirect', $plugin_endpoint, 'handle_endpoint' );
        $this->loader->add_action( 'wds_scheduled_notification', $plugin_endpoint, 'post_site_data', 0 );
        $this->loader->add_action( 'wds_after_validate', $plugin_endpoint, 'post_site_data', 0 );
        $this->loader->add_action( 'wds_test_maintenance_notification', $plugin_endpoint, 'post_site_data', 0 );

        return $this;
    }

    /**
     * Initialize the login logo settings.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_login_logo_hooks() {

        $plugin_login_logo = new Webdogs_Login_Logo();
        $plugin_login_logo->init();

        return $this;

    }

    /**
     * Register all of the hooks related to the admin color scheme.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_color_scheme_hooks() {

        $plugin_admin_color_schemes = new Webdogs_Support_Admin_Color_Schemes();

        $this->loader->add_action( 'init', $plugin_admin_color_schemes, 'init' );
        $this->loader->add_action( 'admin_init', $plugin_admin_color_schemes, 'admin_init' );

        // $this->loader->add_action( 'admin_menu', $plugin_admin_color_schemes, 'admin_menu' );
        $this->loader->add_action( 'admin_post_admin-color-schemes-save', $plugin_admin_color_schemes, 'save' );
        $this->loader->add_action( 'wp_ajax_admin-color-schemes-save', $plugin_admin_color_schemes, 'save' );
        $this->loader->add_action( 'wds_after_validate', $plugin_admin_color_schemes, 'wds_save', 10, 1 );

        // filter cplor scheme options
        $this->loader->add_filter( 'get_color_scheme_options', 'Webdogs_Support_Admin_Color_Schemes', 'filter_color_scheme_options', 10, 1 );

        // Override the user's admin color scheme.
        $this->loader->add_filter( 'get_user_option_admin_color', 'Webdogs_Support_Admin_Color_Schemes', 'must_use_admin_color', 10, 1 );
        
        // Add sass_localize_script.
        $this->loader->add_filter( 'wds_localize_script', 'Webdogs_Support_Admin_Color_Schemes', 'sass_localize_script', 10, 1 );

        // Hide the Admin Color Scheme field from users who can't set a forced color scheme.
        $this->loader->add_action( 'admin_color_scheme_picker', $plugin_admin_color_schemes, 'hide_admin_color_input', 8 );


        $plugin_admin_bar = new Webdogs_Support_Admin_Bar();

        $this->loader->add_action( 'wp_before_admin_bar_render', $plugin_admin_bar, 'save_wp_admin_color_schemes_list' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_bar, 'wp_enqueue_style' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin_bar, 'wp_enqueue_style' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin_bar, 'enqueue_admin_bar_color' );
        $this->loader->add_action( 'wds_after_validate', $plugin_admin_bar, 'save_logo_icon_css_file' , 100 );

        return $this;
    }

    /**
     * Register all of the hooks responsible for plugin installation, activation and upkeep.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_plugin_activation_hooks() {

        $plugin_activation = Webdogs_Plugin_Activation::get_instance();

        return $this;
    }

    /**
     * Register all of the hooks responsible for displaying and handeling options and settings.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_framework_hooks() {
        // Instantiate the main plugin class.
        $options_framework = new Webdogs_Options();
        $this->loader->add_action( 'admin_init', $options_framework, 'set_support_option' );

        // Instantiate the options page.
        $options_framework_admin = new Webdogs_Admin();
        // Gets options to load
        $options = &Webdogs_Options::_wds_options();

        // Checks if options are available
        if ( $options ) {

            // Add the options page and menu item.
            $this->loader->add_action( 'admin_menu', $options_framework_admin, 'add_custom_options_page' );

            // Add the required scripts and styles
            $this->loader->add_action( 'admin_enqueue_scripts', $options_framework_admin, 'enqueue_admin_styles', 10, 1 );
            $this->loader->add_action( 'admin_enqueue_scripts', $options_framework_admin, 'enqueue_admin_scripts', 10, 1 );

            // wds_localize_script
            $this->loader->add_filter( 'wds_localize_script', $options_framework_admin, 'localize_script', 12, 1 );

            // Settings need to be registered after admin_init
            $this->loader->add_action( 'admin_init', $options_framework_admin, 'settings_init' );

            // Adds options menu to the admin bar
            $this->loader->add_action( 'wp_before_admin_bar_render', $options_framework_admin, 'wds_admin_bar' );
            $this->loader->add_action( 'wp_before_admin_bar_render', $options_framework_admin, 'add_adminbar_sitename_logo' );

        } else {
            // Display a notice if options aren't present in the theme
            $this->loader->add_action( 'admin_notices', $options_framework_admin, 'options_notice' );
            $this->loader->add_action( 'admin_init', $options_framework_admin, 'options_notice_ignore' );
        }

        return $this;
    }

    /**
     * Register all of the hooks for media upload support.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_media_uploader_hooks() {

        $plugin_media_uploader = new Webdogs_Media_Uploader();

        $this->loader->add_action( 'init', $plugin_media_uploader, 'init' );

        // wds_l10n
        $this->loader->add_filter( 'wds_l10n', $plugin_media_uploader, 'media_uploader_l10n', 12, 1 );

        return $this;

    }

    /**
     * Register all of the hooks responsible displaying and handeling interactions with support.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_dashboard_widget_hooks() {

        $plugin_dashboard_widget = get_class( new Webdogs_Support_Dashboard_Widget );

        $this->loader->add_action( 'wp_dashboard_setup', $plugin_dashboard_widget, 'add_dashboard_widget' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_dashboard_widget, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_ajax_webdogs_result_dashboard', $plugin_dashboard_widget, 'result_dashboard' );
        $this->loader->add_action( 'wp_ajax_webdogs_reset_dashboard', $plugin_dashboard_widget, 'reset_dashboard' );
        
        // wds_l10n
        $this->loader->add_filter( 'wds_l10n', $plugin_dashboard_widget, 'dashboard_widget_l10n', 12, 1 );

        return $this;
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // if ( ! is_admin() ) return;

        $plugin_admin = new Webdogs_Support_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        return $this;

    }

    /**
     * Register all of the hooks responsible for frontend functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // if ( is_admin() ) return;

        $plugin_public = new Webdogs_Support_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        return $this;

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Webdogs_Support_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
