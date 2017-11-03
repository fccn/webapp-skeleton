<?php
/*
 * Loads middleware into Slim
 *
*/

use \Fccn\Lib\FileLogger;
use \Fccn\Lib\SiteConfig;

//Add locale utilities
$app->add(function ($request, $response, $next) {
  #initialize locale
  $this->get('locale')->init();

  $response = $next($request, $response);
	return $response;
});

//--- include other middlewares below
