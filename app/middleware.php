<?php
/*
 * Loads global middlewares into Slim
 *
*/

use \Fccn\Lib\FileLogger;
use \Fccn\Lib\SiteConfig;

//--- include other middlewares defined in middlewares folder
foreach (glob("middlewares/*.php") as $filename) {
    include $filename;
}
