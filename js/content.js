jQuery(document).ready(function ($) {

  // Retrieve customizations from the server via AJAX
  var wprb_get_customizations = function(cb) {
    var args = {
      action: 'wprb_get_customizations',
      page_uri: WPRB_Content.page_uri
    };

    $.ajax({
      type: 'post',
      dataType: 'json',
      url: WPRB_Content.ajaxurl,
      data: args,
      success: function(res) {
        if(res==-1) {
          return console.log('Unauthorized call to get_customizations');
        }

        if(typeof res.error !== 'undefined') {
          return console.log('get_customizations: ' + res.error);
        }

        cb(res);
      }
    });
  };

  wprb_get_customizations( function(customizations) {
    if(customizations instanceof Array) {
      for(var i=0; i < customizations.length; i++) {
        var cust = customizations[i];

        if( typeof cust.selector != 'undefined' &&
            typeof cust.content != 'undefined' ) {
          $(cust.selector).html(cust.content);
        }
      }
    }
  });
});

