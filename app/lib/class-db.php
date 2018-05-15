<?php
namespace wp_revenue_booster\lib;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;

class Db {
  public $prefix;

  public function __construct() {
    global $wpdb;

    $this->prefix = $wpdb->prefix . base\SLUG_KEY . '_';

    // Tables
    $this->customizations = "{$this->prefix}customizations";
    $this->segment_rules  = "{$this->prefix}segment_rules";
  }

  public static function fetch($force = false) {
    static $db;

    if(!isset($db) || $force) {
      $db = new Db();
    }

    return apply_filters(base\SLUG_KEY.'_fetch_db', $db);
  }

  public function upgrade() {
    global $wpdb;

    static $upgrade_already_running;

    if(isset($upgrade_already_running) && true===$upgrade_already_running) {
      return;
    }
    else {
      $upgrade_already_running = true;
    }

    $old_db_version = get_option(base\SLUG_KEY.'_db_version');

    if(base\DB_VERSION > $old_db_version) {
      // Ensure our big queries can run in an upgrade
      $wpdb->query('SET SQL_BIG_SELECTS=1'); //This may be getting set back to 0 when SET MAX_JOIN_SIZE is executed
      $wpdb->query('SET MAX_JOIN_SIZE=18446744073709551615');

      $this->before_upgrade($old_db_version);

      // This was introduced in WordPress 3.5
      // $char_col = $wpdb->get_charset_collate(); //This doesn't work for most non english setups
      $char_col = "";
      $collation = $wpdb->get_row("SHOW FULL COLUMNS FROM {$wpdb->posts} WHERE field = 'post_content'");

      if(isset($collation->Collation)) {
        $charset = explode('_', $collation->Collation);

        if(is_array($charset) && count($charset) > 1) {
          $charset = $charset[0]; //Get the charset from the collation
          $char_col = "DEFAULT CHARACTER SET {$charset} COLLATE {$collation->Collation}";
        }
      }

      //Fine we'll try it your way this time
      if(empty($char_col)) { $char_col = $wpdb->get_charset_collate(); }

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      /* Create/Upgrade Segment_Rules Table */
      $segment_rules = "
        CREATE TABLE {$this->segment_rules} (
          id int(11) NOT NULL auto_increment,
          rule_type varchar(32) default NULL,
          rule_operator varchar(32) default 'is',
          rule_condition varchar(32) default NULL,
          segment_id int(11) default NULL,
          PRIMARY KEY  (id),
          KEY rule_type (rule_type),
          KEY rule_operator (rule_operator),
          KEY rule_condition (rule_condition),
          KEY segment_id (segment_id)
        ) {$char_col};";
 
      dbDelta($segment_rules);

      /* Create/Upgrade Customizations Table */
      $customizations = "
        CREATE TABLE {$this->customizations} (
          id int(11) NOT NULL auto_increment,
          page_uri varchar(64) default NULL,
          selector varchar(64) default NULL,
          content text default NULL,
          status varchar(32) default NULL,
          segment_id int(11) default NULL,
          PRIMARY KEY  (id),
          KEY page_uri (page_uri),
          KEY selector (selector),
          KEY status (status),
          KEY segment_id (segment_id)
        ) {$char_col};";
 
      dbDelta($customizations);

      $this->after_upgrade($old_db_version);

      /***** SAVE DB VERSION *****/
      //Let's only run this query if we're actually updating
      //update_option(base\SLUG_KEY.'_db_version', base\DB_VERSION);
    }
  }

  public function before_upgrade($curr_db_version) {
    // Nothing yet
  }

  public function after_upgrade($curr_db_version) {
    // Nothing yet
  }

