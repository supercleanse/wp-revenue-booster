<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<div class="wprb-segment-rule-condition wprb-segment-rule-country col-3-12">
  <select name="<?php echo $segment->rules_str; ?>[{{id}}][condition]">
    <option><?php _e('-- Select --'); ?></option>

    <?php
      $countries = wp_revenue_booster\helpers\App::get_countries();

      foreach($countries as $country_code => $country_name):
        ?>
          <option value="<?php echo $country_code; ?>"><?php echo $country_name; ?></option>
        <?php
      endforeach;
    ?>

  </select>
</div>

