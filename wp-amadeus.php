<?php
/**
 * Plugin Name: Amadeus API Services
 * Description: A WordPress Plugin For Test Amadeus API
 * Plugin URI:  https://realwp.net
 * Version:     1.0.0
 * Author:      Mehrshad Darzi
 * Author URI:  https://realwp.net
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * License:     MIT
 * Text Domain: wp-amadeus
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WP_Amadeus
{
    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '7.2.0';

    /**
     * Use plugin's translated strings
     *
     * @var string
     * @default true
     */
    public static $use_i18n = true;

    /**
     * List Of Class
     * @var array
     */
    public static $providers = array(
        'admin\Admin',
        'core\\Utility'
    );

    /**
     * URL to this plugin's directory.
     *
     * @type string
     * @status Core
     */
    public static $plugin_url;

    /**
     * Path to this plugin's directory.
     *
     * @type string
     * @status Core
     */
    public static $plugin_path;

    /**
     * Path to this plugin's directory.
     *
     * @type string
     * @status Core
     */
    public static $plugin_version;

    /**
     * Plugin instance.
     *
     * @see get_instance()
     * @status Core
     */
    protected static $_instance = null;

    /**
     * Plugin Slug
     *
     * @var string
     */
    public static $plugin_slug = 'wp-amadeus';

    /**
     * Plugin Main File
     *
     * @var string
     */
    public static $plugin_main_file = '';

    /**
     * Plugin Data
     *
     * @var array
     */
    public static $plugin_data = array();

    /**
     * Access this plugin’s working instance
     *
     * @wp-hook plugins_loaded
     * @return  object of this class
     * @since   2012.09.13
     */
    public static function instance()
    {
        null === self::$_instance and self::$_instance = new self;
        return self::$_instance;
    }

    /**
     * WP_AMADEUS constructor.
     */
    public function __construct()
    {

        /*
         * Check Require Php Version
         */
        if (version_compare(PHP_VERSION, $this->min_php, '<=')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }

        /*
         * Define Variable
         */
        $this->define_constants();

        /*
         * include files
         */
        $this->includes();
    }

    /**
     * Define Constant
     */
    public function define_constants()
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        self::$plugin_url = plugins_url('', __FILE__);
        self::$plugin_path = plugin_dir_path(__FILE__);
        self::$plugin_main_file = self::$plugin_path . 'wp-amadeus.php';
        self::$plugin_data = get_plugin_data(self::$plugin_main_file);
        self::$plugin_version = self::$plugin_data['Version'];
    }

    /**
     * include Plugin Require File
     */
    public function includes()
    {
        
        /*
         * autoload plugin files
         */
        include_once dirname(__FILE__) . '/inc/config/i18n.php';
        include_once dirname(__FILE__) . '/inc/config/install.php';
        include_once dirname(__FILE__) . '/inc/config/uninstall.php';
        include_once dirname(__FILE__) . '/inc/frontend.php';
        include_once dirname(__FILE__) . '/inc/api.php';
        include_once dirname(__FILE__) . '/inc/admin/admin.php';
        include_once dirname(__FILE__) . '/inc/admin/settings.php';
        include_once dirname(__FILE__) . '/inc/core/settings.php';
        include_once dirname(__FILE__) . '/inc/core/utility.php';
        
        /*
         * Load List Of classes
         */
        foreach (self::$providers as $class) {
            $class_object = '\WP_AMADEUS\\' . $class;
            new $class_object;
        }

        /*
        * init WordPress hook
        */
        $this->init_hooks();

        /*
         * Plugin Loaded Action
         */
        do_action('wp_amadeus_loaded');
    }
    
    /**
     * The main logging function
     *
     * @param $message
     * @uses error_log
     */
    public static function log($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $file = fopen(ABSPATH . "/wp-amadeus.log", "a");
        fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message);
        fclose($file);
    }

    /**
     * Used for regular plugin work.
     *
     * @wp-hook init Hook
     * @return  void
     */
    public function init_hooks()
    {

        /*
         * Activation Plugin Hook
         */
        register_activation_hook(__FILE__, array('\WP_AMADEUS\config\install', 'run_install'));

        /*
         * Uninstall Plugin Hook
         */
        register_deactivation_hook(__FILE__, array('\WP_AMADEUS\config\uninstall', 'run_uninstall'));

        /*
         * Load i18n
         */
        if (self::$use_i18n === true) {
            new \WP_AMADEUS\config\i18n('wp-amadeus');
        }
    }

    /**
     * Show notice about PHP version
     *
     * @return void
     */
    function php_version_notice()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $error = __('Your installed PHP Version is: ', 'wp-amadeus') . PHP_VERSION . '. ';
        $error .= __('The <strong>WP Plugin</strong> plugin requires PHP version <strong>', 'wp-amadeus') . $this->min_php . __('</strong> or greater.', 'wp-amadeus');
        ?>
        <div class="error">
            <p><?php printf($error); ?></p>
        </div>
        <?php
    }

}

/**
 * Main instance of WP_Plugin.
 *
 * @since  1.1.0
 */
function wp_amadeus_api()
{
    return WP_Amadeus::instance();
}

// Global for backwards compatibility.
$GLOBALS['wp-amadeus'] = wp_amadeus_api();
