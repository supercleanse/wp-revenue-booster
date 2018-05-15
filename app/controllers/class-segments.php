<?php
namespace wp_revenue_booster\controllers;

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

use wp_revenue_booster as base;
use wp_revenue_booster\lib as lib;
use wp_revenue_booster\controllers as ctrls;
use wp_revenue_booster\models as models;

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

    return [
      'rules' => $segment->rules,
      'tpls' => [
        'row' => lib\View::get_string('admin/segments/rules/row', compact('segment')),
        'type' => lib\View::get_string('admin/segments/rules/type', compact('segment')),
        'operators' => $this->get_templates('admin/segments/rules/operators', compact('segment')),
        'conditions' => $this->get_templates('admin/segments/rules/conditions', compact('segment'))
      ],
      'submit_button_text' => __('Update', 'wp-revenue-booster')
    ];
  }

  public function enqueue_admin_scripts($hook) {
    global $current_screen;

    if($current_screen->post_type == models\Segment::$cpt) {
      wp_enqueue_style('wprb-admin-segments', base\CSS_URL . '/admin-segments.css');
      wp_enqueue_script('wprb-admin-segments', base\JS_URL . '/admin-segments.js');
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
      $segment->load_from_post(true);
      $segment->store_meta();
    }
  }
}
