<?php
// File: includes/class-admin-scripts.php

if (!defined('WPINC')) {
    die;
}

class Admin_Scripts {
    /**
     * Singleton instance
     *
     * @var Admin_Scripts
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return Admin_Scripts
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new Admin_Scripts();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Enqueue admin JavaScript and localize script
     */
    public function enqueue_admin_scripts($hook) {
        // Load scripts only on the plugin's settings page
        if ($hook !== 'settings_page_pb-digital-gamipress') {
            return;
        }

        wp_enqueue_script(
            'admin-point-level-script',
            plugin_dir_url(__FILE__) . '../assets/js/admin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script(
            'admin-point-level-script',
            'admin_point_level_vars',
            array(
                'pbd_progress_bar'  => get_option('pbd_progress_bar', 0),
                'pbd_rank_type'     => get_option('pbd_rank_type'),
                'pbd_points_type'   => get_option('pbd_points_type'),
                'pbd_coins_type'    => get_option('pbd_coins_type'),
                'pbd_redeem_page'   => get_option('pbd_redeem_page'),
            )
        );
    }
}
