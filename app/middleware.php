<?php
/*
 * Loads middleware into Slim
 *
*/

use \Fccn\Lib\FileLogger;
use \Fccn\Lib\SiteConfig;

//Add locale middleware
$app->add(new Fccn\WebComponents\LocaleMiddleware());


//--- include other middlewares below
