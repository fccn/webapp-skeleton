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

//---- Redirects
function sendToUnauthorized(){
  $app = \Slim\Slim::getInstance();
  $app->render('405.html.twig', [
    'message' => \Libs\Locale::getHtmlContent("error_405")]);
}

function APIrequest(){
//---- API requests
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
