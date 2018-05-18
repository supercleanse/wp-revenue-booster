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

    // Don't show the toolbar link on the back end
    if(!is_admin() && lib\Utils::is_logged_in_and_an_admin()) {
      add_action('admin_bar_menu', [$this,'add_toolbar_links'], 999);
      add_action('the_content', [$this,'add_content_links'], 999);
    }

    add_action( 'wp_ajax_wprb_update_customizations', [$this,'ajax_update'] );
  }

  public function enqueue_selection_scripts() {
    $strings = [
      'add_selection' => __('Click to Add Customizations'),
      'edit_selection' => __('Click to Edit Customizations')
    ];

    $page_uri = lib\Utils::get_page_uri();
    $page_uri = preg_replace('/[\?&]wprb-selection/', '', $page_uri); // Remove wprb-selection param

    $customizations = models\Customization::get_page_customizations($page_uri);
    $selections = array_keys($customizations);
    $segments = models\Segment::get_all();
    $security = \wp_create_nonce('wprb-update-customizations');
    $ajaxurl = \admin_url('admin-ajax.php');

    $popup = lib\View::get_string('customizations/popup', compact('page_uri'));

    $popup_row = lib\View::get_string('customizations/popup-row', compact('segments'));

    wp_register_style('wprb-jquerymodal', base\CSS_URL . '/lib/jquery.modal.css');
    wp_enqueue_style('wprb-selection', base\CSS_URL . '/selection.css',['wprb-jquerymodal']);

    wp_register_script('wprb-tippy', 'https://unpkg.com/tippy.js@2.5.2/dist/tippy.all.min.js');
    wp_register_script('wprb-css-selector-generator', base\JS_URL . '/lib/css-selector-generator.min.js');
    wp_register_script('wprb-jquerymodal', base\JS_URL . '/lib/jquery.modal.js', ['jquery']);
    wp_enqueue_script('wprb-selection', base\JS_URL . '/selection.js', ['jquery','wprb-tippy','wprb-css-selector-generator','wprb-jquerymodal']);
    wp_localize_script('wprb-selection', 'WPRB_Customization', compact('segments', 'selections', 'customizations', 'strings', 'page_uri','popup','popup_row','security','ajaxurl'));
  }

  private function get_toggle_link() {
    $uri = $_SERVER['REQUEST_URI'];

    if(isset($_REQUEST['wprb-selection'])) {
      // Remove the selection param
      // If param is first of many
      $uri = preg_replace('/\?wprb-selection&/','?',$uri);

      // If param is first and only
      $uri = preg_replace('/\?wprb-selection/','',$uri);

      // If param is not first of many
      $uri = preg_replace('/&wprb-selection/','',$uri);
    }
    else {
      $delim = lib\Utils::get_delim($uri);

      $uri = "{$uri}{$delim}wprb-selection";
    }

    return $uri;
  }

  // Add a parent shortcut link
  public function add_toolbar_links($wp_admin_bar) {
    $uri = $this->get_toggle_link();

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

  /** This places a link at the end of the_content for toggling in and out of customization mode. */
  public function add_content_links($content) {
    $url = $this->get_toggle_link();

    if(isset($_REQUEST['wprb-selection'])) {
      $cust_text = __('Finish Adding Dynamic Text');
    }
    else {
      $cust_text = __('Add Dynamic Text');
    }

    $link = "<div class=\"wprb-toggle-selection-mode\"><a href=\"{$url}\">{$cust_text}</a></div>";

    return $content.$link;
  }

  public function ajax_update() {
    lib\Utils::check_ajax_referer('wprb-update-customizations','security');

    if(!lib\Utils::is_post_request()) {
      lib\Utils::exit_with_status(404,json_encode(['error'=>__('Not Found', 'wp-revenue-booster')]));
    }

    if(!lib\Utils::is_user_admin()) {
      lib\Utils::exit_with_status(403,json_encode(['error'=>__('Forbidden', 'wp-revenue-booster')]));
    }

    if(!isset($_POST['page_uri'])) {
      lib\Utils::exit_with_status(400,json_encode(['error'=>__('Must specify a page_uri', 'wp-revenue-booster')]));
    }

    if(!isset($_POST['selector'])) {
      lib\Utils::exit_with_status(400,json_encode(['error'=>__('Must specify a selector', 'wp-revenue-booster')]));
    }

    //if(!isset($_POST['cust'])) {
    //  lib\Utils::exit_with_status(400,json_encode(['error'=>__('Must specify customizations', 'wp-revenue-booster')]));
    //}

    if(!isset($_POST['cust']) || empty($_POST['cust'])) {
      $_POST['cust'] = [];
    }

    $page_uri = \sanitize_text_field($_POST['page_uri']);
    $selector = \sanitize_text_field($_POST['selector']);

    $old_cust = models\Customization::get_page_customizations($page_uri, $selector);

    // Track the cust id's we're updating
    $ids_updated = [];
    foreach($_POST['cust'] as $i => $c) {
      // If an id is set then we're updating
      if(isset($c['id']) && !empty($c['id'])) {
        $customization = new models\Customization($c['id']);
        $ids_updated[] = $c['id'];
      }
      else {
        $customization = new models\Customization();
      }

      // Sanitization on these happen in the model
      $customization->page_uri = $_POST['page_uri']; // Use unsanitized version to prevent double sanitization
      $customization->selector = $_POST['selector']; // Use unsanitized version to prevent double sanitization
      $customization->content = stripslashes($c['content']);
      $customization->segment_id = $c['segment_id'];
      $customization->store();
    }

    // If there are any old_cust's that weren't updated then that means the user deleted them so get rid of them
    foreach($old_cust as $c) {
      if(!in_array($c['id'], $ids_updated)) {
        $customization = new models\Customization($c['id']);
        $customization->destroy();
      }
    }

    return lib\Utils::exit_with_status(
      200,
      json_encode([
        'message' => __('Your customizations were successfully updated', 'wp-revenue-booster'),
        'customizations' => models\Customization::get_page_customizations($page_uri, $selector)
      ])
    );
  }

}

