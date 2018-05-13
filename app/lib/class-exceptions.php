<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;

class Exception extends \Exception { }

class Log_Exception extends Exception {
  public function __construct($message, $code = 0, Exception $previous = null) {
    $classname = get_class($this);
    Utils::error_log("{$classname}: {$message}");
    parent::__construct($message, $code, $previous);
  }
}

class Create_Exception extends Exception { }
class Update_Exception extends Exception { }
class Delete_Exception extends Exception { }

class Invalid_Email_Exception extends Exception { }
class Invalid_Method_Exception extends Exception { }
class Invalid_Variable_Exception extends Exception { }

