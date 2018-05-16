<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;

/** Ctrls are all singletons, so we can use this
  * factory to churn out objects for us.
  */
class Ctrl_Factory {
  public static function fetch($class, $args=array()) {
    static $objs;

    if(0 !== strpos($class, base\CTRLS_NAMESPACE)) {
      $class = base\CTRLS_NAMESPACE . '\\' . Inflector::wp_classify($class);
    }

    if(isset($objs[$class]) && ($objs[$class] instanceof lib\Base_Ctrl)) {
      return $objs[$class];
    }

    if(!class_exists($class)) {
      throw new \Exception(sprintf(__('Ctrl: %s wasn\'t found', 'wp-revenue-booster'), $class));
    }

    // We'll let the autoloader in main.php
    // handle including files containing these classes
    $r = new \ReflectionClass($class);
    $obj = $r->newInstanceArgs($args);

    $objs[$class] = $obj;

    return $obj;
  }

  public static function all($args=array()) {
    $objs = array();

    foreach(self::paths() as $path) {
      $file_names = @glob($path . '/*.php', GLOB_NOSORT);
      foreach($file_names as $file_name) {
        if(basename($file_name) == 'index.php') {
          continue; // don't load index.php
        }
        $file_name = preg_replace('#\.php#', '', basename($file_name));
        $class = lib\Utils::wp_classname($file_name);
        $objs[$class] = self::fetch($class, $args);
      }
    }

    return $objs;
  }

  public static function paths() {
    return apply_filters(base\SLUG_KEY.'_ctrls_paths', array(base\CTRLS_PATH));
  }
}

