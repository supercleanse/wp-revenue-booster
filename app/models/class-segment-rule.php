<?php
namespace wp_revenue_booster\models;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster\lib as lib;

class Segment_Rule extends lib\Base_Model {
  public function __construct($obj = null) {
    $this->initialize(
      [
        'rule_type'      => [ 'type' => 'string' ],
        'rule_operator'  => [ 'type' => 'string' ],
        'rule_condition' => [ 'type' => 'string' ],
        'rule_order'     => [ 'type' => 'integer', 'default' => 0 ],
        'segment_id'     => [ 'type' => 'integer', 'default' => 0 ]
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
    $this->validate_not_empty($this->rule_type, 'rule_type');
    $this->validate_not_empty($this->rule_operator, 'rule_operator');
    $this->validate_not_empty($this->rule_condition, 'rule_condition');
    $this->validate_not_empty($this->rule_order, 'rule_order');
    $this->validate_not_empty($this->segment_id, 'segment_id');
    $this->validate_is_numeric($this->segment_id, 0, null, 'segment_id');
  }

  public function sanitize() {
    $this->rule_type      = sanitize_text_field($this->rule_type);
    $this->rule_operator  = sanitize_text_field($this->rule_operator);
    $this->rule_condition = sanitize_text_field($this->rule_condition);
    $this->rule_order     = sanitize_text_field($this->rule_order);
    $this->segment_id     = sanitize_text_field($this->segment_id);
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

    do_action(base\SLUG_KEY.'_segment_rule_store', $this);

    return $this->id;
  }

  public function destroy() {
    $db = lib\Db::fetch();
    do_action(base\SLUG_KEY.'_segment_rule_destroy', $this);
    return $db->delete_records($db->segment_rules, array('id'=>$this->id));
  }

  public static function create($obj) {
    $db = lib\Db::fetch();

    $args = (array)$obj->get_values();
    unset($args['id']);

    return $db->create_record($db->segment_rule,$args);
  }

  public static function update($obj) {
    $db = lib\Db::fetch();
    return $db->update_record($db->segment_rules,$obj->id,(array)$obj->get_values());
  }

  public static function get_all_by_segment($segment_id, $order_by='rule_order', $limit='') {
    return self::get_all($order_by, $limit, compact('segment_id'));
  }
}

