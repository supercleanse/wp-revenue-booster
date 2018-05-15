<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;

/** Get a specific model from a name string. */
class Model_Factory {
  public static function fetch($model, $id) {
    $class = base\MODELS_NAMESPACE . '\\' . Inflector::wp_classify($model);

    if(!class_exists($class)) {
      return new WP_Error(sprintf(__('A model for %s wasn\'t found', 'wp-revenue-booster'), $model));
    }

    // We'll let the autoloader handle including files containing these classes
    $r = new \ReflectionClass($class);
    $obj = $r->newInstanceArgs(array($id));

    if(isset($obj->ID) && $obj->ID <= 0) {
      return new WP_Error(sprintf(__('There was no %s with an id of %d found', 'wp-revenue-booster'), $model, $obj->ID));
    }
    else if(isset($obj->id) && $obj->id <= 0) {
      return new WP_Error(sprintf(__('There was no %s with an id of %d found', 'wp-revenue-booster'), $model, $obj->id));
    }
    else if(isset($obj->term_id) && $obj->term_id <= 0) {
      return new WP_Error(sprintf(__('There was no %s with an id of %d found', 'wp-revenue-booster'), $model, $obj->term_id));
    }

    $objs[$class] = $obj;

    return $obj;
  }
}

