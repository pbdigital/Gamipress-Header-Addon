<?php
// File: includes/class-admin-menu.php

if (!defined('WPINC')) {
    die;
}

class Admin_Menu {
    /**
     * Singleton instance
     *
     * @var Admin_Menu
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     *
     * @return Admin_Menu
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new Admin_Menu();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'register_submenu'));
    }

    /**
     * Register the submenu page
     */
    public function register_submenu() {
        add_submenu_page(
            'options-general.php',
            __('Gamipress Header Addon', 'pb-digital-gamipress-header-addon'),
            __('Gamipress Header Addon', 'pb-digital-gamipress-header-addon'),
            'manage_options',
            'pb-digital-gamipress',
            array($this, 'render_submenu_page')
        );
    }

    /**
     * Render the submenu page
     */
    public function render_submenu_page() {
        Logger::log('Submenu page accessed');

        if (!function_exists('gamipress_get_rank_type')) {
            echo '<div class="notice notice-error"><p>' . __('Gamipress plugin is not active or installed.', 'pb-digital-gamipress-header-addon') . '</p></div>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_form_submission();
        }

        $this->render_settings_form();
    }

    /**
     * Handle form submission
     */
    private function handle_form_submission() {
        check_admin_referer('save-settings', '_wpnonce_save-settings');
        Logger::log('Form submission started');

        $options = array('pbd_rank_type', 'pbd_points_type', 'pbd_coins_type', 'pbd_redeem_page', 'pbd_progress_bar');
        foreach ($options as $option) {
            $post_key = str_replace('pbd_', '', $option);
            if (isset($_POST[$post_key])) {
                $new_value = sanitize_text_field($_POST[$post_key]);
                update_option($option, $new_value);
                Logger::log("Option '$option' updated to: $new_value");
            } else {
                Logger::log("Option '$option' not found in POST data");
            }
        }

        // Automatically set the correct points type based on the selected rank type
        if (isset($_POST['rank_type']) && !empty($_POST['rank_type'])) {
            $rank_type_slug = sanitize_text_field($_POST['rank_type']);
            $points_type = $this->get_rank_type_points($rank_type_slug);
            if ($points_type) {
                update_option('pbd_points_type', $points_type);
                Logger::log("Points type automatically set to: $points_type");
            }
        }

        Logger::log('Form submission completed');
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved!', 'pb-digital-gamipress-header-addon') . '</p></div>';
    }

