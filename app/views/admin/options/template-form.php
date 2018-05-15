<?php if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); } ?>
<div class="wrap">
  <h2><?php _e('WP Revenue Booster Options'); ?></h2>

  <div>&nbsp;</div>

  <div>
    <div class="description"><?php _e('Select text to customize using the WP Tool Bar on the front end of your website while logged in. Just click the button labeled "Add Dynamic Text":'); ?></div>
    <div>&nbsp;</div>
    <a href="<?php echo home_url(); ?>" class="button button-primary"><?php _e('Visit Site'); ?></a>
  </div>

  <div>&nbsp;</div>

  <div>
    <div class="description"><?php _e('Hopefully everything\'s going swimmingly for you with WP Revenue Booster ... but just in case you can get help here:'); ?></div>
    <div>&nbsp;</div>
    <a href="https://wprevenuebooster.com/help" class="button button-primary"><?php _e('Get Help'); ?></a>
  </div>

<?php /*
  TODO: This is here and ready to rock once we need some options

  <div>&nbsp;</div>

  <form name="wprb-options-form" id="wprb-options" method="post" action="<?php echo admin_url('/admin.php?page=wprb-options'); ?>">
    <?php wp_nonce_field('update-options'); ?>
    <table class="settings-table">
      <tr>
        <td class="settings-table-nav">
          <ul class="sidebar-nav">
            <li><a data-id="general"><?php _e('General', 'wp-revenue-booster'); ?></a></li>
          </ul>
        </td>
        <td class="settings-table-pages">
          <div class="page" id="general">
            <div class="page-title"><?php _e('General Options', 'wp-revenue-booster'); ?></div>
            <?php do_action('wprb_admin_general_options'); ?>
          </div>
        </td>
      </tr>
    </table>
  </form>

*/ ?>

</div>

