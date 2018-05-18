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

  /** Check to see if user matches_segment. urd stands for User Request Data. */
  public function check_for_match($urd) {
    if(!isset($this->rules->condition) || !isset($this->rules->rules)) {
      return false;
    }

    // When $or it's false until there's one true
    // When $and it's true until there's one false
    $user_is_a_match = ($this->rules->condition=='AND');
    foreach($this->rules->rules as $rule) {

      // Not sure why this would happen but it could I suppose
      if(!isset($rule->id)) { continue; }

      $key = lib\Inflector::pluralize($rule->id);

      // If the user request data doesn't have the key we need
      // then just continue looping I suppose
      if(!isset($urd[$key])) { continue; }


      // Check user data against the rule and if there's a match
      // then short circuit with the correct value
      $passed = $this->check_rule_for_match($urd[$key],$rule);

      if($this->should_break($passed)) {
        $user_is_a_match = $passed; break;
      } 
    }

    return $user_is_a_match;
  }

  /** Takes in an array of matching values and tests against the rule's single value */
  private function check_rule_for_match($values, $rule) {
    // Check all the permutations ... as we add additional operators we can account for them here
    return ((in_array($rule->value, $values) && $rule->operator=='equal') ||
            (!in_array($rule->value, $values) && $rule->operator=='not_equal'));
  }

  /** Check to see if we're done evaluating rules for this segment.
   *
   * When condition is set to 'or' it's false until there's one true rule.
   * When condition is set to 'and' it's true until there's one false rule.
   */
  private function should_break($passed) {
    $op = $this->rules->condition;

    return (($passed && (strtoupper($op)=='OR')) ||
            (!$passed && (strtoupper($op)=='AND')));
  }
}

