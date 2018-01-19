<?php

/*
* define routes
*/


use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view
    return $this->view->render($response, 'index.html.twig', [
      'intro' => $this->locale->getHtmlContent('homepage_intro'),
      'number' => 2,
      'gt_trans' => _('this was translated on the controller'),
    ]);
});

$app->get('/utils/setlang/{lang}', Fccn\WebComponents\SwitchLanguageAction::class);

#handle script requests in routes/script.php
$app->group('/script', function () use ($app) {
    require_once "routes/script.php";
});
