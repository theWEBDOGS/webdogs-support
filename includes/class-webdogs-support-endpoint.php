<?php

defined( 'WPINC' ) or die;

/**
 * Class Simple_Json_Api
 */
class Webdogs_Support_Endpoint {

    /**
     * The top level argument for the endpoint.
     * ex http://example.com/myjson/post/1
     *
     * @var string
     */
    public $endpoint_base = 'wds-json';

    /**
     * Only provide json data for the objects in this array.
     *
     * @var array
     */
    public $allowed_objects = array( 'bloginfo', 'core', 'options', 'plugins', 'themes', 'updates' );

    /** LIVE
     * Hook the plugin into WordPress
     */
    static public function register(){

        $plugin = new Self();

        add_action( 'init', array( $plugin, 'add_endpoint' ) );
        add_action( 'template_redirect', array( $plugin, 'handle_endpoint' ) );
        add_action( 'wds_scheduled_notification', array( $plugin, 'post_site_data' ), 0 );
        add_action( 'wds_after_validate', array( $plugin, 'post_site_data' ), 0 );
        add_action( 'wds_test_maintenance_notification', array( $plugin, 'post_site_data' ), 0 );

        return $plugin;
    }
    /**
     * Create an array of data for a single post that will be part
     * of the json response.
     *
     * @param $post
     */
    public function post_site_data(){

        $data = $this->make_json_data();

        $endpoint = home_url( '/wds-json' );
        $nonce = substr( wp_hash( 'wds_site|' . $endpoint . '|webdogs', 'nonce' ), -12, 10 );
        $token = $this->endpoint_encode( $nonce, $endpoint );

        $WDAPI = new WEBDOGS_API( array( 'wds_token' => $token, 'wds_nonce' => $nonce, 'wds_domain' => home_url() ) );
        return $WDAPI->post();
    }

    /**
     * Create an array of data for a single post that will be part
     * of the json response.
     *
     * @param $post
     */
    public function make_json_data( $object = null ){

        if( false !== ( $data = get_transient( 'WDS_Sync_Data' ) ) ) {
            return $data;
        }

        if ( ! function_exists( 'wp_version_check' ) ) require_once ABSPATH . 'wp-includes/update.php'; 

        if ( ! function_exists( 'wp_prepare_themes_for_js' ) ) require_once ABSPATH . 'wp-admin/includes/theme.php';

        if ( ! function_exists( 'get_core_updates' ) ) require_once ABSPATH . 'wp-admin/includes/update.php';

        // check for updates
         wp_version_check( array(), true );
        wp_update_plugins( array() );
         wp_update_themes( array() );


        $data = ( false !== ( $site = new WDS_Site( array(), false, array_map( 'ucfirst', $this->allowed_objects ) ) ) ) ? $site->to_array() : array_fill_keys( array_values( array_map( 'ucfirst', $this->allowed_objects ) ), array() ) ;
        
        $staging = wds_is_staging_site() ? '-staging' : '';

        $account = ( defined('PWP_NAME') ? PWP_NAME : home_url() ) . $staging ;

        if( $object ){
            $object = $data[ ucfirst( $object ) ];
        } else {
            $object = array( $account => $data );
        }
        
        delete_transient( 'WDS_Sync_Data' );
        set_transient( 'WDS_Sync_Data', $object, 20 );

        return $object;
    }


    /**
     * Create our json endpoint by adding new rewrite rules to WordPress
     */
    public function add_endpoint(){

        $object = $this->endpoint_base .'_object';

        // Add new rewrite tags to WP for our endpoint's object
        // and id arguments
        add_rewrite_tag( "%{$object}%", '([^&]+)' );

        // Add the rules that look for our rewrite tags in the route query.
        // Most specific rule first, then fallback to the general rule
        add_rewrite_rule(
            $this->endpoint_base . '/([^&]+)/?',
            'index.php?'.$object.'=$matches[1]',
            'top' );
    }

