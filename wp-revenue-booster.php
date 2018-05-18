<?php
/*
Plugin Name: WP Revenue Booster
Plugin URI: https://wprevenuebooster.com/
Description: Dynamically personalize any text on your site based on your users' behavior.
Version: 1.0.2
Author: Blair Williams, Chris Lema
Author URI: https://wprevenuebooster.com/
Text Domain: wp-revenue-booster
*/

namespace wp_revenue_booster;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use \wp_revenue_booster\lib as lib;
use \wp_revenue_booster\controllers as ctrls;

define(__NAMESPACE__ . '\ROOT_NAMESPACE', __NAMESPACE__);
define(ROOT_NAMESPACE . '\CTRLS_NAMESPACE', ROOT_NAMESPACE . '\controllers');
define(ROOT_NAMESPACE . '\HELPERS_NAMESPACE', ROOT_NAMESPACE . '\helpers');
define(ROOT_NAMESPACE . '\MODELS_NAMESPACE', ROOT_NAMESPACE . '\models');
define(ROOT_NAMESPACE . '\LIB_NAMESPACE', ROOT_NAMESPACE . '\lib');

define(ROOT_NAMESPACE . '\ID', 'wp-revenue-booster/wp-revenue-booster.php');
define(ROOT_NAMESPACE . '\SLUG', 'wp-revenue-booster');
define(ROOT_NAMESPACE . '\SLUG_KEY', 'wprb');
define(ROOT_NAMESPACE . '\PATH', WP_PLUGIN_DIR . '/' . SLUG);
define(ROOT_NAMESPACE . '\CTRLS_PATH', PATH . '/app/controllers');
define(ROOT_NAMESPACE . '\HELPERS_PATH', PATH . '/app/helpers');
define(ROOT_NAMESPACE . '\MODELS_PATH', PATH . '/app/models');
define(ROOT_NAMESPACE . '\LIB_PATH', PATH . '/app/lib');
define(ROOT_NAMESPACE . '\VIEWS_PATH', PATH . '/app/views');
define(ROOT_NAMESPACE . '\JS_PATH', PATH . '/js');
define(ROOT_NAMESPACE . '\CSS_PATH', PATH . '/css');

// Make all of our URLS protocol agnostic
$wprb_url_protocol = (is_ssl())?'https':'http'; // Make all of our URLS protocol agnostic
define(ROOT_NAMESPACE . '\URL', preg_replace('/^https?:/', "{$wprb_url_protocol}:", plugins_url('/'.SLUG)));
define(ROOT_NAMESPACE . '\JS_URL', URL . '/js');
define(ROOT_NAMESPACE . '\CSS_URL', URL . '/css');

define(ROOT_NAMESPACE . '\DB_VERSION', 5);

/**
 * Returns current plugin version.
 *
 * @return string Plugin version
 */
function plugin_info($field) {
  static $plugin_folder, $plugin_file;

  if( !isset($plugin_folder) or !isset($plugin_file) ) {
    if( ! function_exists( 'get_plugins' ) ) {
      require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }

    $plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
    $plugin_file = basename( ( __FILE__ ) );
  }

  if(isset($plugin_folder[$plugin_file][$field])) {
    return $plugin_folder[$plugin_file][$field];
  }

  return '';
}

// Plugin Information from the plugin header declaration
define(ROOT_NAMESPACE . '\VERSION', plugin_info('Version'));
define(ROOT_NAMESPACE . '\DISPLAY_NAME', plugin_info('Name'));
define(ROOT_NAMESPACE . '\AUTHOR', plugin_info('Author'));
define(ROOT_NAMESPACE . '\AUTHOR_URI', plugin_info('AuthorURI'));
define(ROOT_NAMESPACE . '\DESCRIPTION', plugin_info('Description'));

// Autoload all the requisite classes
function autoloader($class_name) {
  // Only load WP Revenue Booster Classes
  if(0 === strpos($class_name, ROOT_NAMESPACE)) {
    preg_match('/([^\\\]*)$/', $class_name, $m);

    $class = $m[1];

    $file_name = strtolower($class);
    $file_name = 'class-' . preg_replace('/_/','-',$file_name);

    $filepath = '';

    if(0 === strpos($class_name, LIB_NAMESPACE . '\Base_/')) {
      $filepath = LIB_PATH."/{$file_name}.php";
    }
    else if(0 === strpos($class_name, CTRLS_NAMESPACE)) {
      $filepath = CTRLS_PATH."/{$file_name}.php";
    }
    else if(0 === strpos($class_name, LIB_NAMESPACE . '\.+_Exception')) {
      $filepath = LIB_PATH."/Exceptions.php";
    }
    else if(0 === strpos($class_name, HELPERS_NAMESPACE)) {
      $filepath = HELPERS_PATH."/{$file_name}.php";
    }
    else if(0 === strpos($class_name, MODELS_NAMESPACE)) {
      $filepath = MODELS_PATH."/{$file_name}.php";
    }
    else if(0 === strpos($class_name, LIB_NAMESPACE)) {
      $filepath = LIB_PATH."/{$file_name}.php";
    }

    if(file_exists($filepath)) {
      require_once($filepath);
    }
  }
}

// if __autoload is active, put it on the spl_autoload stack
if( is_array(spl_autoload_functions()) &&
    in_array('__autoload', spl_autoload_functions()) ) {
   spl_autoload_register('__autoload');
}

// Add the autoloader
spl_autoload_register(ROOT_NAMESPACE . '\autoloader');

// Gotta load the language before everything else
//ctrls\App::load_language();

// Instansiate Ctrls
lib\Ctrl_Factory::all();

// Setup screens
ctrls\App::setup_menus();

register_activation_hook(SLUG, function() { require_once(LIB_PATH . "/activation.php"); });
register_deactivation_hook(SLUG, function() { require_once(LIB_PATH . "/deactivation.php"); });

