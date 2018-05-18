
jQuery(document).ready(function ($) {

  // Elements likely to contain pure text
  var target_elements = 'h1,h2,h3,h4,h5,h6,a,button,p,div,li,span,blockquote';

  // If these tags are present then it's likely that this is a container element
  var wprb_regex = RegExp('<\s*(div|img|i|a|span|blockquote|p|input|form|select|textarea)\s*[> ]');
  var selector_generator = new CssSelectorGenerator;

  // Scrub our highlight classes before trying to get a selector
  var wprb_get_selector = function(target) {
    var selector = selector_generator.getSelector(target);

    if($(target).hasClass('wprb-add-selection')) {
      $(target).removeClass('wprb-add-selection');
      selector = selector_generator.getSelector(target);
      $(target).addClass('wprb-add-selection');
    }
    else if($(target).hasClass('wprb-selection-added')) {
      $(target).removeClass('wprb-selection-added');
      selector = selector_generator.getSelector(target);
      $(target).addClass('wprb-selection-added');
    }

    return selector;
  };

  var wprb_show_tooltip = function(target) {
    // TODO: Add update target support
    tippy(target, {arrow: true});
  };

  var wprb_hover_over_text = function(target) {
    var selector = wprb_get_selector(target);

    if($.inArray(selector, WPRB_Customization.selections) !== -1) {
      //$(target).attr('title', WPRB_Customization.strings['edit_selection']);
    }
    else {
      $(target).attr('title', WPRB_Customization.strings['add_selection']);
      $(target).addClass('wprb-add-selection');
    }

    wprb_show_tooltip(target);
  };

  var wprb_replace_vars = function(str,obj) {
    for (var k in obj){
      if (obj.hasOwnProperty(k)) {
        var regexp_str = '\\\{\\\{' + k + '\\\}\\\}';
        var regexp = new RegExp(regexp_str,'g');
        str = str.replace(regexp, obj[k]);
      }
    }

    return str;
  };

  /** This method maps the form_data returned by serializeArray
   *  in name/value pairs into actual objects.
   */
  var wprb_map_form_data = function(form) {
    // Name / Value Pairs
    var serialized_data = $(form).serializeArray();
    var re = /cust\[([^\]]*)\]\[([^\]]*)\]/;

    var form_data = [];
    var last_matched_index = null;
    var form_data_index = 0;
    for(var i=0; i < serialized_data.length; i++) {
      var m = serialized_data[i].name.match(re);

      if(typeof m[1] != 'undefined' && typeof m[2] != 'undefined') {
        var matched_index = parseInt(m[1]);

        // We don't care what the matched index is we just
        // increment our real, fixed index when it changes ... 
        if(last_matched_index!=matched_index) {
          if(last_matched_index != null) {
            form_data_index++;
          }

          last_matched_index = matched_index;
        }


        var field = m[2];

        if(typeof form_data[form_data_index] == 'undefined') {
          form_data[form_data_index] = {};
        }

        form_data[form_data_index][field] = serialized_data[i].value;
      }
    }

    return form_data;
  };

  var wprb_save_customizations = function(form, cb) {
    var form_data = wprb_map_form_data(form);
    var selector = $(form).data('selector');

    var args = {
      action: 'wprb_update_customizations',
      page_uri: $(form).data('page-uri'),
      selector: selector,
      cust: form_data,
      security: WPRB_Customization.security
    };

    $.ajax({
      type: 'post',
      dataType: 'json',
      url: WPRB_Customization.ajaxurl,
      data: args,
      success: function(res) {
        if(res==-1) {
          return alert('Unauthorized');
        }

        if(typeof res.error !== 'undefined') {
          return alert(res.error);
        }

        // Update on-page customizations
        WPRB_Customization.customizations[selector] = res.customizations;

        cb(args.cust.length);
      }
    });
  };

  var wprb_register_submit_event = function(modal) {
    $(modal.$elm).find('form.wprb-customizations-form').on('submit', function(e) {
      e.preventDefault();

      var selector = $(this).data('selector');
      var target = $(selector);

      wprb_save_customizations(this, function(cust_count) {
        if(cust_count > 0) {
          WPRB_Customization.selections.push(selector);
          $(target).addClass('wprb-selection-added');
          $(target).attr('title', WPRB_Customization.strings['edit_selection']);

        }
        else {
          // Remove selection from WPRB_Customization.selections
          var selection_index = WPRB_Customization.selections.indexOf(selector);
          WPRB_Customization.selections.splice(selection_index, 1);

          $(target).removeClass('wprb-selection-added');
        }

        $.wprb_modal.close();
      });

    });
  };

  var wprb_get_popup_html = function(target) {
    var selector = wprb_get_selector(target);

    var vars = {
      current_text: $(target).html(),
      selector: selector
    };

    return wprb_replace_vars(WPRB_Customization.popup, vars);
  };

  var wprb_get_popup_row_html = function(data) {
    if(data == null) {
      data = {};
    }

    var vars = {
      id: data.id || '',
      content: data.content || '',
      index: data.index || 1
    };

    return wprb_replace_vars(WPRB_Customization.popup_row, vars);
  };

  var wprb_register_remove_customization_event = function() {
    $('a.wprb-remove-customization').click(
      function (e) {
        e.preventDefault();
        $(this).parent().remove();
      }
    );  
  };

  var wprb_register_add_customization_event = function() {
    $('.wprb-add-customizations a').click(
      function(e) {
        e.preventDefault();

        // Ancestry path then find wprb-customizations underneath
        // button -> span.wprb-add-customizations -> div.wprb-add-remove -> form.wprb-customizations-form
        var cust_elem = $(this).parent().parent().parent().find('.wprb-customizations');
        var cust_index = $(cust_elem).children().length + 1;
        var popup_row_html = wprb_get_popup_row_html({index: cust_index});

        $(cust_elem).append(popup_row_html);

        wprb_register_remove_customization_event();
      }
    );
  };

  var wprb_click_text = function(target) {
    var html = wprb_get_popup_html(target);
    $(html).appendTo($('body')).wprb_modal({fadeDuration: 250});
  };

  for(var i=0; i < WPRB_Customization.selections.length; i++) {
    var selector = WPRB_Customization.selections[i];
    if($(selector).length > 0) { // Element exists?
      $(selector).addClass('wprb-selection-added');
      $(selector).attr('title', WPRB_Customization.strings['edit_selection']);
    }
  }

  $( target_elements ).not('#wpadminbar, #wpadminbar *, .wprb-toggle-selection-mode, .wprb-toggle-selection-mode a').hover(
    function() {
      var txt = $(this).html();
      if(txt.length > 0 && !wprb_regex.test(txt)) {
        wprb_hover_over_text(this);
      }
    },
    function() {
      var txt = $(this).html();
      if(txt.length > 0 && !wprb_regex.test(txt)) {
        $(this).removeClass('wprb-add-selection');
      }
    }

  );

  // Click text event handler
  $( target_elements ).not('#wpadminbar, #wpadminbar *, .wprb-toggle-selection-mode, .wprb-toggle-selection-mode a').click(
    function(e) {
      var txt = $(this).html();
      if(txt.length > 0 && !wprb_regex.test(txt)) {
        e.preventDefault();
        wprb_click_text(this);
      }
    }
  );

  // Load the Popup's form with existing data
  var wprb_load_popup_form = function(modal) {
    var form = $(modal.$elm).find('form.wprb-customizations-form');
    var selector = $(form).data('selector');

    if(typeof WPRB_Customization.customizations[selector] != 'undefined') {
      var cust = WPRB_Customization.customizations[selector];
      var cust_elem = $(modal.$elm).find('form.wprb-customizations-form .wprb-customizations');

      for(var i=0; i < cust.length; i++) {
        var cust_index = $(cust_elem).children().length + 1;

        var args = {
          id: cust[i].id,
          index: cust_index,
          content: cust[i].content,
          segment_id: cust[i].segment_id
        };

        var popup_row_html = wprb_get_popup_row_html(args);

        $(cust_elem).append(popup_row_html);

        // Grab the most recently appended row -> segment
        var select_elem = $(cust_elem).find('.wprb-customization-row:last-child select.wprb-customization-segment');

        select_elem.val(cust[i].segment_id);
      }
    }
  };

  // Register events on modal open and do some search and replace
  $('body').on($.wprb_modal.OPEN, function(event, modal) {
    wprb_register_add_customization_event();
    wprb_register_submit_event(modal);
    wprb_load_popup_form(modal);

    // Must happen after load_popup_form so all of the customization rows are in place
    wprb_register_remove_customization_event();
  });

});