  public function create_record($table, $args, $record_created_at=true, $output=false, $record_created_ts=false) {
    global $wpdb;

    $cols = array();
    $vars = array();
    $values = array();

    $i = 0;
    foreach($args as $key => $value) {
      if($key == 'created_at' && $record_created_at && empty($value)) { continue; }

      $cols[$i] = $key;
      if(is_numeric($value) and preg_match('!\.!',$value)) {
        $vars[$i] = '%f';
      }
      else if(is_int($value) or is_numeric($value) or is_bool($value)) {
        $vars[$i] = '%d';
      }
      else {
        $vars[$i] = '%s';
      }

      if(is_bool($value)) {
        $values[$i] = $value ? 1 : 0;
      }
      else {
        $values[$i] = $value;
      }

      $i++;
    }

    if($record_created_at && (!isset($args['created_at']) || empty($args['created_at']))) {
      $cols[$i] = 'created_at';
      $vars[$i] = $wpdb->prepare('%s',Utils::db_now());
      $i++;
    }

    if($record_created_ts) {
      $cols[$i] = 'created_ts';
      $vars[$i] = time();
    }

    if(empty($cols)) {
      return false;
    }

    $cols_str = implode(',',$cols);
    $vars_str = implode(',',$vars);

    $query = "INSERT INTO {$table} ( {$cols_str} ) VALUES ( {$vars_str} )";
    if(empty($values)) {
      $query = esc_sql( $query );
    }
    else {
      $query = $wpdb->prepare( $query, $values );
    }

    if($output)
      echo $query . "\n";

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }

  public function update_record( $table, $id, $args )
  {
    global $wpdb;

    if(empty($args) or empty($id))
      return false;

    $set = '';
    $values = array();
    foreach($args as $key => $value)
    {
      if(empty($set))
        $set .= ' SET';
      else
        $set .= ',';

      $set .= " {$key}=";

      if(is_numeric($value) and preg_match('!\.!',$value))
        $set .= "%f";
      else if(is_int($value) or is_numeric($value) or is_bool($value))
        $set .= "%d";
      else
        $set .= "%s";

      if(is_bool($value))
        $values[] = $value ? 1 : 0;
      else
        $values[] = $value;
    }

    $values[] = $id;
    $query = "UPDATE {$table}{$set} WHERE id=%d";

    if( empty($values) ) {
      $query = esc_sql( $query );
    }
    else {
      $query = $wpdb->prepare( $query, $values );
    }

    if($wpdb->query($query)) {
      return $id;
    }
    else {
      return false;
    }
  }

  public function delete_records($table, $args)
  {
    global $wpdb;

    extract(Db::get_where_clause_and_values( $args ));

    $query = "DELETE FROM {$table}{$where}";

    if( empty($values) ) {
      $query = esc_sql( $query );
    }
    else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->query($query);
  }

  public function get_count($table, $args=array()) {
    global $wpdb;

    extract(Db::get_where_clause_and_values( $args ));

    $query = "SELECT COUNT(*) FROM {$table}{$where}";

    if( empty($values) ) {
      $query = esc_sql( $query );
    }
    else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_var($query);
  }

  public function get_where_clause_and_values( $args ) {
    $args = (array)$args;

    $where = '';
    $values = array();
    foreach($args as $key => $value)
    {
      if(!empty($where))
        $where .= ' AND';
      else
        $where .= ' WHERE';

      $where .= " {$key}=";

      if(is_numeric($value) and preg_match('!\.!',$value))
        $where .= "%f";
      else if(is_int($value) or is_numeric($value) or is_bool($value))
        $where .= "%d";
      else
        $where .= "%s";

      if(is_bool($value))
        $values[] = $value ? 1 : 0;
      else
        $values[] = $value;
    }

    return compact('where','values');
  }

  public function get_one_model($model, $args=array()) {
    $table = $this->get_table_for_model($model);

    $rec = $this->get_one_record($table, $args);

    if(!empty($rec)) {
      $obj = new $model();
      $obj->load_from_array($rec);
      return $obj;
    }

    return $rec;
  }

  public function get_one_record($table, $args=array())
  {
    global $wpdb;

    extract(Db::get_where_clause_and_values( $args ));

    $query = "SELECT * FROM {$table}{$where} LIMIT 1";

    if( empty($values) ) {
      $query = esc_sql( $query );
    }
    else {
      $query = $wpdb->prepare( $query, $values );
    }

    return $wpdb->get_row($query);
  }

  public function get_models($model, $order_by='', $limit='', $args=array()) {
    $table = $this->get_table_for_model($model);
    $recs = $this->get_records($table, $args, $order_by, $limit);

    $models = array();
    foreach($recs as $rec) {
      $obj = new $model();
      $obj->load_from_array($rec);
      $models[] = $obj;
    }

    return $models;
  }

