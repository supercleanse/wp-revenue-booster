<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-condition-state col-3-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][condition]">
    <option><?php _e('-- Select --'); ?></option>

    <?php
      $states = wp_revenue_booster\helpers\App::get_states();

      foreach($states as $state_code => $state_name):
        ?>
          <option value="<?php echo $state_code; ?>"><?php echo $state_name; ?></option>
        <?php
      endforeach;
    ?>

  </select>
</div>

