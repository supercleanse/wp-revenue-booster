jQuery(document).ready(function ($) {
  if(typeof WPRB_Content.customizations != 'undefined' &&
     WPRB_Content.customizations instanceof Array) {

    for(var i=0; i < WPRB_Content.customizations.length; i++) {
      var cust = WPRB_Content.customizations[i];

      if( typeof cust.selector != 'undefined' &&
          typeof cust.content != 'undefined' ) {
        $(cust.selector).html(cust.content);
      }
    }

  }
});

