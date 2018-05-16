<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>
<div class="wprb-customization-row">
  <a href="" class="wprb-remove-customization" title="<?php _e('Remove Customization'); ?>">[x]</a>
  <label><?php _e('Customization #{{index}}'); ?></label>
  <input type="hidden" name="cust[{{index}}][id]" value="{{id}}" />
  <div class="wprb-customization-field">
    <label><?php _e('Segment'); ?></label>
    <select name="cust[{{index}}][segment_id]" class="wprb-customization-segment">
      <?php foreach($segments as $segment): ?>
        <option value="<?php echo $segment->ID; ?>"><?php echo $segment->post_title; ?></option>
      <?php endforeach; ?>
    </select>
    &nbsp;&nbsp;
    <a href="<?php echo admin_url('post-new.php?post_type=mprb-segment'); ?>"><?php _e('[Create New Segment]'); ?></a>
  </div>
  <div class="wprb-customization-field">
    <label><?php _e('Content'); ?></label>
    <textarea name="cust[{{index}}][content]" class="wprb-customization-content">{{content}}</textarea>
  </div>
  <div class="wprb-customization-field">
  </div>
</div>

