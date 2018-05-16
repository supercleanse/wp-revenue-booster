<?php
namespace wp_revenue_booster\models;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster\lib as lib;

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

    return $db->create_record($db->customization,$args);
  }

  public static function update($obj) {
    $db = lib\Db::fetch();
    return $db->update_record($db->customizations,$obj->id,(array)$obj->get_values());
  }

  public static function get_all_by_page_uri($page_uri, $order_by='selector', $limit='') {
    return self::get_all($order_by, $limit, compact('page_uri'));
  }

  public static function get_page_customizations($page_uri) {
    $cust = self::get_all_by_page_uri($page_uri);

    if(empty($cust)) { return []; }

    $customizations = [];
    $last_selector = '';
    foreach($customizations as $c) {
      if($c->selector != $last_selector) {
        $customizations[$c->selector] = [];
      }

      $customizations[$c->selector][] = $c;

      $last_selector = $c->selector;
    }

    return $customizations;
  }
}

