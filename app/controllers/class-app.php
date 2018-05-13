<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrl;

class App extends lib\Base_Ctrl {
  public function load_hooks() {
    if(!is_admin() && array_key_exists('wprb-selection', $_REQUEST)) {
      add_action('wp_enqueue_scripts', [$this,'enqueue_selection_scripts'], 1);
    }
  }

  public function enqueue_selection_scripts() {
    // TODO: Replace this with the actual segments
    $segments = [
      101 => "Tablet Users",
      102 => "Cart Abandoners",
      103 => "Canadian Tablet Users",
      104 => "Mobile Users",
      107 => "Italian Mobile Users",
      110 => "PC Users"
    ];

    // TODO: Replace this with the actual customizations
    $customizations = [
      '.entry-content > :nth-child(1)' => [
        [
          'segment' => 103,
          'text' => 'You are a Canadian Tablet User ... Thanks for being AWESOME!'
        ],
        [
          'segment' => 102,
          'text' => 'You are a Cart Abandoner ... How are you here?'
        ]
      ],
      '.entry-title' => [
        [
          'segment' => 102,
          'text' => 'You Dirty Rotten Cart Abandoner ... Just Purchase Already!'
        ]
      ]
    ];

    $strings = [
      'add_selection' => __('Click to Add Customization'),
      'selection_added' => __('Customization Added'),
      'remove_selection' => __('Click to Remove Customization'),
      'selection_removed' => __('Customization Removed')
    ];

    // Get a clean page url with appropriate query string
    $home = preg_replace('/^https?:/', 'https?:', home_url());
    $page = preg_replace('!' . preg_quote($home,'!') . '!', '', $_SERVER['REQUEST_URI']); // Remove full home path from page
    $page = preg_replace('/[\?&]wprb-selection/', '', $page); // Remove wprb-selection param

    $selections = array_keys($customizations);

    $popup = lib\View::get_string('customizations-popup');

    wp_register_style('wprb-jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
    wp_enqueue_style('wprb-selection', base\CSS_URL . '/wprb-selection.css',['wprb-jquerymodal']);

    wp_register_script('wprb-tippy', 'https://unpkg.com/tippy.js@2.5.2/dist/tippy.all.min.js');
    wp_register_script('wprb-css-selector-generator', base\JS_URL . '/lib/css-selector-generator.min.js');
    wp_register_script('wprb-jquerymodal', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js', ['jquery']);
    wp_enqueue_script('wprb-selection', base\JS_URL . '/wprb-selection.js', ['jquery','wprb-tippy','wprb-css-selector-generator','wprb-jquerymodal']);
    wp_localize_script('wprb-selection', 'WPRB', compact('segments', 'selections', 'customizations', 'strings', 'page','popup'));
  }
}

