<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as models;

class Content extends lib\Base_Ctrl {
  public function load_hooks() {
    if(!is_admin() && !array_key_exists('wprb-selection', $_REQUEST)) {
      add_action('wp_enqueue_scripts', [$this,'enqueue_content_scripts'], 5);
    }
  }

  public function enqueue_content_scripts() {
    wp_enqueue_style('wprb-content', base\CSS_URL . '/content.css');
    wp_enqueue_script('wprb-content', base\JS_URL . '/content.js', ['jquery']);
    wp_localize_script('wprb-content', 'WPRB_Content', []);
  }

}

