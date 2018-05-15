<?php
namespace wp_revenue_booster\helpers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;

class App {
  public static function info_tooltip($id, $title, $info) {
    ?>
    <span id="admin-tooltip-<?php echo $id; ?>" class="admin-tooltip">
      <span class="info-icon dashicons dashicons-info"></span>
      <span class="data-title hidden"><?php echo $title; ?></span>
      <span class="data-info hidden"><?php echo $info; ?></span>
    </span>
    <?php
  }

  public static function get_countries() {
    $countries = require(base\LIB_PATH . '/data/countries.php');
    return $countries;
  }

  public static function get_states() {
    $countries = self::get_countries();

    $states = [];
    $file_names = @glob(base\LIB_PATH . '/data/states/*.php', GLOB_NOSORT);
    foreach($file_names as $file_name) {
      require($file_name); 
    }

    $return_states = [];
    foreach($states as $country_code => $country_states) {
      foreach($country_states as $state_code => $state_name) {
        $country_name = $countries[$country_code];
        $return_states["{$country_code}_{$state_code}"] = "{$country_name} -> {$state_name}";
      }
    }

    return $return_states;
  }
}
                          
