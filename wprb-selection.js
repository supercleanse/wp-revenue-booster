
jQuery(document).ready(function ($) {

  // If these tags are present then it's likely that this is a container element
  var wprb_regex = RegExp('<\s*(div|img|i|a|span|blockquote|p)\s*[> ]');
  var selector_generator = new CssSelectorGenerator;

  // 1. Go over selections an add wprb-selection-added and "click to add customization" tooltip
  // 2. On click of an element already in selections, remove from WPRB.selections, AJAX call to remove on server, remove wprb-selection-added and show tooltip "Selection Removed"
  // 3. On hover add wprb-add-selection and "click to remove customization" tooltip
  // 4. On Click of a normal element, add to WPRB.selections, AJAX call to add on server, add wprb-selection-added, remove

  // These are the tags we can target
  $( 'h1,h2,h3,h4,h5,h6,a,button,p,div,li,span,blockquote' ).hover(
    function() {
      var txt = $(this).html();

      if(txt.length > 0 && !wprb_regex.test(txt)) {
        var page = location.href.replace(/\?.*/,'');
        var selector = selector_generator.getSelector(this);
        var hash = btoa(selector).replace(/=/,'');
        var id = 'mprb-tpl-' + hash;

        console.log("IN PAGE: " + page);
        console.log("SELECTOR: " + selector);
        console.log("ID: " + id);

        // Popover stuff
        var myTemplate = document.createElement('div')
        myTemplate.id = id;
        myTemplate.innerHTML = '<h3>Cool <span style="color: pink;">HTML</span> inside here!</h3><br/><input type="text"/>';
        console.log('huh?', document.querySelector('#'+id));
        tippy( selector, {
          html: myTemplate,
          trigger: 'click',
          theme: 'white'
        } );

        var customizations = {};
        if(typeof WPRB.customizations[selector] !== 'undefined') {
          customizations = WPRB.customizations[selector];
          console.log('** CUSTOMIZATIONS WERE FOUND', WPRB.customizations[selector]);
        }

        $(this).addClass('wprb-add-selection');
      }
    },
    function() {
      var txt = $(this).html();

      if(txt.length > 0 && !wprb_regex.test(txt)) {
        var page = location.href.replace(/\?.*/,'');
        var selector = selector_generator.getSelector(this);
        var id = 'mprb-tpl-' + btoa(selector).replace(/=/,'');

        $('#'+id).remove();
        $(this).removeClass('wprb-add-selection');
      }
    }

  );
});

