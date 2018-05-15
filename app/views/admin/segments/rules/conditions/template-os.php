<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-os col-3-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][condition]">
    <option><?php _e('-- Select --'); ?></option>
    <option value="android"><?php _e('Android'); ?></option>
    <option value="ios"><?php _e('iOS'); ?></option>
    <option value="linux"><?php _e('Linux'); ?></option>
    <option value="macosx"><?php _e('Mac'); ?></option>
    <option value="win"><?php _e('Windows'); ?></option>
  </select>
</div>

