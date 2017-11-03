/*
 * asyncronous load of javascript libraries included in composer and npm
 * --- Include additional composer and npm javascript libraries here ----
 * TODO move init-common.js to ws-utils project
 */

head.load({commons: '/js/init-common.js'}, function(){
  //---- application specific utilities
  head.load({app_utils: '/js/app.js' });
  //---- include other project specific libs here
});
