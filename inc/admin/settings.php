<?php

namespace WP_AMADEUS\admin;

use WP_AMADEUS\core\SettingAPI;
use WP_AMADEUS\WP_AMADEUS_REST;

/**
 * Class Settings
 *
 * @see https://github.com/tareq1988/wordpress-settings-api-class
 *
 * SELECT * FROM `wp_options` WHERE `option_name` LIKE '%wp_amadeus%' ORDER BY `option_id` DESC
 */
class Settings
{
    /**
     * Plugin Option name
     */
    public $setting;

    /**
     * The single instance of the class.
     */
    protected static $_instance = null;

    /**
     * Main Instance.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Admin_Setting_Api constructor.
     */
    public function __construct()
    {
        add_action('admin_init', array($this, 'init_option'));
        add_action('admin_head', array($this, 'admin_head'));
    }

    /**
     * Admin Head
     */
    public function admin_head()
    {
        global $pagenow;
        if ($pagenow == "admin.php" and isset($_GET['page']) and $_GET['page'] == "wp-amadeus") {
            ?>
            <style>
                .form-table th {
                    width: 310px !important;
                }

                tr.level select {
                    min-width: 30rem !important;
                }

                .nav-tab, h2, h3 {
                    font-family: tahoma !important;
                    font-weight: normal !important;
                }

                input#submit {
                    width: 200px;
                    height: 50px;
                    margin-top: 25px;
                }

                tr.dasht-input-ltr input {
                    text-align: left;
                    direction: ltr;
                }
            </style>
            <?php
        }
    }

    /**
     * Display the plugin settings options page
     */
    public function setting_page()
    {

        echo '<div class="wrap">';
        settings_errors();

        $this->setting->show_navigation();
        $this->setting->show_forms();

        echo '</div>';
    }

    /**
     * Registers settings section and fields
     */
    public function init_option()
    {
        global $pagenow;

        $sections = array(
            [
                'id' => 'wp_amadeus',
                'title' => __('Settings', 'wp-amadeus'),
                'desc' => ''
            ]
        );

        // Default Variable
        $status_server = 'Fail';

        // Check in This Page
        if (is_admin() and $pagenow == "admin.php" and isset($_GET['page']) and $_GET['page'] == Admin::$admin_page_slug) {

            // Check Status Server
            $token = WP_AMADEUS_REST::token();
            if ($token['status'] === false) {
                $status_server = '<b style="color: red;">Fail</b>';
            } else {
                $status_server = '<b style="color: green;">success</b>';
            }

        }

        // List Of Settings
        $settings = array(
            array(
                'name' => 'api_key',
                'label' => __('API Key', 'wp-amadeus'),
                'type' => 'text'
            ),
            array(
                'name' => 'api_secret',
                'label' => __('API Secret', 'wp-amadeus'),
                'type' => 'text'
            )
        );

        // Refresh Token
        $connection_log = '';
        if (isset($token['status']) and $token['status'] === false) {
            $connection_log .= '<div style="height:10px;"></div><span style="color: #8c0088;">';
            $connection_log .= 'Error Message: ';
            $connection_log .= $token['message'];
            $connection_log .= '</span>';
        }

        $settings[] = array(
            'name' => 'status_connect_to_db',
            'label' => '',
            'desc' => '<span class="dashicons dashicons-database" style="vertical-align: -5px;"></span>' . ' API Status : ' . '<b>' . $status_server . '</b>&nbsp;&nbsp;<a href="' . add_query_arg(array('page' => Admin::$admin_page_slug), admin_url('admin.php')) . '" class="button button-secondry">Check again</a>' . $connection_log,
            'type' => 'html'
        );

        // Set All Settings Field
        $fields = [
            'wp_amadeus' => apply_filters('wp_amadeus_prepare_settings', $settings)
        ];

        $this->setting = new SettingAPI();

        //set sections and fields
        $this->setting->set_sections($sections);
        $this->setting->set_fields($fields);

        //initialize them
        $this->setting->admin_init();
    }
}