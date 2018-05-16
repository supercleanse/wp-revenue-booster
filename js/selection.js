
jQuery(document).ready(function ($) {

  // Elements likely to contain pure text
  var target_elements = 'h1,h2,h3,h4,h5,h6,a,button,p,div,li,span,blockquote';

  // If these tags are present then it's likely that this is a container element
  var wprb_regex = RegExp('<\s*(div|img|i|a|span|blockquote|p)\s*[> ]');
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
      //$(target).attr('title', WPRB_Customization.strings['remove_selection']);
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
        console.log('regexp_str',regexp_str);
        var regexp = new RegExp(regexp_str,'g');
        str = str.replace(regexp, obj[k]);
      }
    }

    return str;
  };

  var wprb_save_customizations = function(form) {
    // TODO: AJAX save
    var form_data = $(form).serialize();
    console.log('form_data',form_data);
  };

  var wprb_register_submit_event = function(modal) {
    $(modal.$elm).find('form.wprb-customizations-form').on('submit', function(e) {
      e.preventDefault();

      var selector = $(this).data('selector');
      var target = $(selector);
      var selector = wprb_get_selector(target);

      //WPRB_Customization.selections.push(selector);

      wprb_save_customizations(this);

      $(target).addClass('wprb-selection-added');
      $(target).attr('title', WPRB_Customization.strings['remove_selection']);

      $.modal.close();
    });
  };

  var wprb_get_popup_html = function(target) {
    var selector = wprb_get_selector(target);

    var vars = {
      current_text: $(target).html(),
      selector: selector
    };
    console.log('popup',WPRB_Customization.popup);

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

  var wprb_register_add_customization_event = function() {
    $('.wprb-add-customizations button').click(
      function(e) {
        e.preventDefault();

        // Ancestry path then find wprb-customizations underneath
        // button -> span.wprb-add-customizations -> div.wprb-add-remove -> form.wprb-customizations-form
        var cust_elem = $(this).parent().parent().parent().find('.wprb-customizations');
        var cust_index = $(cust_elem).children().length + 1;
        console.log('cust_index',cust_index);
        var popup_row_html = wprb_get_popup_row_html({index: cust_index});

        $(cust_elem).append(popup_row_html);
      }
    );
  }

  var wprb_click_text = function(target) {
    var selector = wprb_get_selector(target);

    if($.inArray(selector, WPRB_Customization.selections) !== -1) {
      $(target).removeClass('wprb-selection-added');
      $(target).attr('title', WPRB_Customization.strings['selection_removed']);
      wprb_show_tooltip(target);

      // Remove selection from WPRB_Customization.selections
      var selection_index = WPRB_Customization.selections.indexOf(selector);
      WPRB_Customization.selections.splice(selection_index, 1);

      // TODO: AJAX Call to remove selection

      $(target).addClass('wprb-add-selection');
    }
    else {
      //$(target).removeClass('wprb-add-selection');
      //$(target).attr('title', WPRB_Customization.strings['selection_added']);
      //wprb_show_tooltip(target);

      var html = wprb_get_popup_html(target);
      console.log('popup html',html);
      $(html).appendTo($('body')).modal({fadeDuration: 250});

      //WPRB_Customization.selections.push(selector);

      // TODO: AJAX Call to add selection

      //$(target).addClass('wprb-selection-added');
      //$(target).attr('title', WPRB_Customization.strings['remove_selection']);
    }
  };

  for(var i=0; i < WPRB_Customization.selections.length; i++) {
    var selector = WPRB_Customization.selections[i];
    if($(selector).length > 0) { // Element exists?
      $(selector).addClass('wprb-selection-added');
      $(selector).attr('title', WPRB_Customization.strings['remove_selection']);
    }
  }

  $( target_elements ).not('#wpadminbar, #wpadminbar *').hover(
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
  $( target_elements ).not('#wpadminbar, #wpadminbar *').click(
    function(e) {
      e.preventDefault();
      var txt = $(this).html();
      if(txt.length > 0 && !wprb_regex.test(txt)) {
        wprb_click_text(this);
      }
    }
  );

  // Register events on modal open and do some search and replace
  $('body').on($.modal.OPEN, function(event, modal) {
    wprb_register_add_customization_event();
    wprb_register_submit_event(modal);
  });

});

