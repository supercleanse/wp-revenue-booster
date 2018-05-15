/* Admin Segments JS */
jQuery(document).ready(function($) {
  $('#publishing-action input#publish').val(WPRB_Segment.submit_button_text);

  $('#wprb-segment-rules').queryBuilder({
    allow_groups: false,

    filters: WPRB_Segment.rule_filters,

    icons: {
      add_group: 'dashicons dashicons-plus-alt',
      add_rule: 'dashicons dashicons-plus-alt',
      remove_group: 'dashicons dashicons-dismiss',
      remove_rule: 'dashicons dashicons-dismiss',
      error: 'dashicons dashicons-warning'
    }
  });

  if(Object.keys(WPRB_Segment.rules).length > 0) {
    $('#wprb-segment-rules').queryBuilder('setRules', WPRB_Segment.rules);
  }

  $('form#post').submit( function(e) {
    //e.preventDefault();
    var rules = $('#wprb-segment-rules').queryBuilder('getRules');

    if (!$.isEmptyObject(rules)) {
      var textarea = '<textarea name="' + WPRB_Segment.rules_str + '" style="display: none">' + JSON.stringify(rules) + '</textarea>';
      $(this).append(textarea);
    }
  });

});