    /**
     * Render the settings form
     */
    private function render_settings_form() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('PB Digital Settings', 'pb-digital-gamipress-header-addon'); ?></h1>
            <form action="" method="post" name="save_settings">
                <?php wp_nonce_field('save-settings', '_wpnonce_save-settings'); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php
                            $this->render_progress_bar_option();
                        ?>
                        <tr>
                            <th colspan="2">
                                <h3><?php esc_html_e('Rank and Points Settings', 'pb-digital-gamipress-header-addon'); ?></h3>
                                <p><?php esc_html_e('These settings are used together to display user rank and points on the front end. Note: Rank type requires an image to be displayed.', 'pb-digital-gamipress-header-addon'); ?></p>
                            </th>
                        </tr>
                        <?php
                            $this->render_rank_type_option();
                            $this->render_points_type_option();
                        ?>
                        <tr>
                            <th colspan="2">
                                <h3><?php esc_html_e('Coins and Redeem Settings', 'pb-digital-gamipress-header-addon'); ?></h3>
                                <p><?php esc_html_e('These settings are used together for the coin system and redemption process. Note: Coins type requires an image to be displayed.', 'pb-digital-gamipress-header-addon'); ?></p>
                            </th>
                        </tr>
                        <?php
                            $this->render_coins_type_option();
                            $this->render_redeem_screen_option();
                        ?>
                    </tbody>
                </table>
                <p>
                    <input class="button button-primary button-large" type="submit" value="<?php esc_attr_e('Save Changes', 'pb-digital-gamipress-header-addon'); ?>" />
                </p>
            </form>
            <div id="settings-preview"></div>
        </div>
        <?php
    }

    /**
     * Render progress bar option
     */
    private function render_progress_bar_option() {
        ?>
        <tr>
            <th scope="row"><?php esc_html_e('Show Progress Bar', 'pb-digital-gamipress-header-addon'); ?></th>
            <td>
                <select name="progress_bar" class="progress_bar">
                    <option value="1" <?php selected(get_option('pbd_progress_bar'), '1'); ?>><?php esc_html_e('Enabled', 'pb-digital-gamipress-header-addon'); ?></option>
                    <option value="0" <?php selected(get_option('pbd_progress_bar'), '0'); ?>><?php esc_html_e('Disabled', 'pb-digital-gamipress-header-addon'); ?></option>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Render rank type option
     */
    private function render_rank_type_option() {
        $rank_types = gamipress_get_rank_types();
        ?>
        <tr>
            <th scope="row"><?php esc_html_e('Rank Type', 'pb-digital-gamipress-header-addon'); ?></th>
            <td>
                <select name="rank_type" class="rank">
                    <option value=""><?php esc_html_e('Select Rank Type', 'pb-digital-gamipress-header-addon'); ?></option>
                    <?php
                        if (!empty($rank_types)) {
                            foreach ($rank_types as $rank) {
                                $data = get_post($rank['ID']);
                                $selected = selected(get_option('pbd_rank_type'), $data->post_name, false);
                                $has_image = $this->check_image_exists($data->ID);
                                $disabled = $has_image ? '' : 'disabled';
                                $image_notice = $has_image ? '' : ' (' . esc_html__('A default rank image needs to be set to use this item', 'pb-digital-gamipress-header-addon') . ')';
                                echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . ' ' . $disabled . '>' . esc_html($rank['plural_name']) . $image_notice . '</option>';
                            }
                        } else {
                            echo '<option>' . esc_html__('No suitable rank types found', 'pb-digital-gamipress-header-addon') . '</option>';
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
    private function render_points_type_option() {
        $point_types = gamipress_get_points_types();
        $selected_rank_type = get_option('pbd_rank_type');
        $rank_type_points = $this->get_rank_type_points($selected_rank_type);
        
        ?>
        <tr>
            <th scope="row"><?php esc_html_e('Points Type', 'pb-digital-gamipress-header-addon'); ?></th>
            <td>
                <select name="points_type" class="points">
                    <option value=""><?php esc_html_e('Select Points Type', 'pb-digital-gamipress-header-addon'); ?></option>
                    <?php
                        if (!empty($point_types)) {
                            foreach ($point_types as $points) {
                                $data = get_post($points['ID']);
                                $selected = ($data->post_name == $rank_type_points) ? 'selected' : '';
                                $disabled = ($data->post_name !== $rank_type_points) ? 'disabled' : '';
                                echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . ' ' . $disabled . '>' . esc_html($points['plural_name']) . '</option>';
                            }
                        } else {
                            echo '<option>' . esc_html__('No suitable point types found', 'pb-digital-gamipress-header-addon') . '</option>';
                        }
                    ?>
                </select>
                <p class="description"><?php esc_html_e('This points type is automatically set based on the selected Rank Type requirements.', 'pb-digital-gamipress-header-addon'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Get the points type associated with a rank type's requirements
     *
     * @param string $rank_type_slug The slug of the rank type
     * @return string|null The slug of the associated points type, or null if not found
     */
    private function get_rank_type_points($rank_type_slug) {
        $rank_types = gamipress_get_rank_types();
        $rank_type = isset($rank_types[$rank_type_slug]) ? $rank_types[$rank_type_slug] : null;
       
        if ($rank_type) {
            $rank_id = $rank_type['ID'];
            if ($rank_id) {
                $ranks= gamipress_get_ranks(array('rank_type' => $rank_id));
                $rank_id = $ranks[0]->ID;
                $requirements = gamipress_get_rank_requirements($rank_id);
                
                if (!empty($requirements)) {
                    
                    foreach ($requirements as $requirement) {
                        $points_type = get_post_meta($requirement->ID, '_gamipress_points_type_required', true);
                        if ($points_type) {
                            return $points_type;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Render coins type option
     */
    private function render_coins_type_option() {
        $point_types = gamipress_get_points_types();
        ?>
        <tr>
            <th scope="row"><?php esc_html_e('Coins Type', 'pb-digital-gamipress-header-addon'); ?></th>
            <td>
                <select name="coins_type" class="coins">
                    <option value=""><?php esc_html_e('Select Coins Type', 'pb-digital-gamipress-header-addon'); ?></option>
                    <?php
                        if (!empty($point_types)) {
                            foreach ($point_types as $points) {
                                $data = get_post($points['ID']);
                                $selected = selected(get_option('pbd_coins_type'), $data->post_name, false);
                                $has_image = $this->check_image_exists($data->ID);
                                $disabled = $has_image ? '' : 'disabled';
                                $image_notice = $has_image ? '' : ' (' . esc_html__('A default image needs to be set to use this item', 'pb-digital-gamipress-header-addon') . ')';
                                echo '<option value="' . esc_attr($data->post_name) . '" ' . $selected . ' ' . $disabled . '>' . esc_html($points['plural_name']) . $image_notice . '</option>';
                            }
                        } else {
                            echo '<option>' . esc_html__('No suitable point types found', 'pb-digital-gamipress-header-addon') . '</option>';
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
    private function render_redeem_screen_option() {
        $pages = get_posts(array(
            'post_type'      => 'page',
            'numberposts'    => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        ?>
        <tr>
            <th scope="row"><?php esc_html_e('Redeem Screen', 'pb-digital-gamipress-header-addon'); ?></th>
            <td>
                <select name="redeem_page" class="redeem_page">
                    <option value=""><?php esc_html_e('Select Page', 'pb-digital-gamipress-header-addon'); ?></option>
                    <?php
                        if (!empty($pages)) {
                            foreach ($pages as $page) {
                                $selected = selected(get_option('pbd_redeem_page'), $page->ID, false);
                                echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
                            }
                        } else {
                            echo '<option>' . esc_html__('No pages found', 'pb-digital-gamipress-header-addon') . '</option>';
                        }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Check if an image exists for a given post ID
     */
    private function check_image_exists($post_id) {
        $image_id = get_post_thumbnail_id($post_id);
        return !empty($image_id);
    }
}
