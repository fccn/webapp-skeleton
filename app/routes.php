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

#--- helper functions
function getSession($force_login = false){
  \FileLogger::debug('getting session info');
  $ss = Libs\AuthSession::getInstance($force_login);
  if ($ss->isAuthenticated()) {
    return $ss;
  }elseif($force_login){
    sendToNotLoggedIn();
  }else{
    return false;
  }
}

function getDevices($req){
  $user_agent = $req->getUserAgent();
  $devices = new stdClass;
  $devices->is_iOS = (preg_match('/iPhone|iPad|iPod/', $user_agent) != 0);
  $devices->is_iOS_iPad = (preg_match('/iPad/', $user_agent) != 0);
  $devices->is_iOS_iPhone = (preg_match('/iPhone|iPod/', $user_agent) != 0);
  $devices->is_Android = (preg_match('/Android/', $user_agent) != 0);
  $devices->is_WM8 = (preg_match('/Windows Phone 8/', $user_agent) != 0);
  $devices->is_mobile = ($devices->is_iOS || $devices->is_Android || $devices->is_WM8);
  return $devices;
}

//---- Redirects
function sendToUnauthorized(){
  $app = \Slim\Slim::getInstance();
  $app->render('405.html.twig', [
    'message' => \Libs\Locale::getHtmlContent("error_405")]);
}

function sendToNotLoggedIn(){
  $app = \Slim\Slim::getInstance();
  $app->render('405.html.twig', [
    'message' => \Libs\Locale::getHtmlContent("error_not_logged")]);
}

function sendToNotFound(){
  $app = \Slim\Slim::getInstance();
  $app->render('404.html.twig', [
    'message' => \Libs\Locale::getHtmlContent("error_404")]);
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
