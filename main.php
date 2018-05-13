<?php
/*
Plugin Name: WP Revenue Booster
Plugin URI: https://wprevenuebooster.com/
Description: Personalize the text on your site based on customer segmentation.
Version: 1.0.0
Author: Chris Lema, Blair Williams
Author URI: https://chrislema.com/
Text Domain: wp-revenue-booster
*/

namespace wp_revenue_booster;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use \wp_revenue_booster\lib as lib;
use \wp_revenue_booster\controllers as ctrl;

define(__NAMESPACE__ . '\ROOT_NAMESPACE', __NAMESPACE__);
define(ROOT_NAMESPACE . '\CTRLS_NAMESPACE', ROOT_NAMESPACE . '\controllers');
define(ROOT_NAMESPACE . '\HELPERS_NAMESPACE', ROOT_NAMESPACE . '\helpers');
define(ROOT_NAMESPACE . '\MODELS_NAMESPACE', ROOT_NAMESPACE . '\models');
define(ROOT_NAMESPACE . '\LIB_NAMESPACE', ROOT_NAMESPACE . '\lib');

define(ROOT_NAMESPACE . '\ID', 'wp-revenue-booster/main.php');
define(ROOT_NAMESPACE . '\SLUG', 'wp-revenue-booster');
define(ROOT_NAMESPACE . '\PATH', WP_PLUGIN_DIR . '/' . SLUG);

// Make all of our URLS protocol agnostic
$wprb_url_protocol = (is_ssl())?'https':'http'; // Make all of our URLS protocol agnostic
define(ROOT_NAMESPACE . '\URL', preg_replace('/^https?:/', "{$wprb_url_protocol}:", plugins_url('/'.SLUG)));

// Cookie the user immediately if they aren't already cookied
// V2 Modify the cookie based on customer usage
// Cookie should store:
// - Location
// - Device
// - OS
// - Browser
//
// When in select mode (?wprb-select):
// - insert "?wprb-select" into every internal link when in select mode
// - change background color or border or both on hover
// - on click, popup interface
// - :
//

// WPRB Selection Stuff
class WP_Revenue_Booster {
  public function __construct() {
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

    wp_enqueue_style('wprb-selection', URL . '/wprb-selection.css');

    wp_register_script('wprb-tippy', 'https://unpkg.com/tippy.js@2.5.2/dist/tippy.all.min.js');
    wp_register_script('wprb-css-selector-generator', URL . '/lib/css-selector-generator.min.js');
    wp_enqueue_script('wprb-selection', URL . '/wprb-selection.js', ['jquery','wprb-tippy','wprb-css-selector-generator']);
    wp_localize_script('wprb-selection', 'WPRB', compact('segments', 'selections', 'customizations', 'strings', 'page'));
  }
}

$wprb = new WP_Revenue_Booster();

