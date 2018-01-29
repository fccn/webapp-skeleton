<?php
/*
* Initializes the web application
*/

$app = new \Slim\App(['settings' => \Fccn\Lib\SiteConfig::getInstance()->get('slim_settings')]);

#array(
#    'mode' => \Fccn\Lib\SiteConfig::getInstance()->get('app_mode'),
#    'templates.path' => \Fccn\Lib\SiteConfig::getInstance()->get('twig_templates_path'),
#));


// Fetch DI Container
$container = $app->getContainer();

// Register application configurations
$container['config'] = function ($cnt) {
    $config = \Fccn\Lib\SiteConfig::getInstance();
    return $config;
};

//setup view renderer
// Register Twig View helper
$container['view'] = function ($cnt) {
    $view = new \Slim\Views\Twig(\Fccn\Lib\SiteConfig::getInstance()->get('twig_templates_path'), [
        'cache' => \Fccn\Lib\SiteConfig::getInstance()->get('twig_cache_path'),
        'charset' => 'utf-8',
        'auto_reload' => true,
        'strict_variables' => false,
        'autoescape' => true,
        'debug' => \Fccn\Lib\SiteConfig::getInstance()->get('twig_debug'),
    ]);

    #add additional paths
    #$view->getLoader()->setPaths($path, $namespace)

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $cnt['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($cnt['router'], $basePath));
    #load filters and extensions
    \Fccn\Lib\ConfigLoader::loadTwigFiltersAndExtensions($view->getEnvironment());
    #load common vars
    \Fccn\Lib\ConfigLoader::loadTwigGlobals($view->getEnvironment());

    return $view;
};

//setup slim logs with file logger
$container['logger'] = function ($cnt) {
    $logger = \Fccn\Lib\FileLogger::getInstance();
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler(\Fccn\Lib\SiteConfig::getInstance()->get('logfile_path'), \Fccn\Lib\SiteConfig::getInstance()->get('logfile_level')));
    return $logger;
};

//setup locale utilities with Locale

$container['locale'] = function ($cnt) {
    $locale = new Fccn\Lib\Locale(array('slim_middleware' => true));
    #$current_lang = $locale->getCurrentLang();
    #add global lang var
    $cnt->view->getEnvironment()->addGlobal('lang', $locale);
    return $locale;
};

//setup library loader
$container['loader'] = function ($cnt) {
    $loader = \Fccn\WebComponents\ExtLibsLoader::getInstance();
    return $loader;
};


// Register middlewares
require __DIR__ . '/middleware.php';

// Register routes
require __DIR__ . '/routes.php';

// Run app
$app->run();
