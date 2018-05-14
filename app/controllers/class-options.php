<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;

class Options extends lib\Base_Ctrl {
  public function load_hooks() {
    add_action('wprb_admin_general_options', array($this, 'general'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
  }

  public function route() {
    lib\View::render('admin/options/form');
  }

  public function general() {
    lib\View::render('admin/options/general');
  }

  public function enqueue_admin_scripts($hook) {
    wp_enqueue_script('wprb-tooltip', base\JS_URL . '/tooltip.js', ['jquery','wp-pointer'], base\VERSION);

    if( $hook == 'revenue-booster_page_wprb-options' ) {
      wp_enqueue_style('wprb-admin-options', base\CSS_URL . '/admin-options.css');
      wp_enqueue_script('wprb-admin-options', base\JS_URL . '/admin-options.js');
      wp_localize_script('wprb-admin-options', 'WPRB_Options', []);

      wp_enqueue_style('wprb-settings-table', base\CSS_URL . '/settings_table.css', null, base\VERSION);
      wp_enqueue_script('wprb-settings-table', base\JS_URL . '/settings_table.js', ['jquery'], base\VERSION);
    }
  }
}
