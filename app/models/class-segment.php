<?php
namespace wp_revenue_booster\models;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster\lib as lib;

class Segment extends lib\Base_Cpt_Model {
  public static $cpt = 'mprb-segment';

  public function __construct($obj = null) {
    $this->load_cpt(
      $obj,
      self::$cpt,
      [
        'rules' => [ 'default' => [], 'type' => 'object', (object)[] ],
      ]
    );
  }

  public function validate() {
    // Nothing to do here at this point
  }

  public function sanitize() {
    // Nothing to do here at this point
  }
}

