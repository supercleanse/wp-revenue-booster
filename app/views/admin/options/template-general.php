<?php if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); } ?>

<div class="grid">
  <div class="col-1-3 grid-pad">
    <div class="content">
      <label for="option-1"><?php _e('Option 1', 'wp-revenue-booster'); ?></label>
        <?php
          wp_revenue_booster\helpers\App::info_tooltip(
            'option-1',
            __('Option 1', 'wp-revenue-booster'),
            __('This is a tooltip. Use it wisely', 'wp-revenue-booster')
          );
      ?>
    </div>
  </div>
  <div class="col-2-3 grid-pad">
    <div class="content">
      <input type="text" class="regular-text" name="option-1" value="" />
    </div>
  </div>
  <div class="col-1-3 grid-pad">
    <div class="content">
      <label for="option-2"><?php _e('Option 2', 'wp-revenue-booster'); ?></label>
    </div>
  </div>
  <div class="col-2-3 grid-pad">
    <div class="content">
      <input type="checkbox" name="option-2" />
    </div>
  </div>
</div>
