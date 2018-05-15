/* Admin Segments JS */
jQuery(document).ready(function($) {
  $('#publishing-action input#publish').val(WPRB_Segment.submit_button_text);

// Query Builder Sample:
// var rules_basic = {
//   condition: 'AND',
//   rules: [{
//     id: 'price',
//     operator: 'less',
//     value: 10.25
//   }, {
//     condition: 'OR',
//     rules: [{
//       id: 'category',
//       operator: 'equal',
//       value: 2
//     }, {
//       id: 'category',
//       operator: 'equal',
//       value: 1
//     }]
//   }]
// };

  var query_builder_options = {
    allow_groups: false,

    filters: WPRB_Segment.rule_filters,

    icons: {
      add_group: 'dashicons dashicons-plus-alt',
      add_rule: 'dashicons dashicons-plus-alt',
      remove_group: 'dashicons dashicons-dismiss',
      remove_rule: 'dashicons dashicons-dismiss',
      error: 'dashicons dashicons-warning'
    }
  };

  $('#wprb-segment-rules').queryBuilder(query_builder_options);

});

