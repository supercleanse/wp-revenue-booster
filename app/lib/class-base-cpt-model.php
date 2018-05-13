<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;

/** Specific base class for CPT Style models */
abstract class Base_Cpt_Model extends Base_Builtin_Model {
  //All inheriting classes should set -- public static $cpt (custom post type)
  public static $cpt = '';

  /** This should only be used if the model is using a custom post type **/
  protected function initialize_new_cpt() {
    $class = get_class($this);

    if(!isset($this->attrs) or !is_array($this->attrs)) {
      $this->attrs = array();
    }

    $cpt = Utils::get_property($class, 'cpt');

    $this->attrs = array_merge(
      array(
       'ID'            => array( 'default' => null,      'type' => 'integer' ),
       'post_content'  => array( 'default' => '',        'type' => 'string' ),
       'post_title'    => array( 'default' => null,      'type' => 'string' ),
       'post_excerpt'  => array( 'default' => '',        'type' => 'string' ),
       'post_name'     => array( 'default' => null,      'type' => 'string' ),
       'post_date'     => array( 'default' => null,      'type' => 'datetime' ),
       'post_status'   => array( 'default' => 'publish', 'type' => 'string' ),
       'post_parent'   => array( 'default' => 0,         'type' => 'integer' ),
       'menu_order'    => array( 'default' => 0,         'type' => 'integer' ),
       'post_type'     => array( 'default' => $cpt,      'type' => 'string' ),
      ),
      $this->attrs
    );

    $this->rec = $this->get_defaults_from_attrs($this->attrs);

    return $this->rec;
  }

  /** Requires defaults to be set */
  protected function load_cpt($id, $cpt, $attrs) {
    $this->attrs = $this->meta_attrs = $attrs;

    // Short Circuit if id is null/empty/non-numeric
    if(empty($id) || !is_numeric($id)) {
      return $this->initialize_new_cpt();
    }

    $post = (array)get_post($id);

    if( null === $post || (isset($post['post_type']) && $post['post_type'] != $cpt) ) {
      $this->initialize_new_cpt();
    }
    else {
      $this->rec = (object)$post;
      $this->load_meta($id);
    }
  }

  /** Requires defaults to be set */
  protected function load_meta($id) {
    $metas = get_post_custom($id);

    $rec = array();

    // Unserialize and set appropriately
    foreach( $this->attrs as $attr => $config ) {
      $meta_key = $this->get_attr_key($attr);
      if(isset($metas[$meta_key])) {
        if(count($metas[$meta_key]) > 1) {
          $rec[$attr] = array();
          foreach($metas[$meta_key] as $sub_key => $sub_val) {
            $rec[$attr][$sub_key] = maybe_unserialize($sub_val);
          }
        }
        else {
          $meta_val = $metas[$meta_key][0];
          if($meta_val==='' && strpos($config['type'],'bool')===0) {
            $rec[$attr] = false;
          }
          else {
            $rec[$attr] = maybe_unserialize($meta_val);
          }
        }
      }
    }

    $defaults = (array)$this->get_defaults_from_attrs($this->attrs);
    $this->rec = (object)array_merge((array)$this->rec,$defaults,$rec);
  }

  public function store($validate = true) {
    if($validate) {
      try {
        $this->validate();
      }
      catch(Exception $e) {
        return new WP_Error(get_class($e), $e->getMessage());
      }
    }

    $store_action = (isset($this->ID) && !is_null($this->ID)) ? 'update' : 'create';

    if($store_action=='update') {
      $id = wp_update_post((array)$this->rec);
    }
    else {
      $id = wp_insert_post((array)$this->rec);
    }

    if(empty($id) || is_wp_error($id)) {
      return $id;
    }
    else {
      $this->ID = $id;
    }

    $this->store_meta();

    $cpt = Utils::get_static_property(get_class($this), 'cpt');
    do_action(base\SLUG_KEY."_cpt_{$store_action}-{$cpt}", $this);

    return $id;
  }

  public function store_meta() {
    $attrs = (array)$this->get_values();
    $cpt = Utils::get_static_property(get_class($this), 'cpt');

    foreach($attrs as $attr => $attr_value) {
      $attr_key = $this->get_attr_key($attr);
      $old_attr_value = maybe_unserialize(get_post_meta($this->ID, $attr_key, true));

      do_action(
        base\SLUG_KEY."_cpt_store_meta-{$cpt}-{$attr}",
        $this->cast_attr($attr,$attr_value),
        $this->cast_attr($attr,$old_attr_value),
        $this,
        $attr_key
      );

      update_post_meta($this->ID, $attr_key, $this->cast_attr($attr,$attr_value));
    }
  }

  public function destroy() {
    $res = wp_delete_post($this->ID, true);

    if(false===$res) {
      throw new Create_Exception(sprintf(__( 'This was unable to be deleted.', 'wp-revenue-booster')));
    }

    return $res;
  }

