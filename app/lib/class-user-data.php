<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base,
    wp_revenue_booster\controllers as ctrls,
    wp_revenue_booster\models as model,
    wp_revenue_booster\lib as lib;

/** Utilities for dealing with the lookup and gathering of user data for use in segmentation. */
class User_Data {

  /** This gathers all the user information for this request.
   */
  public static function get_user_request_data() {
    // Only call this once per request
    // urd stands for "User Request Data" :)
    static $urd;

    if(!isset($urd)) {
      $urd = [];

      // User Data
      $urd['user_types'] = [];

      // Logged In
      if(lib\Utils::is_user_logged_in()) {
        $urd['user_types'][] = 'logged_in';
      }

      // Returning Visitor
      // TODO: Should we do this page by page?
      if(isset($_COOKIE['wprb_visitor'])) {
        $urd['user_types'][] = 'returning_visitor';
      }

      if(!isset($_COOKIE['wprb_visitor'])) {
        setcookie('wprb_visitor', 'https://wprevenuebooster.com', time() + lib\Utils::months(1));
      }

      $tech_info = lib\User_Data::get_tech_info();

      // Device
      $urd['devices'] = $tech_info['devices'];

      // Browser
      $urd['browsers'] = $tech_info['browsers'];

      // OS
      $urd['oses'] = $tech_info['oses'];

      // Country
      $urd['countries'] = [lib\User_Data::country_by_ip()];

      // State
      $urd['states'] = [lib\User_Data::state_by_ip()];
    }

    return $urd;
  }

  /** Get browser info from the Caseproof micro-service. */
  public static function get_browser_info() {
    $cookie_name = 'wprb-browser-info';

    if(isset($_COOKIE[$cookie_name])) {
      return json_decode(base64_decode($_COOKIE[$cookie_name]), true);
    }

    $ua = urlencode($_SERVER['HTTP_USER_AGENT']);
    $url = "https://cspf-parse-user-agent.herokuapp.com/?ua={$ua}";
    $res = wp_remote_get($url);

    if(is_wp_error($res)) {
      error_log($res->get_error_message());
      return [];
    }

    if(is_array($res)) {
      $obj = json_decode($res['body'], true);

      setcookie($cookie_name, base64_encode(json_encode($obj['result'])), time() + lib\Utils::months(1));

      return $obj['result']; 
    }

    return (object)[];
  }

  /** Determine the tech info for a given browsecap breakdown */
  public static function get_tech_info() {
    static $tech_info;

    if(isset($tech_info)) { return $tech_info; }

    $info = self::get_browser_info();

    if(empty($info)) { return []; }

    // Devices
    $devices=[];

    if($info['ismobiledevice']===true ||
       $info['ismobiledevice']==='true') {
      $devices[]='mobile';
    }

    if($info['istablet']===true ||
       $info['istablet']==='true') {
      $devices[]='tablet';
    }
    if(($info['istablet']===false ||
        $info['istablet']==='false') &&
       ($info['ismobiledevice']===true ||
        $info['ismobiledevice']==='true')) {
      $devices[]='phone';
    }

    if(($info['istablet']===false ||
        $info['istablet']==='false') &&
       ($info['ismobiledevice']===false ||
        $info['ismobiledevice']==='false')) {
      $devices[]='desktop';
    }

    // Operating Systems
    $oses = [];
    $info_os = strtolower($info['platform']);
    $windows_oses = array( 'win10', 'win32', 'win7', 'win8', 'win8.1', 'winnt', 'winvista' );
    $other_oses = array('android', 'linux', 'ios', 'macosx');

    // map macos to macosx for now
    $info_os = (($info_os=='macos') ? 'macosx' : $info_os);

    if(in_array($info_os, $other_oses)) {
      $oses[] = $info_os;
    }
    else if(in_array($info_os, $windows_oses)) {
      $oses[] = 'win';
    }

    $browsers = [];
    $info_browser = strtolower($info['browser']);
    $android_browsers = array('android', 'android webview');
    $ie_browsers = array('fake ie', 'ie');
    $other_browsers = array('chrome', 'chromium', 'coast', 'edge', 'firefox', 'opera', 'safari', 'silk', 'kindle');

    if(in_array($info_browser, $other_browsers)) {
      $browsers[] = $info_browser;
    }
    else if(in_array($info_browser, $ie_browsers)) {
      $browsers[] = 'ie';
    }
    else if(in_array($info_browser, $android_browsers)) {
      $browsers[] = 'android';
    }

    $tech_info = compact('devices','oses','browsers');

    return $tech_info;
  }

  public static function locate_by_ip($ip=null, $source='caseproof') {
    static $loc;

    // This only needs to happen once per request
    if(isset($loc)) { return $loc; }

    $ip = (is_null($ip)?lib\Utils::get_current_client_ip():$ip);

    if(!lib\Utils::is_ip($ip)) { return false; }

    if($source=='caseproof') {
      $url    = "https://cspf-locate.herokuapp.com?ip={$ip}";
      $cindex = 'country_code';
      $rindex = 'region_code';
    }
    else { // geoplugin
      $url    = "http://www.geoplugin.net/json.gp?ip={$ip}";
      $cindex = 'geoplugin_countryCode';
      $rindex = 'geoplugin_regionCode';
    }

    $cookie_name = 'wprb-loc-' . md5($ip.$source);
    if(!isset($_COOKIE[$cookie_name])) {
      $res = wp_remote_get($url);
      if(is_wp_error($res)) { error_log($res->get_error_message); return []; }
      $obj = json_decode($res['body']);

      setcookie($cookie_name, base64_encode($res['body']), time() + lib\Utils::months(1));
    }
    else {
      $obj = json_decode(base64_decode($_COOKIE[$cookie_name]));
    }

    $country = (isset($obj->{$cindex})?$obj->{$cindex}:'');
    $state = (isset($obj->{$rindex})?$obj->{$rindex}:'');

    return (object)compact('country','state');
  }

  public static function country_by_ip($ip=null, $source='caseproof') {
    return (($loc = self::locate_by_ip()) ? $loc->country : '' );
  }

  public static function state_by_ip($ip=null, $source='caseproof') {
    return (($loc = self::locate_by_ip()) ? "{$loc->country}_{$loc->state}" : '' );
  }
}
