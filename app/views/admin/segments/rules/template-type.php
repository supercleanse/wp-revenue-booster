<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-type col-2-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][type]">
    <option><?php _e('-- Type --', 'wp-revenue-booster'); ?></option>
    <option value="logged-in"><?php _e('Logged-In', 'wp-revenue-booster'); ?></option>
    <option value="browser"><?php _e('Browser', 'wp-revenue-booster'); ?></option>
    <option value="device"><?php _e('Device', 'wp-revenue-booster'); ?></option>
    <option value="os"><?php _e('Operating System', 'wp-revenue-booster'); ?></option>
    <option value="state"><?php _e('State', 'wp-revenue-booster'); ?></option>
    <option value="country"><?php _e('Country', 'wp-revenue-booster'); ?></option>
  </select>
</div>

