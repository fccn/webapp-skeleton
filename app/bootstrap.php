<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';
require_once 'libs/AppLog.php';
require_once 'libs/Locale.php';
require_once 'libs/SiteConfig.php';
require_once 'libs/AuthSession.php';
require_once 'libs/WebMailer.php';
require_once 'libs/AppUtils.php';


// Prepare app
$app = new \Slim\Slim(array(
    'mode' => \SiteConfig::getInstance()->get('mode'),
    'templates.path' => realpath(dirname(__FILE__).'/../templates'),
));

// Prepare view
$app->view(new \Slim\Views\Twig());

$app->view()->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true,
    'debug' => true,
);

$app->view()->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new \JSW\Twig\TwigExtension(),
    new Twig_Extensions_Extension_I18n(),
    new Twig_Extension_Debug()
  );


// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => true
    ));
});


// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    if(\SiteConfig::getInstance()->get('logfile_level') == 'DEBUG'){
      $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    }elseif (\SiteConfig::getInstance()->get('logfile_level') == 'INFO') {
      $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::INFO));
    }else{ #default to warning
      $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::WARNING));
    }
    return $log;
});

$app->notFound(function () use ($app) {

    $app->render('404.html.twig', [
    'message' => \Libs\Locale::getHtmlContent("error_404")]);
});

// Handle Locale Cookie
$app->add(new Libs\Locale());
$app->add(new Libs\AuthSession());

$app->view()->set('config', \SiteConfig::getInstance()->all());

$filter  = new Twig_SimpleFilter("cast_to_array", function($stdClassObject) {
    $response = array();
    foreach ($stdClassObject as $key => $value) {
      $response[] = array($key, $value);
    }
    return $response;
  });

$app->view()->getEnvironment()->addFilter($filter);

$filter  = new Twig_SimpleFilter("type", function($stdClassObject) {
    return gettype($stdClassObject);
  });

$app->view()->getEnvironment()->addFilter($filter);

$filter  = new Twig_SimpleFilter("translate", function($stdClassObject) {
    return _($stdClassObject);
  });

$app->view()->getEnvironment()->addFilter($filter);

$filter = new Twig_SimpleFilter('md5', function ($string) {
    return md5($string);
});

$app->view()->getEnvironment()->addFilter($filter);

$filter = new Twig_SimpleFilter('toXBts', function ($rate) {
    return Libs\AppUtils::bytes_pretty_print($rate);
});

$app->view()->getEnvironment()->addFilter($filter);

$filter = new Twig_SimpleFilter('secToDate', function ($secs,$format) {
    return date($format,$secs);
});

$app->view()->getEnvironment()->addFilter($filter);

//add functions

$function = new Twig_SimpleFunction('compress_params', function ($params) {
    return Libs\AppUtils::compress_params($params);
});

$app->view()->getEnvironment()->addFunction($function);

$function = new Twig_SimpleFunction('get_org_shortname', function () {
    return Libs\AppUtils::get_short_fqdn();
});

$app->view()->getEnvironment()->addFunction($function);

$function = new Twig_SimpleFunction('to_json', function ($var) {
    return json_encode($var);
});

$app->view()->getEnvironment()->addFunction($function);

//store twig view in container
$app->container['twig'] = $app->view();

return $app;
