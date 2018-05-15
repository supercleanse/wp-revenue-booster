<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-device col-3-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][condition]">
    <option><?php _e('-- Select --'); ?></option>
    <option value="desktop"><?php _e('Desktop'); ?></option>
    <option value="mobile"><?php _e('Mobile'); ?></option>
    <option value="phone"><?php _e('Phone'); ?></option>
    <option value="tablet"><?php _e('Tablet'); ?></option>
  </select>
</div>

