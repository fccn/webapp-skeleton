/*
 * asyncronous load of common javascript libraries included in composer and npm
 * TODO move to ws-utils project
 */

head.load({jquery: '/script/lib/jquery'}, function(){
  //jquery dependent libs
  head.load({moment: '/script/lib/moment'},
    {page_loader: '/js/page_loader.min.js'},
    {cookie_utils: '/js/cookie_utils.min.js' }
    //---- additional common libs ---
  );
  //load bootstrap and themes
  head.load({bootstrap: '/script/lib/bootstrap'},function() {
    head.load({bootbox: '/script/lib/bootbox'},
      {datetimepicker: '/script/lib/datetimepicker'}
      //---- additional common libs that require boostrap ---
    );
  });
});
