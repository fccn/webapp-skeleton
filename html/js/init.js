/*
 * asyncronous load of javascript libraries included in composer and npm
 * --- Include additional composer and npm javascript libraries here ----
 */

head.load({jquery: '/js/ext_libs.js.php?lib=jquery'}, function(){
  //jquery dependent libs
  head.load({moment: '/js/ext_libs.js.php?lib=moment'},
    {chart_js: '/js/ext_libs.js.php?lib=chart_js'},
    {page_loader: '/js/page_loader.min.js'},
    {cookie_utils: '/js/cookie_utils.min.js' }
    //---- additional project specific libs ---
  );
  //load bootstrap and themes
  head.load({bootstrap: '/js/ext_libs.js.php?lib=bootstrap'},function() {
    head.load({bootbox: '/js/ext_libs.js.php?lib=bootbox'},
      {datetimepicker: '/js/ext_libs.js.php?lib=datetimepicker'}
      //---- additional project specific libs that require boostrap ---
      ,function() {
        head.load({app_utils: '/js/app.js' });
    });
  });
});
