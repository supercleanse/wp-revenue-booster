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
    $loc = [
      'user_request_data' => models\Segment::get_user_request_data(),
      'customizations' => $this->get_matching_customizations()
    ];

    wp_enqueue_style('wprb-content', base\CSS_URL . '/content.css');
    wp_enqueue_script('wprb-content', base\JS_URL . '/content.js', ['jquery']);
    wp_localize_script('wprb-content', 'WPRB_Content', $loc);
  }


  /** This method gathers all of the customizations that match this page_uri AND for which the user is in a segment.
   */
  public function get_matching_customizations() {
    $page_uri = lib\Utils::get_page_uri();
    $segment_ids = models\Customization::get_unique_customization_segments_by_page_uri($page_uri);

    $user_matched_segments = [];

    foreach($segment_ids as $segment_id) {
      $segment = new models\Segment($segment_id);

      if($segment->current_user_matches_segment()) {
        $user_matched_segments = $segment_id;
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

}

