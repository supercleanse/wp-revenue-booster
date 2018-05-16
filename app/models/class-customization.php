<?php
namespace wp_revenue_booster\models; 
if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\models as models;

class Customization extends lib\Base_Model {
  public function __construct($obj = null) {
    $this->initialize(
      [
        'id'         => [ 'type' => 'integer', 'default' => 0 ],
        'page_uri'   => [ 'type' => 'string', 'default' => null ],
        'selector'   => [ 'type' => 'string', 'default' => null ],
        'content'    => [ 'type' => 'string', 'default' => null ],
        'status'     => [ 'type' => 'string', 'default' => 'active' ],
        'segment_id' => [ 'type' => 'integer', 'default' => 0 ],
      ], 
      $obj
    );

    $this->statuses = [
      'active', // Enabled, working and in place
      'not_found', // Selector is no longer found
      'disabled' // Admin has disabled
    ];
  }

  public function validate() {
    // Nothing to do here at this point
    $this->validate_not_empty($this->page_uri, 'page_uri');
    $this->validate_not_empty($this->selector, 'selector');
    $this->validate_not_empty($this->content, 'content');
    $this->validate_not_empty($this->segment_id, 'segment_id');
    $this->validate_is_in_array($this->status, $this->statuses, 'status'); $this->validate_is_numeric($this->segment_id, 0, null, 'segment_id');
  }

  public function sanitize() {
    $this->page_uri   = sanitize_text_field($this->page_uri);
    $this->selector   = sanitize_text_field($this->selector); $this->content    = wp_kses_post($this->content);
    $this->status     = sanitize_text_field($this->status);
    $this->segment_id = sanitize_text_field($this->segment_id);
  }

  public function store($validate = true) {
    if($validate) {
      try {
        $this->validate();
      }
      catch(Exception $e) {
        return new WP_Error(get_class($e), $e->getMessage());
      }
    }

    if(isset($this->id) && !is_null($this->id) && (int)$this->id > 0) {
      $this->id = self::update($this);
    }
    else {
      $this->id = self::create($this);
    }

    do_action(base\SLUG_KEY.'_customization_store', $this);

    return $this->id;
  }

  public function destroy() {
    $db = lib\Db::fetch();
    do_action(base\SLUG_KEY.'_customization_destroy', $this);
    return $db->delete_records($db->customizations, array('id'=>$this->id));
  }

  public static function create($obj) {
    $db = lib\Db::fetch();

    $args = (array)$obj->get_values();
    unset($args['id']);

    return $db->create_record($db->customizations,$args,false /* ignore created_at */);
  }

  public static function update($obj) {
    $db = lib\Db::fetch();
    return $db->update_record($db->customizations,$obj->id,(array)$obj->get_values());
  }

  public static function get_all_by_page_uri($page_uri, $order_by='selector', $limit='') {
    return self::get_all($order_by, $limit, compact('page_uri'));
  }

  /** Return all the customizations for a given page. Optionally restrict to one selector.
   */
  public static function get_page_customizations($page_uri, $selector=null) {

    if(is_null($selector)) {
      $objs = self::get_all_by_page_uri($page_uri);
    }
    else {
      $objs = self::get_all_by_page_uri_and_selector($page_uri,$selector);
    }

    if(empty($objs)) { return []; }

    $customizations = [];
    $last_selector = '';
    foreach($objs as $c) {
      if($c->selector != $last_selector) {
        $customizations[$c->selector] = [];
      }

      $customizations[$c->selector][] = $c->get_values();

      $last_selector = $c->selector;
    }

    if(is_null($selector)) {
      return $customizations;
    }
    else {
      return $customizations[$c->selector];
    }
  }

  public static function get_all_by_page_uri_and_selector($page_uri, $selector, $order_by='', $limit='') {
    return self::get_all($order_by, $limit, compact('page_uri','selector'));
  }

  public static function get_all_by_page_uri_and_segments($page_uri, $segment_ids=[], $order_by='', $limit='') {
    global $wpdb;

    if(is_numeric($segment_ids)) { $segment_ids = [$segment_ids]; }

    if(empty($segment_ids)) { return []; }

    $db = lib\Db::fetch();

    $q = $wpdb->prepare("
        SELECT c.id
          FROM {$db->customizations} AS c
         WHERE c.page_uri=%s
           AND c.segment_id IN (".implode(',',$segment_ids).")
      ",
      $page_uri
    );

    $ids = $wpdb->get_col($q);

    if(empty($ids)) {
      return [];
    }

    $customizations = [];
    foreach($ids as $id) {
      $customizations[] = new models\Customization($id);
    }

    return $customizations;
  }

  public static function get_unique_customization_segments_by_page_uri($page_uri) {
    global $wpdb;

    $db = lib\Db::fetch();

    $q = $wpdb->prepare("
        SELECT DISTINCT c.segment_id
          FROM {$db->customizations} AS c
         WHERE c.page_uri=%s
      ",
      $page_uri
    );

    $ids = $wpdb->get_col($q);

    if(empty($ids)) {
      return [];
    }

    return $ids;
  }
}

