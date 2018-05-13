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

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

define('WPRB_PLUGIN_ID', 'wp-revenue-booster/main.php');
define('WPRB_PLUGIN_SLUG', 'wp-revenue-booster');
define('WPRB_PATH', WP_PLUGIN_DIR . '/' . WPRB_PLUGIN_SLUG);

// Make all of our URLS protocol agnostic
$wprb_url_protocol = (is_ssl())?'https':'http';
define('WPRB_URL',preg_replace('/^https?:/', "{$wprb_url_protocol}:", plugins_url('/'.WPRB_PLUGIN_SLUG)));

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
// HOW WILL WE SAVE THE ELEMENT? Do we base it on the element or on the text?
//
// OR Should we implement it as a shortcode? This would have several advantages for our initial implementation.

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

    wp_enqueue_style('wprb-selection', WPRB_URL . '/wprb-selection.css');

    wp_register_script('wprb-tippy', 'https://unpkg.com/tippy.js@2.5.2/dist/tippy.all.min.js');
    wp_register_script('wprb-css-selector-generator', WPRB_URL . '/lib/css-selector-generator.min.js');
    wp_enqueue_script('wprb-selection', WPRB_URL . '/wprb-selection.js', ['jquery','wprb-tippy','wprb-css-selector-generator']);
    wp_localize_script('wprb-selection', 'WPRB', compact('segments', 'customizations'));
  }
}

$wprb = new WP_Revenue_Booster();

