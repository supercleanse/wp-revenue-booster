/* Admin Segments JS */
jQuery(document).ready(function($) {
  $('#publishing-action input#publish').val(WPRB_Segment.submit_button_text);

  WPRB_Segment.get_tpl = function(tpl_name, match_index, match_val) {
    if(match_index == null) { match_index = ''; }
    if(match_val == null) { match_val = ''; }

    var tpl = WPRB_Segment.tpls[tpl_name];

    tpl = tpl.replace(/\{\{index\}\}/g,match_index);
    tpl = tpl.replace(/\{\{val\}\}/g,match_val);

    return tpl;
  };

  WPRB_Segment.get_match_row_index = function(row) {
    return row.prevAll().length;
  };

  WPRB_Segment.get_match_row_count = function() {
    return $("#ea-override-matches").children().length;
  };

  WPRB_Segment.get_commission_row_index = function(row) {
    return row.prevAll().length;
  };

  WPRB_Segment.get_commission_row_count = function() {
    return $("#ea-override-commissions").children().length;
  };

  WPRB_Segment.append_field_tpl = function(row, match_type, row_index, field) {
    if(field == null) { field = ''; }

    if(match_type == 'transaction') {
      row.append(WPRB_Segment.get_tpl('match_field_transaction',row_index,field));
      if(field != '') { row.find('.ea-override-match-field select').val(field); }
    }
    else if(match_type == 'affiliate') {
      row.append(WPRB_Segment.get_tpl('match_field_affiliate',row_index,field));
      if(field != '') { row.find('.ea-override-match-field select').val(field); }
    }
  };

  WPRB_Segment.append_operator_and_condition_tpl = function(row, match_type, match_field, row_index, operator, condition) {
    if(operator == null) { operator = ''; }
    if(condition == null) { condition = ''; }

    if(match_type == 'affiliate') {
      if(match_field == 'username') {
        row.append(WPRB_Segment.get_tpl('match_operator_string_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_username',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
        wafp_setup_autocomplete(); // ensure the username autocomplete is working
      }
      else if(match_field == 'referrals') {
        row.append(WPRB_Segment.get_tpl('match_operator_number_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'sales') {
        row.append(WPRB_Segment.get_tpl('match_operator_number_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'salesamount') {
        row.append(WPRB_Segment.get_tpl('match_operator_number_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_currency',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
    }
    else if(match_type == 'transaction') {
      if(match_field == 'source') {
        row.append(WPRB_Segment.get_tpl('match_operator_string_extended',row_index));
        row.append(WPRB_Segment.get_tpl('match_condition_integration',row_index));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
        if(condition != '') { row.find('.ea-override-match-condition select').val(condition); }
      }
      else if(match_field == 'coupon') {
        row.append(WPRB_Segment.get_tpl('match_operator_string_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'item_id') {
        row.append(WPRB_Segment.get_tpl('match_operator_string_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'item_name') {
        row.append(WPRB_Segment.get_tpl('match_operator_string_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'amount') {
        row.append(WPRB_Segment.get_tpl('match_operator_number_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_currency',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
      else if(match_field == 'paynum') {
        row.append(WPRB_Segment.get_tpl('match_operator_number_extended',row_index,operator));
        row.append(WPRB_Segment.get_tpl('match_condition_text',row_index,condition));
        if(operator != '') { row.find('.ea-override-match-operator select').val(operator); }
      }
    }
  };

  WPRB_Segment.append_commission_row = function(data) {
    if(data==null) { data = '0.00'; }

    var commission_row_template = WPRB_Segment.commission_row_tpl;
    var commission_row_count = WPRB_Segment.get_commission_row_count();
    commission_row_template = commission_row_template.replace(/\{\{ea_commission_index\}\}/g,commission_row_count);
    commission_row_template = commission_row_template.replace(/\{\{ea_commission_index_display\}\}/g,commission_row_count+1);
    commission_row_template = commission_row_template.replace(/\{\{ea_commission_val\}\}/g,data);

    $('#ea-override-commissions').append(commission_row_template);
    $('#esaf-remove-override-commission-row').show();
  };

  WPRB_Segment.append_match_row = function(data) {
    var match_row_count = WPRB_Segment.get_match_row_count();

    if(data==null) { data = {}; }

    var match_row_template = WPRB_Segment.match_row_tpl;
    match_row_template = match_row_template.replace(/\{\{ea_match_index\}\}/g,match_row_count+1);

    $('#ea-override-matches').append(match_row_template);
    $('#esaf-remove-override-match-row').show();

    var row = $('#ea-override-matches .ea-override-match-row:last-child');
    var row_index = WPRB_Segment.get_match_row_index(row);

    row.append(WPRB_Segment.get_tpl('match_type', row_index));
    if(data['type'] != undefined) {
      row.find('.ea-override-match-type select').val(data['type']);

      if(data['field'] != undefined) {
        WPRB_Segment.append_field_tpl(row, data['type'], row_index, data['field']);

        if(data['operator'] != undefined && data['condition'] != undefined) {
          WPRB_Segment.append_operator_and_condition_tpl(
            row,
            data['type'],
            data['field'],
            row_index,
            data['operator'],
            data['condition']
          );
        }
      }
    }
  };

  WPRB_Segment.render_matches = function() {
    if(this.matches.length > 0) {
      $(this.matches).each(function(index,value) {
        WPRB_Segment.append_match_row(value);
      });
    }
    else {
      WPRB_Segment.append_match_row();
    }
  };

  WPRB_Segment.render_matches();

  WPRB_Segment.render_commissions = function() {
    if(this.commissions.length > 0) {
      $(this.commissions).each(function(index,value) {
        WPRB_Segment.append_commission_row(value);
      });
    }
    else {
      // Add a default row
      WPRB_Segment.append_commission_row();
    }
  };

  WPRB_Segment.render_commissions();

  WPRB_Segment.update_commission_type = function() {
    var commission_type = $('select#ea-override-commission-type').val();

    if(commission_type=='percentage' || commission_type=='' || commission_type==undefined) {
      $('.ea-override-commission-percentage-symbol').show();
      $('.ea-override-commission-currency-symbol').hide();
    }
    else {
      $('.ea-override-commission-percentage-symbol').hide();
      $('.ea-override-commission-currency-symbol').show();
    }
  };

  WPRB_Segment.update_commission_type();

  $('body').on('click', '#esaf-add-override-match-row', function(e) {
    e.preventDefault();
    WPRB_Segment.append_match_row();
  });

  $('body').on('click', '#esaf-add-override-commission-row', function(e) {
    e.preventDefault();
    WPRB_Segment.append_commission_row();
    WPRB_Segment.update_commission_type();
  });

  $('body').on('click', '#esaf-remove-override-match-row', function(e) {
    e.preventDefault();
    $('#ea-override-matches .ea-override-match-row:last-child').remove();

    var row_count = WPRB_Segment.get_match_row_count();
    if(row_count <= 0) {
      $(this).hide();
    }
  });

  $('body').on('click', '#esaf-remove-override-commission-row', function(e) {
    e.preventDefault();
    $('#ea-override-commissions .ea-override-commission-row:last-child').remove();

    var row_count = WPRB_Segment.get_commission_row_count();
    if(row_count <= 0) {
      $(this).hide();
    }
  });
  
  $('body').on('change', '.ea-override-match-type select', function(e) {
    e.preventDefault();

    var row = $(this).parent().parent();

    var row_index = WPRB_Segment.get_match_row_index(row);

    row.find('.ea-override-match-field, .ea-override-match-operator, .ea-override-match-condition').remove();

    var match_type = $(this).val();

    WPRB_Segment.append_field_tpl(row, match_type, row_index);
  });

  $('body').on('change', '.ea-override-match-field select', function(e) {
    e.preventDefault();

    var row = $(this).parent().parent();
    var row_index = WPRB_Segment.get_match_row_index(row);

    row.find('.ea-override-match-operator, .ea-override-match-condition').remove();

    match_type = row.find('.ea-override-match-type select').val();
    match_field = $(this).val();

    WPRB_Segment.append_operator_and_condition_tpl(row, match_type, match_field, row_index);
  });

  $('body').on('change', 'select#ea-override-commission-type', function(e) {
    e.preventDefault();
    WPRB_Segment.update_commission_type();
  });

});

