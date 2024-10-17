<?php 
/**
 * @package   PB Digital - Gamipress Points/Levels/Coins Header Add-on
 * @author    Paul Bright
 * @license   GPL-2.0+
 * @link      https://pbdigital.com.au
 *
 * Plugin Name:     PB Digital - Gamipress Points/Levels/Coins Header Add-on
 * Plugin URI:      https://pbdigital.com.au
 * Description:     Adds points, levels, and coins display to the header using GamiPress.
 * Version:         1.3.0
 * Author:          Paul Bright
 * Author URI:      https://pbdigital.com.au
 * Text Domain:     pb-digital-gamipress-header-addon
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.0
 */

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-gamipress-header-addon.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Initialize the plugin
function pb_digital_initialize_plugin() {
    $plugin = Gamipress_Header_Addon::get_instance();
    $plugin->run();
}
add_action('plugins_loaded', 'pb_digital_initialize_plugin');

/* Activation Requirements */
register_activation_hook(__FILE__, 'pb_digital_activation_check');
function pb_digital_activation_check(){
    if (!class_exists('GamiPress')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Sorry, but this plugin requires the GamiPress plugin to be installed and active.', 'pb-digital-gamipress-header-addon'));
    }
}

add_action('admin_init', 'pb_digital_plugin_activate');
function pb_digital_plugin_activate(){
    if (!class_exists('GamiPress')) {
        deactivate_plugins(plugin_basename(__FILE__));
    }
}

/* Include the Update Checker */
require plugin_dir_path(__FILE__) . 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/pbdigital/Gamipress-Header-Addon',
    __FILE__,
    'gamipress-header-addon'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch('release');
