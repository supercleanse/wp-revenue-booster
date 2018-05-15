<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-condition-currency col-3-12">
  <input
    type="text"
    name="<?php echo $segment->rules_str; ?>[{{id}}][condition]"
    class="wprb-responsive-text"
    value="{{match_val}}" />
</div>

