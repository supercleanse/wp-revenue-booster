<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

/** Specific base class for Builtin Style models */
abstract class Base_Builtin_Model extends Base_Model {
  public $meta_attrs;
  /** Get all the meta attributes and default values */
  public function get_meta_attrs() {
    return (array)$this->meta_attrs;
  }
}

