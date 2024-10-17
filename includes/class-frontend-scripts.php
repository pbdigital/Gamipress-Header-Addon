<?php
// File: includes/class-frontend-scripts.php

if (!defined('WPINC')) {
    die;
}

class Frontend_Scripts {
    /**
     * Singleton instance
     *
     * @var Frontend_Scripts
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return Frontend_Scripts
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new Frontend_Scripts();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }

    /**
     * Enqueue frontend JavaScript and localize script
     */
    public function enqueue_frontend_scripts() {
        $show_bar = get_option('pbd_progress_bar', 0);

        if (!$show_bar) {
            return;
        }

        $user_id = get_current_user_id();
        $data = $this->get_point_level_data($user_id);

        wp_enqueue_script(
            'point-level-script',
            plugin_dir_url(__FILE__) . '../assets/js/point-level-coin.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('point-level-script', 'point_level_vars', $data);
    }

    /**
     * Get point level data for a user
     *
     * @param int $user_id The user ID
     * @return array The point level data
     */
    private function get_point_level_data($user_id) {
        $pbd_rank_type     = get_option('pbd_rank_type', 0);
        $pbd_points_type   = get_option('pbd_points_type');
        $pbd_coins_type    = get_option('pbd_coins_type');
        $pbd_redeem_page   = get_option('pbd_redeem_page');

        $next_level_id     = gamipress_get_next_user_rank_id($user_id, $pbd_rank_type);
        $current_rank_id   = gamipress_get_user_rank_id($user_id, $pbd_rank_type);
        $current_rank      = get_the_title($current_rank_id);
        $current_points    = gamipress_get_user_points($user_id, $pbd_points_type);
        $current_coins     = gamipress_get_user_points($user_id, $pbd_coins_type);

        $points_needed = 0;
        if ($pbd_rank_type) {
            $requirements = gamipress_get_rank_requirements($next_level_id);
            if (!empty($requirements)) {
                $points_needed = get_post_meta($requirements[0]->ID, '_gamipress_points_required', true);
            }
        }

        $completion = ($points_needed != 0) ? round($current_points / $points_needed * 100, 0) : 0;

        $redeem_screen = $pbd_redeem_page ? get_permalink($pbd_redeem_page) : '';
        $coins_img = $this->get_coins_image($pbd_coins_type);

        // Logging
        $logger = Logger::get_instance();
        if (empty($redeem_screen)) {
            $logger->log('Redeem screen URL is empty');
        }
        if (empty($coins_img)) {
            $logger->log('Coins image URL is empty');
        }
        if (empty($current_coins)) {
            $logger->log('Current coins value is empty');
        }

        return array(
            'rank_img'               => $this->get_rank_image($current_rank_id, $pbd_rank_type),
            'current_rank'           => $current_rank,
            'current_points'         => $current_points,
            'buddy_theme_accent_color'=> buddyboss_theme_get_option('accent_color'),
            'points_needed'          => $points_needed,
            'completion'             => $completion,
            'redeem_screen'          => $redeem_screen,
            'coins_img'              => $coins_img,
            'current_coins'          => $current_coins
        );
    }

    /**
     * Get rank image URL
     *
     * @param int $current_rank_id The current rank ID
     * @param string $pbd_rank_type The rank type
     * @return string The rank image URL
     */
    private function get_rank_image($current_rank_id, $pbd_rank_type) {
        $rank_img = get_the_post_thumbnail_url($current_rank_id);
        if (!$rank_img) {
            $rank = gamipress_get_rank_type($pbd_rank_type);
            if ($rank && isset($rank['ID'])) {
                $rank_img = get_the_post_thumbnail_url($rank['ID']);
            }
        }
        return $rank_img;
    }

    /**
     * Get coins image URL
     *
     * @param string $pbd_coins_type The coins type
     * @return string The coins image URL
     */
    private function get_coins_image($pbd_coins_type) {
        $coins = gamipress_get_points_type($pbd_coins_type);
        $coins_img = !empty($coins) ? get_the_post_thumbnail_url($coins['ID']) : '';

        // Provide a default image URL if no image is found
        if (empty($coins_img)) {
            $coins_img = plugin_dir_url(__FILE__) . '../assets/images/default-coins.png';
        }

        return $coins_img;
    }
}
