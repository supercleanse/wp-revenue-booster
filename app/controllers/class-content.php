<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base,
    wp_revenue_booster\lib as lib,
    wp_revenue_booster\controllers as ctrls,
    wp_revenue_booster\models as models;

class Content extends lib\Base_Ctrl {
  public function load_hooks() {
    if(!is_admin() && !array_key_exists('wprb-selection', $_REQUEST)) {
      add_action('wp_enqueue_scripts', [$this,'enqueue_content_scripts'], 5);
    }

    add_action( 'wp_ajax_wprb_get_customizations', [$this,'ajax_get_customizations'] );
    add_action( 'wp_ajax_nopriv_wprb_get_customizations', [$this,'ajax_get_customizations'] );
  }

  public function enqueue_content_scripts() {
    $page_uri = lib\Utils::get_page_uri();
    $ajaxurl = \admin_url('admin-ajax.php');

    $loc = compact('page_uri','ajaxurl');

    wp_enqueue_style('wprb-content', base\CSS_URL . '/content.css');
    wp_enqueue_script('wprb-content', base\JS_URL . '/content.js', ['jquery']);
    wp_localize_script('wprb-content', 'WPRB_Content', $loc);
  }


  /** This method gathers all of the customizations that match this page_uri AND for which the user is in a segment.
   */
  public function get_matching_customizations($page_uri) {
    $segment_ids = models\Customization::get_unique_customization_segments_by_page_uri($page_uri);

    $user_matched_segments = [];

    foreach($segment_ids as $segment_id) {
      $segment = new models\Segment($segment_id);

      $urd = lib\User_Data::get_user_request_data();

      if($segment->check_for_match($urd)) {
        $user_matched_segments[] = $segment_id;
      }
    }

    $matching_customizations = models\Customization::get_all_by_page_uri_and_segments($page_uri, $user_matched_segments);

    if(empty($matching_customizations)) { return []; }

    // Filter duplicate selectors
    $selectors = [];
    $filtered_matching_customizations = [];
    foreach($matching_customizations as $c) {
      $s = $c->selector;

      if(!in_array($s, $selectors)) {
        // There's not already a customization for this selector
        $selectors[] = $s;
        $filtered_matching_customizations[] = [
          'selector' => $c->selector,
          'content' => $c->content,
          'segment_id' => $c->segment_id
        ];
      }
    }
    
    return $filtered_matching_customizations;
  }

  public function ajax_get_customizations() {
    if(!lib\Utils::is_post_request()) {
      lib\Utils::exit_with_status(404,json_encode(['error'=>__('Not Found', 'wp-revenue-booster')]));
    }

    if(!isset($_POST['page_uri'])) {
      lib\Utils::exit_with_status(400,json_encode(['error'=>__('Must specify a page_uri', 'wp-revenue-booster')]));
    }

    // TODO: Cache this in a cookie with the md5 hashed user_request_data
    // to be used as a dirty bit on whether we should get new info again.
    $customizations = $this->get_matching_customizations($_POST['page_uri']);

    return lib\Utils::exit_with_status(200, json_encode($customizations));
  }
}

