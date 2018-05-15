<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>

<table class="form-table">
<tbody>
  <tr valign="top">
    <th scope="row">
      <label for="<?php echo $segment->rule_match_type_str; ?>"><span><?php _e('Type:', 'wp-revenue-booster'); ?></span> </label>
      <?php
        wp_revenue_booster\helpers\App::info_tooltip(
          'wprb-segment-rule-type',
          __('Match Type', 'wp-revenue-booster'),
          __('This determines whether to match <b>all</b> conditions or <b>any</b> of them.', 'wp-revenue-booster')
        );
      ?>
    </th>
    <td>
      <select id="wprb-segment-rule-type" name="<?php echo $segment->rule_match_type_str; ?>">
        <option value="all" <?php selected('all',$segment->rule_match_type); ?>><?php _e('All', 'wp-revenue-booster'); ?></option>
        <option value="any" <?php selected('any',$segment->rule_match_type); ?>><?php _e('Any', 'wp-revenue-booster'); ?></option>
      </select>
    </td>
  </tr>
</tbody>
</table>

<div id="wprb-segment-rules">
</div>
<div>&nbsp;</div>
<div>
  <a href="" id="wprb-add-segment-rule-row"><span class="dashicons dashicons-plus"></span></a>
  <a href="" id="wprb-remove-segment-rule-row" class="wprb_hidden"><span class="dashicons dashicons-minus"></span></a>
</div>
