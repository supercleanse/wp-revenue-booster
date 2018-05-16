<?php if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');} ?>
<div class="wprb-customization-popup">
  <h2><?php _e('Edit Dynamic Text'); ?></h2>

  <div class="wprb-current">
    <label><?php _e('Editing Text for:'); ?></label>
    <pre>{{selector}}</pre>
  </div>

  <div class="wprb-current">
    <label><?php _e('Current Text:'); ?></label>
    <div class="wprb-current-text">{{current_text}}</div>
  </div>

  <div class="wprb-current">
    <label><?php _e('Dynamic Text Customizations:'); ?></label>
    <form class="wprb-customizations-form" data-selector="{{selector}}" data-page-uri="<?php echo $page_uri; ?>">
      <div class="wprb-customizations"></div>

      <div class="wprb-add-remove">
        <span class="wprb-add-customizations" title="<?php _e('Add Dynamic Text Customization'); ?>"><a href="">[+]</a></span>
      </div>

      <input type="submit" value="<?php _e('Save'); ?>" />
    </form>
  </div>
</div>

