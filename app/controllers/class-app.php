<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as models;

class App extends lib\Base_Ctrl {
  public function load_hooks() {
    add_action('admin_init', array($this,'install'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
  }

  public function install() {
    $db = lib\Db::fetch();
    $db->upgrade();
    flush_rewrite_rules();
  }

  public static function setup_menus() {
    $app = new App();
    add_action('admin_menu', [$app,'menu']);
  }

  public function toplevel_menu_route() {
    ?>
      <script>
        window.location.href="<?php echo admin_url("admin.php?page=wprb-options"); ?>";
      </script>
    <?php
  } 

  public function menu() {
    $capability = 'remove_users';

    $sgmt_ctrl = new ctrls\Segments();

    add_menu_page(
      __('Revenue Booster'),
      __('Revenue Booster'),
      $capability,
      base\SLUG,
      [$this,'toplevel_menu_route'],
      'dashicons-chart-area',
      5
    );

    $options_ctrl = ctrls\Options::fetch();
    add_submenu_page(
      base\SLUG,
      __('WP Revenue Booster | Options', 'wp-revenue-booster'),
      __('Options', 'wp-revenue-booster'),
      $capability,
      base\SLUG_KEY . '-options',
      [$options_ctrl, 'route']
    );

    do_action(base\SLUG_KEY . '_menu');
  }

  public function enqueue_admin_scripts($hook) {
    wp_enqueue_style('wprb-admin', base\CSS_URL . '/admin.css');
    wp_enqueue_script('wprb-admin', base\JS_URL . '/admin.js');
  }

}

