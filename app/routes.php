<?php

// Define routes

$app->get('/', function () use ($app) {
  $app->render('index.html.twig', ['description' => \Libs\Locale::getHtmlContent("homepage_text")]);
});

$app->group('/doc', function() use ($app) {
    require_once "routes/doc.php";
});

$app->group('/utils', function() use ($app) {
    require_once "routes/utils.php";
});

$app->group('/admin', function() use ($app) {
    require_once "routes/admin.php";
});

//---- API requests
function APIrequest(){
        $app = \Slim\Slim::getInstance();

        $app->view(new \JsonApiView());
        $app->add(new \JsonApiMiddleware());

        $app->view()->clear("config");
        $app->view()->clear("lang");
        $app->view()->clear("ss");

}

$app->group('/api', 'APIrequest', function() use ($app) {
    require_once "routes/api.php";
});

//---- define other routes...
