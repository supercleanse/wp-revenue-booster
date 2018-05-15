<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-condition-bool col-3-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][condition]">
    <option><?php _e('-- Select --'); ?></option>
    <option value="true"><?php _e('True'); ?></option>
    <option value="false"><?php _e('False'); ?></option>
  </select>
</div>

