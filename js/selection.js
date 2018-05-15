
jQuery(document).ready(function ($) {

  // Elements likely to contain pure text
  var target_elements = 'h1,h2,h3,h4,h5,h6,a,button,p,div,li,span,blockquote';

  // If these tags are present then it's likely that this is a container element
  var wprb_regex = RegExp('<\s*(div|img|i|a|span|blockquote|p)\s*[> ]');
  var selector_generator = new CssSelectorGenerator;

  // 1. Go over customizations an add wprb-selection-added and "click to add customization" tooltip
  // 2. On click of an element already in selections, remove from WPRB_Customization.selections, AJAX call to remove on server, remove wprb-selection-added and show delayed/fade tooltip "Selection Removed"
  // 3. On hover add wprb-add-selection and "click to remove customization" tooltip
  // 4. On Click of a normal element, add to WPRB_Customization.selections, AJAX call to add on server, add wprb-selection-added, remove wprb-add-selection and show delayed/fade tooltip "Selection Added"

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
  }

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

  // Register events on modal open and do some search and replace
  $('body').on($.modal.OPEN, function(event, modal) {
    $(modal.$elm).find('form.wprb-customizations-form').on('submit', function(e) {
      e.preventDefault();

      var selector = $(this).data('selector');
      var target = $(selector);
      console.log('target',target);
      var selector = wprb_get_selector(target);

      console.log("SELECTOR", selector);

      WPRB_Customization.selections.push(selector);

      // TODO: AJAX save

      $(target).addClass('wprb-selection-added');
      $(target).attr('title', WPRB_Customization.strings['remove_selection']);

      $.modal.close();
    });
  });

  var wprb_get_popup_html = function(target) {
    var selector = wprb_get_selector(target);

    tpl = WPRB_Customization.popup;
    tpl = tpl.replace(/\{\{current-text\}\}/g,'<pre>'+$(target).html()+'</pre>');
    tpl = tpl.replace(/\{\{selector\}\}/g,selector);

    return tpl;
  };

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
      $(target).removeClass('wprb-add-selection');
      $(target).attr('title', WPRB_Customization.strings['selection_added']);
      wprb_show_tooltip(target);

      this.blur();
      var html = wprb_get_popup_html(this);
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

  $( target_elements ).not('#wpadminbar, #wpadminbar *').click(
    function(e) {
      e.preventDefault();
      var txt = $(this).html();
      if(txt.length > 0 && !wprb_regex.test(txt)) {
        wprb_click_text(this);
      }
    }
  );
});

