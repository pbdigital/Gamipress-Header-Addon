<?php
/**
 * Gamipress Header Addon
 *
 * This file contains the core functionality for the Gamipress Header Addon plugin.
 */

// Exit if accessed directly
if (!defined('WPINC')) {
    die;
}

// Register submenu
add_action('admin_menu', 'pb_digital_register_submenu');

/**
 * Register the submenu page
 */
function pb_digital_register_submenu()
{
    add_submenu_page(
        'options-general.php',
        'Gamipress Header Addon',
        'Gamipress Header Addon',
        'manage_options',
        'pb-digital-gamipress',
        'pb_digital_gamipress_submenu_page'
    );

    add_action('load-admin-point-level-js', 'load_admin_point_level_js');
}

/**
 * Load admin JavaScript
 */
function load_admin_point_level_js()
{
    add_action('admin_enqueue_scripts', 'enqueue_point_level_admin_js');
}

/**
 * Enqueue admin JavaScript and localize script
 */
function enqueue_point_level_admin_js()
{
    wp_enqueue_script('admin-point-level-script', plugin_dir_url(__FILE__) . 'assets/js/_admin.js');
    wp_localize_script(
        'admin-point-level-script',
        'admin_point_level_vars',
        array(
            'pbd_progress_bar' => get_option('pbd_progress_bar', 0),
            'pbd_rank_type' => get_option('pbd_rank_type'),
            'pbd_points_type' => get_option('pbd_points_type'),
            'pbd_coins_type' => get_option('pbd_coins_type'),
            'pbd_redeem_page' => get_option('pbd_redeem_page'),
        )
    );
}

/**
 * Load front-end scripts and styles
 */
function point_level_load_scripts()
{
    $user_id = get_current_user_id();
    $show_bar = get_option('pbd_progress_bar', 0);

    if (!$show_bar) {
        return;
    }

    $data = get_point_level_data($user_id);

    wp_enqueue_script('point-level-script', plugin_dir_url(__FILE__) . 'assets/js/point-level-coin.js', array('jquery'));
    wp_localize_script('point-level-script', 'point_level_vars', $data);
}
add_action('wp_enqueue_scripts', 'point_level_load_scripts');

/**
 * Get point level data for a user
 *
 * @param int $user_id The user ID
 * @return array The point level data
 */
function get_point_level_data($user_id)
{
    $pbd_rank_type = get_option('pbd_rank_type', 0);
    $pbd_points_type = get_option('pbd_points_type');
    $pbd_coins_type = get_option('pbd_coins_type');
    $pbd_redeem_page = get_option('pbd_redeem_page');

    $next_level_id = gamipress_get_next_user_rank_id($user_id, $pbd_rank_type);
    $current_rank_id = gamipress_get_user_rank_id($user_id, $pbd_rank_type);
    $current_rank = get_the_title($current_rank_id);
    $current_points = gamipress_get_user_points($user_id, $pbd_points_type);
    $current_coins = gamipress_get_user_points($user_id, $pbd_coins_type);

    $points_needed = 0;
    if ($pbd_rank_type) {
        $requirements = gamipress_get_rank_requirements($next_level_id);
        $points_needed = get_post_meta($requirements[0]->ID, '_gamipress_points_required', true);
    }

    $completion = ($points_needed != 0) ? round($current_points / $points_needed * 100, 0) : 0;

    $redeem_screen = $pbd_redeem_page ? get_permalink($pbd_redeem_page) : '';
    $coins_img = get_coins_image($pbd_coins_type);

    // Add error logging
    if (empty($redeem_screen)) {
        pb_digital_log('Redeem screen URL is empty');
    }
    if (empty($coins_img)) {
        pb_digital_log('Coins image URL is empty');
    }
    if (empty($current_coins)) {
        pb_digital_log('Current coins value is empty');
    }

    return array(
        'rank_img' => get_rank_image($current_rank_id, $pbd_rank_type),
        'current_rank' => $current_rank,
        'current_points' => $current_points,
        'buddy_theme_accent_color' => buddyboss_theme_get_option('accent_color'),
        'points_needed' => $points_needed,
        'completion' => $completion,
        'redeem_screen' => $redeem_screen,
        'coins_img' => $coins_img,
        'current_coins' => $current_coins
    );
}

/**
 * Get rank image URL
 *
 * @param int $current_rank_id The current rank ID
 * @param string $pbd_rank_type The rank type
 * @return string The rank image URL
 */
function get_rank_image($current_rank_id, $pbd_rank_type)
{
    $rank_img = get_the_post_thumbnail_url($current_rank_id);
    if (!$rank_img) {
        $rank = gamipress_get_rank_type($pbd_rank_type);
        $rank_img = get_the_post_thumbnail_url($rank['ID']);
    }
    return $rank_img;
}

/**
 * Get coins image URL
 *
 * @param string $pbd_coins_type The coins type
 * @return string The coins image URL
 */
