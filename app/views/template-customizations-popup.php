<div class="wprb-customization-popup">
  <h2><?php _e('Customize Field'); ?></h2>

  <div class="wprb-current">
    <label><?php _e('Current Text:'); ?></label>
    <div class="wprb-current-text">{{wprb-current-text}}</div>
  </div>

  <div class="wprb-customizations">
    <form class="wprb-customizations-form">
      <div class="wprb-segment">
        <span><?php _e('When Country'); ?></span>
        <select name="wprb-segment-country-operator">
          <option><?php _e('is'); ?></option>
          <option><?php _e('is not'); ?></option>
        </select>
        <input type="text" name="wprb-segment-country-text" placeholder="<?php _e('United States'); ?>" />
      </div>

      <input type="submit" name="wprb-customiztions-submit" value="<?php _e('Save'); ?>" />
    </form>
  </div>
</div>

