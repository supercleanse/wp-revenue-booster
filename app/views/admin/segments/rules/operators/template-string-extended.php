<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-operator col-2-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][operator]">
    <option value="is"><?php _e('Is', 'wp-revenue-booster'); ?></option>
    <option value="isnot"><?php _e('Is Not', 'wp-revenue-booster'); ?></option>
    <option value="contains"><?php _e('Contains', 'wp-revenue-booster'); ?></option>
    <option value="doesnotcontain"><?php _e('Does Not Contain', 'wp-revenue-booster'); ?></option>
    <option value="regex"><?php _e('Matches', 'wp-revenue-booster'); ?></option>
    <option value="in"><?php _e('In', 'wp-revenue-booster'); ?></option>
  </select>
</div>

