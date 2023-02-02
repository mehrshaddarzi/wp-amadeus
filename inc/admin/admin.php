<?php

namespace WP_AMADEUS\admin;

use WP_Amadeus;
use WP_AMADEUS\WP_AMADEUS_REST;

class Admin
{

    public static $admin_page_slug = 'wp-amadeus';

    public static $defaultPermission = 'manage_options';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
        //add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }

    public function admin_assets()
    {
        wp_enqueue_style('wp-amadeus', WP_Amadeus::$plugin_url . '/asset/admin/css/style.css', array(), WP_Amadeus::$plugin_version, 'all');
        wp_enqueue_script('wp-amadeus', WP_Amadeus::$plugin_url . '/asset/admin/js/script.js', array('jquery'), WP_Amadeus::$plugin_version, false);
    }

    public function admin_menu()
    {
        add_menu_page(__('Amadeus', 'wp-amadeus'), __('Amadeus', 'wp-amadeus'), self::$defaultPermission, self::$admin_page_slug, array(Settings::instance(), 'setting_page'), 'dashicons-schedule', 88);
    }
}