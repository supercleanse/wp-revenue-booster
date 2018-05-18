<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as model;

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

  public static function php_get_browsercap_ini() {
    static $browsecap_ini;
 
    if(!isset($browsecap_ini)) {
      $browsecap_ini = parse_ini_file( base\LIB_PATH . '/data/php_browscap.ini', true, INI_SCANNER_RAW );
    }
 
    return $browsecap_ini;
  }
 
  /** Needed because we don't know if the target uesr will have a browsercap file installed
   *  on their server ... particularly in a shared hosting environment this is difficult
   */
  public static function php_get_browser($agent = NULL) {
    $agent=$agent?$agent:$_SERVER['HTTP_USER_AGENT'];
    $yu=array();
    $q_s=array("#\.#","#\*#","#\?#");
    $q_r=array("\.",".*",".?");
    $brows = self::php_get_browsercap_ini();
 
    if(empty($agent)) { return array(); }
 
    //Do a bit of caching here
    static $hu;
    if(!isset($hu)) {
      $hu = array();
    }
    else {
      return $hu;
    }
 
    if(!empty($brows) and $brows and is_array($brows)) {
      foreach($brows as $k=>$t) {
        if(fnmatch($k,$agent)) {
          $yu['browser_name_pattern']=$k;
          $pat=preg_replace($q_s,$q_r,$k);
          $yu['browser_name_regex']=strtolower("^$pat$");
          foreach($brows as $g=>$r) {
            if($t['Parent']==$g) {
              foreach($brows as $a=>$b) {
                if(isset($r['Parent']) && $r['Parent']==$a) {
                  $yu=array_merge($yu,$b,$r,$t);
                  foreach($yu as $d=>$z) {
                    $l=strtolower($d);
                    $hu[$l]=$z;
                  }
                }
              }
            }
          }

          break;
        }
      }
    }

    return $hu;
  }

  public static function get_browser_info() {
    $ua = urlencode($_SERVER['HTTP_USER_AGENT']);
    $url = "https://cspf-parse-user-agent.herokuapp.com/?ua={$ua}";
    $res = wp_remote_get($url);

    if(is_wp_error($res)) {
      error_log($res->get_error_message());
      return [];
    }

    if(is_array($res)) {
      $body = $res['body'];
      $obj = json_decode($body);
      return $obj; 
    }

    return (object)[];
  }

  /** Determine the tech info for a given browsecap breakdown */
  public static function get_tech_info() {
    static $tech_info;

    $info = self::get_browser_info();

    if(empty($info)) { $tech_info = []; }

    if(isset($tech_info)) { return $tech_info; }

    // TODO: Store this in a cookie ... this won't change for the given browser used

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

    $lockey = 'wprb_locate_by_ip_' . md5($ip.$source);
    $loc = get_transient($lockey);

    if(false===$loc) {
      if($source=='caseproof') {
        $url    = "https://cspf-locate.herokuapp.com?ip={$ip}";
        $cindex = 'country_code';
      }
      elseif($source=='freegeoip') {
        $url    = "https://freegeoip.net/json/{$ip}";
        $cindex = 'country_code';
      }
      else { // geoplugin
        $url    = "http://www.geoplugin.net/json.gp?ip={$ip}";
        $cindex = 'geoplugin_countryCode';
      }

      $res = wp_remote_get($url);
      if(is_wp_error($res)) { error_log($res->get_error_message); return []; }
      $obj = json_decode($res['body']);
      error_log('Location Object: ' . print_r($obj, true));
      $country = (isset($obj->{$cindex})?$obj->{$cindex}:'');
      $state = 'UT'; // How can we get this from the location object?

      $loc = (object)compact('country');
      set_transient($lockey,$loc,DAY_IN_SECONDS);
    }

    return $loc;
  }

  public static function country_by_ip($ip=null, $source='caseproof') {
    return (($loc = self::locate_by_ip()) ? $loc->country : '' );
  }

  public static function state_by_ip($ip=null, $source='caseproof') {
    return (($loc = self::locate_by_ip()) ? "{$loc->country}_{$loc->state}" : '' );
  }
}
