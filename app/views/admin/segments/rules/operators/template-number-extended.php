<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-operator col-2-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][operator]">
    <option value="e"><?php echo htmlentities('='); ?></option>
    <option value="ne"><?php echo htmlentities('!='); ?></option>
    <option value="gt"><?php echo htmlentities('>'); ?></option>
    <option value="lt"><?php echo htmlentities('<'); ?></option>
    <option value="gte"><?php echo htmlentities('>='); ?></option>
    <option value="lte"><?php echo htmlentities('<='); ?></option>
    <option value="in"><?php _e('In', 'wp-revenue-booster'); ?></option>
  </select>
</div>

