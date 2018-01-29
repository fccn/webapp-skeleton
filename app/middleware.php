<?php
/*
 * Loads middleware into Slim
 *
*/

use \Fccn\Lib\FileLogger;
use \Fccn\Lib\SiteConfig;

//Add locale middleware
$container = $app->getContainer();
$app->add(new Fccn\WebComponents\LocaleMiddleware($container['locale']));

//--- include other middlewares defined in middlewares folder
foreach (glob("middlewares/*.php") as $filename) {
    include $filename;
}
