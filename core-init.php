<?php
//Plugin Update 
if (!defined('WPINC')) {
   die;
} // if called directly

add_action('admin_menu', 'pb_digital_register_submenu');
function pb_digital_register_submenu()
{
   add_submenu_page('options-general.php', 'Gamipress Header Addon', 'Gamipress Header Addon', 'manage_options', 'pb-digital-gamipress', 'pb_digital_gamipress_submenu_page');

   add_action('load-admin-point-level-js', 'load_admin_point_level_js');
}

function load_admin_point_level_js()
{
   add_action('admin_enqueue_scripts', 'enqueue_point_level_admin_js');
}

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


function point_level_load_scripts()
{
   $user_id = get_current_user_id();
   $show_bar = get_option('pbd_progress_bar', 0);

   $rank_img = '';
   $current_rank = '';
   $current_points = '';
   $points_needed = '';
   $completion = '';
   $redeem_screen = '';
   $coins_img = '';
   $current_coins = '';

   if ($show_bar) {
      $pbd_rank_type = get_option('pbd_rank_type',0);
      $pbd_points_type = get_option('pbd_points_type');
      $pbd_coins_type = get_option('pbd_coins_type');
      $pbd_redeem_page = get_option('pbd_redeem_page');

      $next_level_id = gamipress_get_next_user_rank_id($user_id, $pbd_rank_type);
  
      if ($pbd_rank_type) {
         $requirements = gamipress_get_rank_requirements($next_level_id);
         $points_needed = get_post_meta($requirements[0]->ID, '_gamipress_points_required', true);
         $current_rank_id = gamipress_get_user_rank_id($user_id, $pbd_rank_type);
         $current_rank = get_the_title($current_rank_id);
         $current_points = gamipress_get_user_points($user_id, $pbd_points_type);
      }

      // Check for division by zero error
      if ($points_needed == 0) {
         $completion = 0;
      } else {
         $completion = round($current_points / $points_needed * 100, 0);
      }

      $current_coins = gamipress_get_user_points($user_id, $pbd_coins_type);
      $rank = gamipress_get_rank_type($pbd_rank_type);
      $points = gamipress_get_points_type($pbd_points_type);
      $all_ranks = gamipress_get_ranks();

      if (!$pbd_redeem_page) {
         $redeem_screen = 'javascript:void(0)';
      } else {
         $redeem_screen = get_permalink($pbd_redeem_page);
      }
      if (!empty($rank)) {
         $rank_img = get_the_post_thumbnail_url($current_rank_id);
         if (!$rank_img) {
            $rank_img = get_the_post_thumbnail_url($points['ID']); //fallback for rank image
         }
      }
      $coins = gamipress_get_points_type($pbd_coins_type);
      if (!empty($coins)) {
         $coins_img = get_the_post_thumbnail_url($coins['ID']);
      }
   }

   wp_enqueue_script('point-level-script', plugin_dir_url(__FILE__) . 'assets/js/point-level-coin.js', array('jquery'));

   if ($show_bar) {
      wp_localize_script('point-level-script', 'point_level_vars', array(
         'rank_img'               => $rank_img,
         'current_rank'           => $current_rank,
         'current_points'         => $current_points,
         'buddy_theme_accent_color' => buddyboss_theme_get_option('accent_color'),
         'points_needed'          => $points_needed,
         'completion'             => $completion,
         'redeem_screen'          => $redeem_screen,
         'coins_img'              => $coins_img,
         'current_coins'          => $current_coins
      ));
   }
}



add_action('wp_enqueue_scripts', 'point_level_load_scripts');

