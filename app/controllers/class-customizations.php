<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as models;

class Customizations extends lib\Base_Ctrl {
  public function load_hooks() {
    if(!is_admin() && lib\Utils::is_logged_in_and_an_admin() && array_key_exists('wprb-selection', $_REQUEST)) {
      add_action('wp_enqueue_scripts', [$this,'enqueue_selection_scripts'], 10);
    }

    add_action('admin_bar_menu', [$this,'toolbar_links'], 999);
  }

  public function enqueue_selection_scripts() {
    $strings = [
      'add_selection' => __('Click to Add Customization'),
      'selection_added' => __('Customization Added'),
      'remove_selection' => __('Click to Remove Customization'),
      'selection_removed' => __('Customization Removed')
    ];

    // Get a clean page url with appropriate query string
    $home = preg_replace('/^https?:/', 'https?:', home_url());
    $page_uri = preg_replace('!' . preg_quote($home,'!') . '!', '', $_SERVER['REQUEST_URI']); // Remove full home path from page_uri
    $page_uri = preg_replace('/[\?&]wprb-selection/', '', $page_uri); // Remove wprb-selection param

    $popup = lib\View::get_string('customizations-popup');

    $customizations = models\Customization::get_page_customizations($page_uri);
    $selections = array_keys($customizations);
    $segments = models\Segment::get_all();

    wp_register_style('wprb-jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
    wp_enqueue_style('wprb-selection', base\CSS_URL . '/selection.css',['wprb-jquerymodal']);

    wp_register_script('wprb-tippy', 'https://unpkg.com/tippy.js@2.5.2/dist/tippy.all.min.js');
    wp_register_script('wprb-css-selector-generator', base\JS_URL . '/lib/css-selector-generator.min.js');
    wp_register_script('wprb-jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', ['jquery']);
    wp_enqueue_script('wprb-selection', base\JS_URL . '/selection.js', ['jquery','wprb-tippy','wprb-css-selector-generator','wprb-jquerymodal']);
    wp_localize_script('wprb-selection', 'WPRB_Customization', compact('segments', 'selections', 'customizations', 'strings', 'page_uri','popup'));
  }

  // Add a parent shortcut link
  public function toolbar_links($wp_admin_bar) {
    $uri = $_SERVER['REQUEST_URI'];

    // Remove the selection param
    // If param is first of many
    $uri = preg_replace('/\?wprb-selection&/','?',$uri);

    // If param is first and only
    $uri = preg_replace('/\?wprb-selection/','',$uri);

    // If param is not first of many
    $uri = preg_replace('/&wprb-selection/','',$uri);

    $args = [];
    if(isset($_REQUEST['wprb-selection'])) {
      $args = [
        'id' => 'wp-revenue-booster',
        'title' => __('Finish Adding Dynamic Text'), 
        'href' => $uri,
        'meta' => [
          'class' => 'wprb-exit-selection-mode', 
          'title' => __('Exit WP Revenue Booster Dynamic Text Selection Mode.')
        ]
      ];
    }
    else {
      $delim = lib\Utils::get_delim($uri);

      $uri = "{$uri}{$delim}wprb-selection";

      $args = [
        'id' => 'wp-revenue-booster',
        'title' => __('Add Dynamic Text'), 
        'href' => $uri, 
        'meta' => [
          'class' => 'wprb-enter-selection-mode', 
          'title' => __('Add Custom Dynamic Text with WP Revenue Booster')
        ]
      ];
    }

    $wp_admin_bar->add_node($args);
  }

}

