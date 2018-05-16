<?php
namespace wp_revenue_booster\models;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster\lib as lib;
use wp_revenue_booster\models as models;

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

  /** This gathers all the user information for this request.
   */
  public static function get_user_request_data() {
    // Only call this once per request
    static $user_request_data;

    if(!isset($user_request_data)) {
      $user_request_data = [];

      // Logged In
      $user_request_data['logged_in'] = lib\Utils::is_user_logged_in();

      // Returning Visitor
      // TODO: Should we do this page by page?
      $user_request_data['returning_visitor'] = isset($_COOKIE['wprb_visitor']);

      if(!isset($_COOKIE['wprb_visitor'])) {
        setcookie('wprb_visitor', 'https://wprevenuebooster.com', time() + lib\Utils::months(1));
      }

      // TODO:
      // Device
      // Browser
      // Country
      // State
    }

    return (object)$user_request_data;
  }

  /** This gathers all the user information for this request.
   */
  public function current_user_matches_segment() {
    if(!isset($this->rules->condition) || !isset($this->rules->rules)) {
      return false;
    }

    $user_request_data = models\Segment::get_user_request_data();

    // When $or it's false until there's one true
    // When $and it's true until there's one false
    $user_is_a_match = ($this->rules->condition=='AND');
    foreach($this->rules->rules as $rule) {

      if($rule->id=='logged_in') {
        $passed = $this->test_rule((int)$user_request_data->logged_in,$rule);
        if($this->should_break($passed)) {
          $user_is_a_match = $passed; break;
        } 
      }

      else if($rule->id=='returning_visitor') {
        $passed = $this->test_rule((int)$user_request_data->returning_visitor,$rule);
        if($this->should_break($passed)) {
          $user_is_a_match = $passed; break;
        } 
      }
    }

    return $user_is_a_match;
  }

  private function test_rule($value, $rule) {
    return (($value==$rule->value && $rule->operator=='equal') ||
            ($value!=$rule->value && $rule->operator=='not_equal'));
  }

  private function should_break($passed) {
    $op = $this->rules->condition;
    // When $or it's false until there's one true
    // When $and it's true until there's one false
    return (($passed && (strtoupper($op)=='OR')) ||
            (!$passed && (strtoupper($op)=='AND')));
  }
}