function pb_digital_gamipress_submenu_page()
{

   if (function_exists("gamipress_get_rank_type")) {
      #gamipress function is available
?>
      <div class="wrap">
         <?php
         if ($_POST) {
            check_admin_referer('save-settings', '_wpnonce_save-settings');
            update_option('pbd_rank_type', $_POST['rank']);
            update_option('pbd_points_type', $_POST['points']);
            update_option('pbd_coins_type', $_POST['coins']);
            update_option('pbd_redeem_page', $_POST['redeem_page']);
            update_option('pbd_progress_bar', $_POST['progress_bar']);
            echo '<div class="notice notice-success is-dismissible">
             <p>Settings saved!</p>
            </div>';
         }

         ?>
         <h1>PB Digital</h1>
         <form action="" method="post" name="save_settings">
            <?php wp_nonce_field('save-settings', '_wpnonce_save-settings') ?>
            <table class="form-table" role="presentation">
               <tbody>
                  <tr>
                     <th>Show Progress Bar</th>
                     <td>

                        <select name="progress_bar" class="progress_bar">
                           <option value="1" <?php selected(get_option('pbd_progress_bar'), '1'); ?>>Enabled</option>
                           <option value="0" <?php selected(get_option('pbd_progress_bar'), '0'); ?>>Disabled</option>
                        </select>

                     </td>
                  </tr>
                  <tr>
                     <th>Rank Type</th>
                     <td>
                        <?php
                        #Get rank types
                        $rank_types = gamipress_get_rank_types();
                        echo '<select name="rank" class="rank">';
                        echo '<option value=""></option>';

                        if (!empty($rank_types)) {
                           foreach ($rank_types as $rank) {
                              $data = get_post($rank['ID']);
                              $selected = selected(get_option('pbd_rank_type'), $data->post_name, false);
                              echo '<option value="' . $data->post_name . '" ' . $selected . '>' . $rank['plural_name'] . '</option>';
                           }
                        } else {
                           echo '<option>No suitable rank types found</option>';
                        }
                        echo '</select>';
                        ?>
                     </td>
                  </tr>

                  <tr>
                     <th>Points Type</th>
                     <td>
                        <?php

                        #Get point types
                        $point_types = gamipress_get_points_types();

                        echo '<select name="points" class="points">';
                        echo '<option value=""></option>';

                        if (!empty($point_types)) {
                           foreach ($point_types as $points) {
                              $data = get_post($points['ID']);
                              $selected = selected(get_option('pbd_points_type'), $data->post_name, false);
                              echo '<option value="' . $data->post_name . '" ' . $selected . '>' . $points['plural_name'] . '</option>';
                           }
                        } else {
                           echo '<option>No suitable point types found</option>';
                        }
                        echo '</select>';

                        ?>
                     </td>
                  </tr>
                  <tr>
                     <th>Coins Type</th>
                     <td>
                        <?php

                        #Get point types
                        $point_types = gamipress_get_points_types();

                        echo '<select name="coins" class="coins">';
                        echo '<option value=""></option>';

                        if (!empty($point_types)) {
                           foreach ($point_types as $points) {
                              $data = get_post($points['ID']);
                              $selected = selected(get_option('pbd_coins_type'), $data->post_name, false);
                              echo '<option value="' . $data->post_name . '" ' . $selected . '>' . $points['plural_name'] . '</option>';
                           }
                        } else {
                           echo '<option>No suitable point types found</option>';
                        }
                        echo '</select>';

                        ?>
                     </td>
                  </tr>
                  <tr>
                     <th>Redeem Screen</th>
                     <td>
                        <?php

                        #Get point types
                        $pages = get_posts(['post_type' => 'page', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);

                        echo '<select name="redeem_page" class="redeem_page">';
                        echo '<option value=""></option>';

                        if (!empty($pages)) {
                           foreach ($pages as $page) {
                              $selected = selected(get_option('pbd_redeem_page'), $page->ID, false);
                              echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
                           }
                        } else {
                           echo '<option>No pages found</option>';
                        }
                        echo '</select>';

                        ?>
                     </td>
                  </tr>
               </tbody>
            </table>
            <p>
               <input class="button button-primary button-large" type="submit" value="Save Changes" />
            </p>
         </form>
      </div>

<?php
   }
}
