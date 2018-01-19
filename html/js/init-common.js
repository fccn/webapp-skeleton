/*
 * asyncronous load of common javascript libraries included in composer and npm
 * TODO move to ws-utils project
 */

head.load({jquery: '/script/lib/jquery'}, function(){
  //jquery dependent libs
  head.load({moment: '/script/lib/moment'},
    {page_loader: '/script/lib/page_loader'},
    {cookie_utils: '/script/lib/cookie_utils' }
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