    public function endpoint_encode( $endpoint_hash, $endpoint ) {
        $seed = "";

       for ($i = 1; $i <= 10; $i++)
           $seed .= substr('0123456789abcdef', rand( 0, 15 ), 1 );

       $hash_seed = hash_hmac('md5', $seed, $endpoint_hash );

       return sha1( $hash_seed.$endpoint.$endpoint_hash ).$seed;
    }

    public function endpoint_check( $endpoint_hash, $stored_value ) {

        $endpoint_array = explode('?', ( is_ssl() ) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $endpoint = array_shift( $endpoint_array );

        if (strlen( $stored_value ) != 50 )
            return FALSE;

        $stored_seed = substr( $stored_value , 40, 10 );

        $hash_seed = hash_hmac('md5', $stored_seed, $endpoint_hash );

        if ( sha1( $hash_seed.$endpoint.$endpoint_hash ).$stored_seed == $stored_value )
            return TRUE;
        else
            return FALSE;
    }

    public function verify_nonce( $endpoint_hash, $stored_value ) {

        $endpoint_array = explode('?', ( is_ssl() ) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $endpoint = array_shift( $endpoint_array );
        
        $expected = substr( wp_hash( 'wds_site|' . $endpoint . '|webdogs', 'nonce' ), -12, 10 );

        if ( hash_equals( $expected, $endpoint_hash ) ) {
            return $this->endpoint_check( $expected, $stored_value );
        }
        return 0;
    }

    /**
     * Handle the request of an endpoint
     */
    public function handle_endpoint(){
        // Nonce generated 0-12 hours ago

        if ( ! isset( $_REQUEST['wds_nonce'] ) || ! $this->verify_nonce( $_REQUEST['wds_nonce'], $_REQUEST['wds_token'] ) ) {  // error_log( ."\n", 3, plugin_dir_path( dirname( __FILE__ ) ).'/activity.log');
            return; 
        }
        global $wp_query;

        // get the query args and sanitize them for confidence
        $object = sanitize_text_field( $wp_query->get( $this->endpoint_base.'_object' ) ); // $type = sanitize_text_field( $wp_query->get( $this->endpoint_base . '_type' ) );

        // only allowed objects
        if ( ! empty( $object ) && ! in_array( $object, $this->allowed_objects ) ) {
            return;
        }

        // output
        wp_send_json( $this->make_json_data( $object ) );
    }
}


class WEBDOGS_API {

    private $wp_http;
    
    public $request_uri = 'https://webdogsplugins.wpengine.com/wds-sync';
    public $args = array(); 
    public $resp = '';
    public $is_error;
    public $timeout = 30;

    public function __construct($args = array()) {
        $this->wp_http = new WP_Http();
        
        //set some defaults
        $defaults = array( 'wds_domain'=>'', 'wds_nonce'=>'', 'wds_token'=>'' );
        
        $this->args = $defaults;

        //merge args passed to class 
        if(!empty($args)) {
            $this->args = array_merge($this->args,$args);
        }

        add_filter('http_request_timeout',array($this,'get_timeout'));

    }
    
    public function get_timeout() {
        return $this->timeout;
    }
    
    public function setup_request($method='GET') {
        if(empty($this->args['method'])) {
            return new WP_Error('error',"Please specify a method for this request.");
        } else {
            if( 'GET' == $method ) 
            {
                if(count($this->args) > 0) {
                    foreach($this->args as $k=>$v) {
                            if(!empty($v))
                                $this->request_uri = add_query_arg(array($k=>$v),$this->request_uri);
                    }
                }
            } 
        }
        return null;
    }
    
    public function get() {
        $this->setup_request();
        $this->resp = $this->wp_http->get($this->request_uri);
        return $this;
    }
    
    public function post() {
        $this->resp = $this->wp_http->post($this->request_uri,array('body'=>$this->args));
        return $this;
    }
    
    public function set_arg($arg,$value) {
        $this->args[$arg] = $value;
        return null;
    }
    
    public function get_arg($arg) {
        if(!empty($this->args[$arg])) {
            return $this->args[$arg];
        } else {
            return false;
        }
    }
    
    public function message() {
        $array = json_decode($this->resp['body']); 
        return $array->error_msg;
    }
    
    public function set_notice($notice = null) {
        if(!empty($notice)) { $this->resp = new WP_Error('error',$notice); }
        if(is_network_admin()) {
            add_action('network_admin_notices',array($this,'render_notice'));
        } else {
            add_action('admin_notices',array($this,'render_notice'));
        }
    }
        
    public function render_notice() {
        if(!is_wp_error($this->resp)) {     
            $notice = json_decode($this->resp['body']); 
            if($this->is_error OR $this->is_error() ) {
                $notice = array('code'=> $notice->error_code,'message'=>$notice->error_msg);
            } else {
                $notice = array('code'=>'updated','message'=>$notice->message);
            }?>
            <div id="message" class="<?php echo $notice['code']; ?>"><p><?php echo $notice['message']; ?></p></div>
            <?php
        } else {
            ?><div id="message" class="error"><p><?php echo $this->resp->get_error_message(); ?></p></div>
            <?php
        }
    }
    
    public function is_error() {
        if(!is_wp_error($this->resp)) {
            $error = $this->resp['body'];
            $error = json_decode($error);
            if(@$error->error_code == 'error') {
                return $error->error_msg;
                $this->is_error = 1;
            } else {
                return false;
            }
        } else {
            return $this->resp->get_error_message();
        }
    }
}


/**
 * 
 */
abstract class WDS_Abstract
{
    protected $method;
    protected $offsets;

    public function __construct( $args, $method = '__get', $offsets = array() )
    {   
        $this->method  = $method;
        $this->offsets = $offsets;
        $this->populate( $args );

        return $this;

    }
    abstract protected function populate( array $arguments = array() );

    public function to_array() {
        $array = array();

        $list = ( $this->offsets ) ? $this->offsets : get_object_vars( $this );

        foreach ( $list as $property ) {
            $array[ $property ] = $this->{ $property };
        }
        return $array;
    } 

    protected function map( $argument ) { 
                
        if ( is_string( $argument ) ) {

            return trim( $argument );
        }
        elseif( is_object( $argument ) && $this->method && method_exists( $argument, $this->method ) ) {
            
            if ( empty( $this->offsets ) ) {

                return call_user_func_array( array( $argument, $this->method ), array() );

            } else {

                $values = array();

                foreach ( $this->offsets as $offset ) {

                    $value = call_user_func_array( array( $argument, $this->method ), array( $offset ) );

                    if ( is_null( $value ) ) { 

                        continue; 
                    }
                    $this->{ $offset } = $value;
                }
                return $values;
            } 
        } else {
           return $argument;
        }
    }
}

/**
 * 
 */
class WDS_Object extends WDS_Abstract
{
    
    protected function populate( array $arguments = array() ) {

        if ( ! empty( $arguments ) ) {
            $arguments = array_map( array( &$this, 'map' ), $arguments );

            foreach ( $arguments as $property => $argument ) {
                if(is_null($argument)){ continue; }
                
                $this->{$property} = $argument;
            }
        } elseif ( $this->method && function_exists( $this->method ) && ! empty( $this->offsets ) ) {
           
            foreach ( $this->offsets as $offset ) {

                $value = call_user_func_array( $this->method, array( $offset ) );

                if ( is_null( $value ) ) { 
                    continue; 
                }
                $this->{ $offset } = $value;
            }
        }
    }
}

/**
 * 
 */
class WDS_Site extends WDS_Object
{

    protected function populate( array $arguments = array() ) {

        if ( empty( $this->offsets ) ) return;

        foreach ( array_map( 'strtolower', $this->offsets ) as $offset ) {
            if ( empty( $this->$offset ) ) { 
                continue; 
            }
        }
    }

    public function __get( $key ) {

          $key = strtolower( $key );
        $value = array();

        if ( in_array( $key, array_map( 'strtolower', $this->offsets ) ) ) {

            switch ( $key ) {

                case 'bloginfo':
                    $value = ( false !== ( $object = new WDS_Object( array(), 'get_bloginfo', array( 'url' , 'wpurl' , 'description', 'rdf_url', 'rss_url', 'rss2_url', 'atom_url', 'comments_atom_url', 'comments_rss2_url', 'pingback_url', 'stylesheet_directory', 'template_directory', 'template_url', 'admin_email', 'charset', 'html_type', 'version', 'language', 'rtl', 'name' ) ) ) ) ? $object->to_array() : $value ;
                    break;
                
                
                case 'core':
                    $core_updates = get_core_updates( array( 'available' => true, 'dismissed' => true ) );
                    $value = ( false !== ( $object = array_shift( $core_updates ) ) && ( ( is_array( $object ) || is_object( $object ) ) && ! empty( $object ) ) ) ? $object : $value ;
                    break;
                
                
                case 'plugins':
                    $value = new WDS_Collection( array( 'installed' => get_plugins(), 'active' => array( 'is_plugin_active', true ), 'update' => get_plugin_updates() ), false, false );
                    break;
                
                
                case 'themes':
                    $value = new WDS_Collection( array( 'installed' => wp_get_themes(), 'active' => array( wp_get_theme()->get('TextDomain'), true ), 'update' => get_theme_updates() ), 'get', array( 'Name', 'ThemeURI', 'Description', 'Author', 'AuthorURI', 'Version', 'Template', 'Status', 'Tags', 'TextDomain', 'DomainPath' ) );
                    break;
                
                
                case 'updates':
                    $value = wp_get_update_data();
                    break;
                
                
                case 'options':
                    $value = get_option( 'wds_support_options' );
                    break;
            }
        }
        return $value;
    }
}

/**
 * 
 */
class WDS_Collection extends WDS_Object
{
    public $installed = array();
    public $active    = array();
    public $update    = array();

    private $defaults = array(
        'installed'  => array(), 
        'active'     => array('is_null',false),
        'update'     => array(),

    );

    protected function populate( array $arguments = array() ) {

        $arguments = wp_parse_args( $arguments, $this->defaults );

        foreach ( $arguments['installed'] as $id => $item ) {
            $item = ( is_object( $item ) && $this->method && $this->offsets && ( false !== ( $object = new WDS_Object( array( $id => $item ), $this->method, $this->offsets ) ) ) ) ? $object->to_array() : $item ;
            // $item = ( is_object( $item ) ) ? $item->to_array() : $item ;
            $item['Active'] = $item['Update'] = false;
            $this->installed[ $id ] = $item ;
        }
        $active_filter = array();
        if ( ( is_string( $arguments['active'][0] ) || is_array( $arguments['active'][0] ) ) && in_array( $arguments['active'][0], array_keys( $this->installed ) ) ) {
            $active_filter = is_string( $arguments['active'][0] ) ? array( $arguments['active'][0] ) : $arguments['active'][0];
        }
        elseif ( is_string( $arguments['active'][0] ) && function_exists( $arguments['active'][0] ) ) {
            $active_filter = array_filter( array_keys( $this->installed ), $arguments['active'][0] );
        }
        $this->active = ( $arguments['active'][1] ) ? array_values( array_intersect( $active_filter, array_keys( $this->installed ) ) ) : array_values( array_diff( $active_filter, array_keys( $this->installed ) ) );
        foreach ( $this->active as $id ) {
            $this->installed[ $id ]['Active'] = true;
        }
        foreach ( $arguments['update'] as $id => $item ) {
            $this->installed[ $id ]['Update'] = ( is_object( $item ) && property_exists( $item, 'update' ) ) ? $item->update : true ;
            array_push( $this->update, $id );
        }
    }
}

