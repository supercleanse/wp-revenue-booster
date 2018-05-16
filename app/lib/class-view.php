<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as model;

class View {
  public static function file($slug,$paths=array()) {
    $paths = (empty($paths) ? self::paths() : $paths);

    $find = $slug . '.php';

    // the filename will always be prepended with 'template-'
    // as per WordPress Code Style Guidelines
    $find = preg_replace('#^([^/]*)$#','template-$1',$find); // if no telescoping
    $find = preg_replace('#/([^/]*)$#','/template-$1',$find); // When there's telescoping

    if(!preg_match('#^/#', $find)) { $find = '/' . $find; }

    // Since we're including our default path, the file will be
    // found so we're not doing any validation for that here
    foreach($paths as $path) {
      if(file_exists($path . $find)) {
        return $path . $find;
      }
    }

    return false;
  }

  /** Used to get a string of a view. We can use this when calling a file to
    * pass all the locally defined variables as the args variable:
    *
    * lib\View::get_string('mycoolstuff/what', get_defined_vars());
    *
    */
  public static function get_string($slug, $vars=array(), $paths=array()) {
    $paths = apply_filters(base\SLUG_KEY . '_view_paths_get_string_'.$slug, $paths, $slug, $vars);
    $paths = apply_filters(base\SLUG_KEY . '_view_paths_get_string', $paths, $slug, $vars);

    $template_part_slug = base\SLUG.'/'.dirname($slug);
    $template_part_name = basename($slug);

    do_action( "get_template_part_{$template_part_slug}", $template_part_slug, $template_part_name );

    extract($vars, EXTR_SKIP);

    $file = self::file($slug,$paths);

    if(!$file) { return; }

    ob_start();
    require($file);
    $view = ob_get_clean();

    $view = apply_filters(base\SLUG_KEY.'_view_get_string_'.$slug, $view, $vars); // Slug specific filter
    $view = apply_filters(base\SLUG_KEY.'_view_get_string', $view, $slug, $vars); // General filter

    return $view;
  }

  /** Used to render a view. We can use this when calling a file to
    * pass all the locally defined variables as the args variable:
    *
    * lib\View::render('mycoolstuff/what', get_defined_vars());
    *
    */
  public static function render($slug, $vars=array(), $paths=array()) {
    $view = self::get_string($slug, $vars, $paths);

    echo $view;

    return $view;
  }

  public static function paths() {
    $paths = array();

    $template_path = get_template_directory();
    $stylesheet_path = get_stylesheet_directory();

    //Put child theme's first if one's being used
    if($stylesheet_path!==$template_path) {
      $paths[] = "{$stylesheet_path}/".base\SLUG_KEY;
    }

    $paths[] = "{$template_path}/".base\SLUG_KEY;
    $paths[] = base\VIEWS_PATH;

    return apply_filters(base\SLUG_KEY.'_view_paths', $paths);
  }
}