  //Should probabaly add a delim char check to add before the args
  //similar to how I did it in Options
  public function url($args = '') {
    $link = Utils::get_permalink($this->ID);
    return apply_filters(base\SLUG_KEY.'_cpt_model_url', "{$link}{$args}", $this);
  }

  protected static function get_one_by_class($class, $args) {
    if(!is_numeric($args) && !is_array($args) && !is_object($args)) {
      return false;
    }

    // Get the sub class ... only works in PHP 5.3 or higher
    //$class = get_called_class();

    if(is_numeric($args)) {
      $args = array('ID' => $args);
    }

    $data = self::get_all_data(
      $class,
      ARRAY_A,  // $type
      'ID',    // $orderby
      'ASC',   // $order
      1,       // $limit
      0,       // $offset
      array(), // $selects
      array(), // $joins
      $args    // $wheres
    );

    if(!empty($data) && is_array($data) &&
       !empty($data[0]) && is_array($data[0])) {
      $obj = new $class();
      $obj->load_from_array((array)$data[0]);
      return $obj;
    }

    return false;
  }

  protected static function get_all_by_class($class,$order_by='',$limit='',$args=array()) {
    // Get the sub class ... only works in PHP 5.3 or higher
    //$class = get_called_class();

    if(is_numeric($args)) {
      $args = array('ID' => $args);
    }

    $order = 'ASC';

    if(!empty($order_by)) {
      $orders=explode(' ', $order_by);
      if(isset($orders[0])) {
        $order_by = $orders[0];
      }
      if(isset($orders[1])) {
        $order = $orders[1];
      }
    }

    if(empty($order_by)) {
      $order_by = 'ID';
    }

    $data = self::get_all_data(
      $class,
      ARRAY_A,    // $type
      $order_by, // $orderby
      $order,    // $order
      $limit,    // $limit
      0,         // $offset
      array(),   // $selects
      array(),   // $joins
      $args      // $wheres
    );

    $objs = false;
    if(!empty($data) && is_array($data)) {
      $objs = array();
      foreach($data as $row) {
        $obj = new $class();
        $obj->load_from_array((array)$row);
        $objs[] = $obj;
      }
    }

    return $objs;
  }

  protected static function get_count_by_class($class,$args=array()) {
    global $wpdb;
    $db = Db::fetch();

    // Get the sub class
    //$class = get_called_class();

    $r = new ReflectionClass($class);
    $cpt = $r->getStaticPropertyValue('cpt');

    return $db->get_count($wpdb->posts,array('post_type'=>$cpt));
  }

  private static function get_all_data( $class, // get_class relies on $this so we have to pass the name in
                                        $type=ARRAY_A,
                                        $orderby='ID',
                                        $order='ASC',
                                        $limit=100,
                                        $offset=0,
                                        $selects=array(),
                                        $joins=array(),
                                        $wheres=array() ) {
    global $wpdb;

    $rc = new ReflectionClass($class);
    $obj = $rc->newInstance();

    $attrs = $obj->get_meta_attrs();
    $meta_keys = array_keys($attrs);

    array_unshift(
      $wheres,
      $wpdb->prepare( 'p.post_type=%s', $rc->getStaticPropertyValue('cpt') ),
      $wpdb->prepare( 'p.post_status=%s', 'publish' )
    );

    if(empty($selects)) {
      $selects = array('p.*');
      $fill_selects = true;
    }
    else {
      $fill_selects = false;
    }

    foreach($meta_keys as $meta_key) {
      $meta_key_getter = "{$meta_key}_str";
      $meta_key_str = $obj->{$meta_key_getter};

      if($fill_selects) {
        $selects[] = "pm_{$meta_key}.meta_value AS {$meta_key}";
      }

      $joins[] = $wpdb->prepare( "
          LEFT JOIN {$wpdb->postmeta} AS pm_{$meta_key}
            ON pm_{$meta_key}.post_id=p.ID
           AND pm_{$meta_key}.meta_key=%s
        ",
        $meta_key_str
      );
    }

    $selects_str = join(', ', $selects);
    $joins_str = join(' ', $joins);
    $wheres_str = join( ' AND ', $wheres );

    if(empty($limit)) {
      $limit_str = '';
    }
    else {
      $limit_str = "
        LIMIT {$limit}
      ";
    }

    if(empty($offset)) {
      $offset_str = '';
    }
    else {
      $offset_str = "
        OFFSET {$limit}
      ";
    }

    $q = "
      SELECT {$selects_str}
        FROM {$wpdb->posts} AS p {$joins_str}
       WHERE {$wheres_str}
       ORDER BY {$orderby} {$order}
       {$limit_str}
       {$offset_str}
    ";

    $res = $wpdb->get_results($q,$type);

    // two layer maybe_unserialize
    for( $i=0; $i<count($res); $i++ ) {
      foreach( $res[$i] as $k => $val ) {
        $res[$i][$k] = maybe_unserialize($val);
      }
    }

    return $res;
  }
}

