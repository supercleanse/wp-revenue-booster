<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-operator col-2-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][operator]">
    <option value="e"><?php echo htmlentities('='); ?></option>
    <option value="ne"><?php echo htmlentities('!='); ?></option>
  </select>
</div>

