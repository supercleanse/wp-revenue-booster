<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as models;
use wp_revenue_booster\helpers as helpers;

class Segments extends lib\Base_Cpt_Ctrl {
  public function load_hooks() {
    $this->ctaxes = [];

    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('save_post_' . models\Segment::$cpt, [ $this, 'save_postdata' ]);
  }

  public function register_post_type() {
    $this->cpt = (object)[
      'slug' => models\Segment::$cpt,
      'config' => [
        'labels' => [
          'name' => __('Segments', 'wp-revenue-booster'),
          'singular_name' => __('Segment', 'wp-revenue-booster'),
          'add_new_item' => __('Add New Segment', 'wp-revenue-booster'),
          'edit_item' => __('Edit Segment', 'wp-revenue-booster'),
          'new_item' => __('New Segment', 'wp-revenue-booster'),
          'view_item' => __('View Segment', 'wp-revenue-booster'),
          'search_items' => __('Search Segments', 'wp-revenue-booster'),
          'not_found' => __('No Segments found', 'wp-revenue-booster'),
          'not_found_in_trash' => __('No Segments found in Trash', 'wp-revenue-booster'),
          'parent_item_colon' => __('Parent Segment:', 'wp-revenue-booster')
        ],
        'public' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_rest' => false,
        'show_in_menu' => base\SLUG,
        'has_archive' => false,
        'capability_type' => 'page',
        'hierarchical' => false,
        'register_meta_box_cb' => [$this,'add_meta_boxes'],
        'rewrite' => false,
        'supports' => ['title']
      ]
    ];

    if(!empty($this->ctaxes)) {
      $this->cpt->config['taxonomies'] = $this->ctaxes;
    }

    register_post_type( models\Segment::$cpt, $this->cpt->config );
  }

  public function add_meta_boxes() {
    add_meta_box(
      'wprb-segment-rules',
      __('Rules', 'wp-revenue-booster'),
      [$this, 'meta_box'],
      models\Segment::$cpt,
      'normal'
    );
  }

  public function meta_box($obj) {
    $segment = new models\Segment($obj->ID);
    lib\View::render('admin/segments/rules/meta-box', compact('segment'));
  }

  private function get_templates($views_path, $args) {
    $templates = @glob(base\VIEWS_PATH . "/{$views_path}/template-*.php", GLOB_NOSORT);

    extract($args);

    $tpls = [];
    foreach($templates as $template) {
      $template_name = preg_replace('!template-(.*)\.php!', '$1', basename($template));
      ob_start();
      require($template);
      $tpls[$template_name] = ob_get_clean();
    }

    return $tpls;
  }

  public function get_admin_script_args() {
    global $post_ID;

    $segment = new models\Segment($post_ID);

    $rule_filters = [
      [
        'id' => 'user_type',
        'label' => __('User Type'),
        'type' => 'string',
        'input' => 'select',
        'values' => [
          'logged_in' => __('Logged In'),
          'returning_visitor' => __('Returning Visitor')
        ],
        'operators' => ['equal','not_equal']
      ],
      [
        'id' => 'device',
        'label' => __('Device'),
        'type' => 'string',
        'input' => 'select',
        'values' => [
          'desktop' => __('Desktop'),
          'mobile'  => __('Mobile'),
          'phone'   => __('Phone'),
          'tablet'  => __('Tablet')
        ],
        'operators' => ['equal','not_equal']
      ],
      [
        'id' => 'browser',
        'label' => __('Browser'),
        'type' => 'string',
        'input' => 'select',
        'values' => [
          'silk'     => __('Amazon Silk'),
          'android'  => __('Android'),
          'chrome'   => __('Chrome'),
          'chromium' => __('Chromium'),
          'edge'     => __('Edge'),
          'firefox'  => __('Firefox'),
          'ie'       => __('Internet Explorer'),
          'kindle'   => __('Kindle'),
          'opera'    => __('Opera'),
          'coast'    => __('Opera Coast'),
          'safari'   => __('Safari'),
        ],
        'operators' => ['equal','not_equal']
      ],
      [
        'id' => 'os',
        'label' => __('Operating System'),
        'type' => 'string',
        'input' => 'select',
        'values' => [
          'android' => __('Android'),
          'ios'     => __('iOS'),
          'linux'   => __('Linux'),
          'macosx'  => __('Mac'),
          'win'     => __('Windows')
        ],
        'operators' => ['equal','not_equal']
      ],
      [
        'id' => 'country',
        'label' => __('Country'),
        'type' => 'string',
        'input' => 'select',
        'values' => helpers\App::get_countries(),
        'operators' => ['equal','not_equal']
      ],
      [
        'id' => 'state',
        'label' => __('State'),
        'type' => 'string',
        'input' => 'select',
        'values' => helpers\App::get_states(),
        'operators' => ['equal','not_equal']
      ],
    ];

    return [
      'rules' => $segment->rules,
      'rules_str' => $segment->rules_str,
      'rule_filters' => $rule_filters,
      'submit_button_text' => __('Update', 'wp-revenue-booster')
    ];
  }

  public function enqueue_admin_scripts($hook) {
    global $current_screen;

    if($current_screen->post_type == models\Segment::$cpt) {
      wp_register_style('wprb-query-builder', base\CSS_URL . '/lib/query-builder.css');
      wp_register_style('wprb-query-builder-bs', base\CSS_URL . '/lib/query-builder-bs.css');
      wp_enqueue_style('wprb-admin-segments', base\CSS_URL . '/admin-segments.css', ['wprb-query-builder','wprb-query-builder-bs']);

      wp_register_script('wprb-query-builder', 'https://cdn.jsdelivr.net/npm/jQuery-QueryBuilder/dist/js/query-builder.standalone.min.js');
      wp_enqueue_script('wprb-admin-segments', base\JS_URL . '/admin-segments.js', ['jquery','wprb-query-builder']);
      wp_localize_script('wprb-admin-segments', 'WPRB_Segment', $this->get_admin_script_args());
    }
  }

  public function save_postdata($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return $post_id;
    }

    if(defined('DOING_AJAX')) {
      return;
    }

    if(lib\Utils::is_post_request()) {
      $segment = new models\Segment($post_id);

      if(isset($_POST[$segment->rules_str])) {
        $rules = $_POST[$segment->rules_str];
        $_POST[$segment->rules_str] = json_decode( stripslashes($rules) );
      }
      
      $segment->load_from_post(true);
      $segment->store_meta();
    }
  }
}
