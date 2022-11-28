<?php 
/**
 * @package   PB Digital - Gamipress Points/Levels/Coins Header Add-on
 * @author    Paul Bright <paul@pbdigital.com.au>
 * @copyright  
 * @license   
 * @link      https://pbdigital.com.au
 *
 * Plugin Name:     PB Digital - Gamipress Points/Levels/Coins Header Add-on
 * Plugin URI:      https://pbdigital.com.au
 * Description:     Generates roadmap from learndash course
 * Version:         1.2.0
 * Author:          Paul Bright
 * Author URI:      https://pbdigital.com.au
 * Text Domain:     PB Digital - Gamipress Points/Levels/Coins Header Add-on
 * License:         {
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:     /languages
 * Requires PHP:    7.0
 * WordPress-Plugin-Boilerplate-Powered: v3.2.0
 */
if (!defined('WPINC')) {die;} // if called directly

/* Activation Requirements [Start] */
if ( !function_exists( 'pbd_point_level_activation' ) ) {
    register_activation_hook( __FILE__, 'pbd_point_level_activation' );
    function pbd_point_level_activation(){
        if ( ! class_exists('GamiPress') ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die('Sorry, but this plugin requires the GamiPress to be installed and active.');
        }
    }
}

add_action( 'admin_init', 'pbd_point_level_plugin_activate' );
function pbd_point_level_plugin_activate(){
    if ( ! class_exists( 'GamiPress' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
}

if(file_exists(plugin_dir_path( __FILE__ ).'core-init.php')){
  include_once(plugin_dir_path( __FILE__ ).'core-init.php');
}
/* Activation Requirements End */

// Include our updater file
include_once( plugin_dir_path( __FILE__ ).'class/pbd-addon-updater.class.php' );
$updater = new PBD_Addon_Updater( __FILE__ ); // instantiate our class
$updater->set_username( 'pbdigital' ); // set username
$updater->set_repository( 'Gamipress-Header-Addon' ); // set repo
$updater->initialize(); // initialize the updater