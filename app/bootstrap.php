<?php

require '../vendor/autoload.php';

// Setup custom Twig view
$twigView = new \Slim\Extras\Views\Twig();

$in_development = file_exists("/tmp/development.webtut.txt");

$app = new \Slim\Slim(array(
    'mode' => ($in_development?'development':'production'),
    'debug' => $in_development,
    'view' => $twigView,
    'templates.path' => '../templates/',
));

$app->view()->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);

$app->view()->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new \JSW\Twig\TwigExtension());

$app->notFound(function () use ($app) {
    $app->render('404.html.twig');
});


return $app;