function get_coins_image($pbd_coins_type)
{
    $coins = gamipress_get_points_type($pbd_coins_type);
    $coins_img = !empty($coins) ? get_the_post_thumbnail_url($coins['ID']) : '';
    
    // Provide a default image URL if no image is found
    if (empty($coins_img)) {
        $coins_img = plugin_dir_url(__FILE__) . 'assets/images/default-coins.png';
    }
    
    return $coins_img;
}

/**
 * Render the submenu page
 */
function pb_digital_gamipress_submenu_page()
{
    pb_digital_log('Submenu page accessed');
    if (!function_exists("gamipress_get_rank_type")) {
        echo '<div class="notice notice-error"><p>Gamipress plugin is not active or installed.</p></div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handle_form_submission();
    }

    render_settings_form();
}

/**
 * Handle form submission
 */
function handle_form_submission()
{
    check_admin_referer('save-settings', '_wpnonce_save-settings');
    pb_digital_log('Form submission started');

    $options = array('pbd_rank_type', 'pbd_points_type', 'pbd_coins_type', 'pbd_redeem_page', 'pbd_progress_bar');
    foreach ($options as $option) {
        $post_key = str_replace('pbd_', '', $option);
        if (isset($_POST[$post_key])) {
            $new_value = sanitize_text_field($_POST[$post_key]);
            update_option($option, $new_value);
            pb_digital_log("Option '$option' updated to: $new_value");
        } else {
            pb_digital_log("Option '$option' not found in POST data");
        }
    }

    pb_digital_log('Form submission completed');
    echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
}

/**
 * Render the settings form
 */
function render_settings_form()
{
    ?>
    <div class="wrap">
        <h1>PB Digital</h1>
        <form action="" method="post" name="save_settings">
            <?php wp_nonce_field('save-settings', '_wpnonce_save-settings') ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <?php render_progress_bar_option(); ?>
                    <?php render_rank_type_option(); ?>
                    <?php render_points_type_option(); ?>
                    <?php render_coins_type_option(); ?>
                    <?php render_redeem_screen_option(); ?>
                </tbody>
            </table>
            <p>
                <input class="button button-primary button-large" type="submit" value="Save Changes" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Render progress bar option
 */
function render_progress_bar_option()
{
    ?>
    <tr>
        <th>Show Progress Bar</th>
        <td>
            <select name="progress_bar" class="progress_bar">
                <option value="1" <?php selected(get_option('pbd_progress_bar'), '1'); ?>>Enabled</option>
                <option value="0" <?php selected(get_option('pbd_progress_bar'), '0'); ?>>Disabled</option>
            </select>
        </td>
    </tr>
    <?php
}

/**
 * Render rank type option
 */
function render_rank_type_option()
{
    $rank_types = gamipress_get_rank_types();
    ?>
    <tr>
        <th>Rank Type</th>
        <td>
            <select name="rank_type" class="rank">
                <option value=""></option>
                <?php
                if (!empty($rank_types)) {
                    foreach ($rank_types as $rank) {
                        $data = get_post($rank['ID']);
                        $selected = selected(get_option('pbd_rank_type'), $data->post_name, false);
                        echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . '>' . esc_html($rank['plural_name']) . '</option>';
                    }
                } else {
                    echo '<option>No suitable rank types found</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

/**
 * Render points type option
 */
function render_points_type_option()
{
    $point_types = gamipress_get_points_types();
    ?>
    <tr>
        <th>Points Type</th>
        <td>
            <select name="points_type" class="points">
                <option value=""></option>
                <?php
                if (!empty($point_types)) {
                    foreach ($point_types as $points) {
                        $data = get_post($points['ID']);
                        $selected = selected(get_option('pbd_points_type'), $data->post_name, false);
                        echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . '>' . esc_html($points['plural_name']) . '</option>';
                    }
                } else {
                    echo '<option>No suitable point types found</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

/**
 * Render coins type option
 */
function render_coins_type_option()
{
    $point_types = gamipress_get_points_types();
    ?>
    <tr>
        <th>Coins Type</th>
        <td>
            <select name="coins_type" class="coins">
                <option value=""></option>
                <?php
                if (!empty($point_types)) {
                    foreach ($point_types as $points) {
                        $data = get_post($points['ID']);
                        $selected = selected(get_option('pbd_coins_type'), $data->post_name, false);
                        echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . '>' . esc_html($points['plural_name']) . '</option>';
                    }
                } else {
                    echo '<option>No suitable point types found</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

/**
 * Render redeem screen option
 */
function render_redeem_screen_option()
{
    $pages = get_posts(['post_type' => 'page', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    ?>
    <tr>
        <th>Redeem Screen</th>
        <td>
            <select name="redeem_page" class="redeem_page">
                <option value=""></option>
                <?php
                if (!empty($pages)) {
                    foreach ($pages as $page) {
                        $selected = selected(get_option('pbd_redeem_page'), $page->ID, false);
                        echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
                    }
                } else {
                    echo '<option>No pages found</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

/**
 * Custom logging function
 *
 * @param string $message The message to log
 */
function pb_digital_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'pb_digital.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($log_file, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
}
