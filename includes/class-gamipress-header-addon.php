<?php
// File: includes/class-gamipress-header-addon.php

if (!defined('WPINC')) {
    die;
}

class Gamipress_Header_Addon {
    /**
     * Singleton instance
     *
     * @var Gamipress_Header_Addon
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return Gamipress_Header_Addon
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new Gamipress_Header_Addon();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('PB_DIGITAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('PB_DIGITAL_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('PB_DIGITAL_LOG_FILE', PB_DIGITAL_PLUGIN_DIR . '../pb_digital.log');
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once PB_DIGITAL_PLUGIN_DIR . 'class-admin-menu.php';
        require_once PB_DIGITAL_PLUGIN_DIR . 'class-admin-scripts.php';
        require_once PB_DIGITAL_PLUGIN_DIR . 'class-frontend-scripts.php';
        require_once PB_DIGITAL_PLUGIN_DIR . 'class-logger.php';
    }

    /**
     * Run the plugin
     */
    public function run() {
        // Initialize Admin
        if (is_admin()) {
            Admin_Menu::get_instance();
            Admin_Scripts::get_instance();
        }

        // Initialize Frontend
        Frontend_Scripts::get_instance();
    }
}