  public function get_records($table, $args=array(), $order_by='', $limit='', $joins=array(), $return_type=OBJECT) {
    global $wpdb;

    extract(Db::get_where_clause_and_values( $args ));
    $join = '';

    if(!empty($order_by)) {
      $order_by = " ORDER BY {$order_by}";
    }

    if(!empty($limit)) {
      $limit = " LIMIT {$limit}";
    }

    if(!empty($joins)) {
      foreach($joins as $join_clause) {
        $join .= " {$join_clause}";
      }
    }

    $query = "SELECT * FROM {$table}{$join}{$where}{$order_by}{$limit}";

    if(empty($values)) {
      $query = esc_sql($query);
    }
    else {
      $query = $wpdb->prepare($query, $values);
    }

    return $wpdb->get_results($query, $return_type);
  }

  /* Built to work with WordPress' built in WP_List_Table class */
  public static function list_table( $cols,
                                     $from,
                                     $joins=array(),
                                     $args=array(),
                                     $order_by='',
                                     $order='',
                                     $paged='',
                                     $search='',
                                     $perpage=10,
                                     $countonly=false ) {
    global $wpdb;

    // Setup selects
    $col_str_array = array();
    foreach( $cols as $col => $code ) {
      $col_str_array[] = "{$code} AS {$col}";
    }

    $col_str = implode(", ",$col_str_array);

    // Setup Joins
    if(!empty($joins)) {
      $join_str = " " . implode( " ", $joins );
    }
    else {
      $join_str = '';
    }

    $args_str = implode(' AND ', $args);

    /* -- Ordering parameters -- */
    //Parameters that are going to be used to order the result
    $order_by = (!empty($order_by) and !empty($order)) ? ( $order_by = ' ORDER BY ' . $order_by . ' ' . $order ) : '';

    //Page Number
    if(empty($paged) or !is_numeric($paged) or $paged<=0 ){ $paged=1; }

    $limit = '';
    //adjust the query to take pagination into account
    if(!empty($paged) and !empty($perpage)) {
      $offset=($paged-1)*$perpage;
      $limit = ' LIMIT '.(int)$offset.','.(int)$perpage;
    }

    // Searching
    $search_str = "";
    $searches = array();
    if(!empty($search)) {
      foreach($cols as $col => $code) {
        $searches[] = "{$code} LIKE '%{$search}%'";
      }

      if(!empty($searches)) {
        $search_str = implode(' OR ', $searches);
      }
    }

    $conditions = "";

    // Pull Searching into where
    if(!empty($args)) {
      if(!empty($searches)) {
        $conditions = " WHERE $args_str AND ({$search_str})";
      }
      else {
        $conditions = " WHERE $args_str";
      }
    }
    else {
      if(!empty($searches)) {
        $conditions = " WHERE {$search_str}";
      }
    }

    $query = "SELECT {$col_str} FROM {$from}{$join_str}{$conditions}{$order_by}{$limit}";
    $total_query = "SELECT COUNT(*) FROM {$from}{$join_str}{$conditions}";

    //Allows us to run the bazillion JOINS we use on the list tables
    $wpdb->query("SET SQL_BIG_SELECTS=1");

    $results = $wpdb->get_results($query);
    $count = $wpdb->get_var($total_query);

    return array( 'results' => $results, 'count' => $count );
  }

  public function get_table_for_model($model) {
    global $wpdb;
    $table = strtolower(lib\Utils::class_basename($model));

    // TODO: We need to get true inflections working here eventually ...
    //       just tacking on an 's' like this is sketchy
    return "{$this->prefix}{$table}s";
  }

  public function table_exists($table) {
    global $wpdb;
    $q = $wpdb->prepare('SHOW TABLES LIKE %s', $table);
    $table_res = $wpdb->get_var($q);
    return ($table_res == $table);
  }

  public function table_empty($table) {
    return ($this->get_count($table) <= 0);
  }

  public function column_exists($table, $column) {
    global $wpdb;
    $q = $wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", $column);
    $res = $wpdb->get_col($q);
    return (count($res) > 0);
  }
}
