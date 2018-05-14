<?php
namespace wp_revenue_booster\models;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster\lib as lib;

class Segment extends lib\Base_Cpt_Model {
  public static $cpt = 'mprb-segment';
  public $match_types;

  public function __construct($obj = null) {
    $this->load_cpt(
      $obj,
      self::$cpt,
      [
        'priority'   => [ 'default' => 1,     'type' => 'integer' ],
        'matches'    => [ 'default' => [],    'type' => 'array' ],
        'match_type' => [ 'default' => 'all', 'type' => 'string' ]
      ]
    );

    $this->match_types = [ 'all', 'any' ];
  }

  public function validate() {
    $this->validate_is_in_array($this->match_type, $this->match_types, 'match_type');
  }

  public function sanitize() {
    // Nothing to do here at this point
  }
}

