<?php

$app->get('/setlang/:lang', function ($lang) use ($app) {
  foreach(\SiteConfig::getInstance()->get("locales") as $locale) {
    if (strtoupper($lang) == strtoupper($locale["label"])) {
      $app->setCookie('locale', $locale['locale']);
    }
  }

  if ($app->request()->getReferrer() == "") {
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/");
  } else {
    $app->redirect($app->request()->getReferrer());
  }
});

$app->get('/login', function () use ($app) {

  $session = Libs\SAMLSession::getInstance(true);
  if ($session->isAuthenticated()) {
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . '/');
  } else
    $app->render('login-failed.html.twig', [
      'attributes' => $session->getAttributes()]);

});

$app->get('/logout', function () use ($app) {

  $session = Libs\SAMLSession::getInstance(false);

  if ($session->isAuthenticated()) {

    $user = Model::factory('User')->where('email',$session->getEmail())->find_one();

    if (($user != false) && (get_class($user) == "User")) {
      $user->in_session = false;
      $user->save();
      AppLog("logout", $user);
    }

    $session->logout();

  } else
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/");
});

$app->get('/css', function () use ($app) {
  $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/assets/css");
});

//--- user specific utilities

$app->group('/user', function() use ($app) {
  require_once "users.php";
});

//add application specific utilities here -----
