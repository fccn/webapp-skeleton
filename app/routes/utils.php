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

#-- show select login page
$app->get('/select_login', function() use ($app) {
  $rto = $app->request()->get('rto');
  $app->render('select_login.html.twig', [
    'description' => \Libs\Locale::getHtmlContent("select_login_intro"),
    'rto' => $rto
  ]);
});

#-- login user
$app->get('/login/:kind', function ($kind) use ($app) {
  \FileLogger::debug("GET /utils/login/$kind");
  //get valid auth schemes
  $valid_auths = [Libs\AuthProvider::$RCTSAAI];
  $social_auths = \SiteConfig::getInstance()->get('hauth_config');
  foreach ($social_auths['providers'] as $sa_name => $sa_status) {
    if(!empty($sa_status['enabled'])){
      $valid_auths << strtolower($sa_name);
    }
  }
  if(in_array($kind,$valid_auths)) {
    //get session instance
    $session = Libs\AuthSession::getInstance(false);
    if($session && $session->isAuthenticated()){
      //user is already authenticated
      $app->redirect(\SiteConfig::getInstance()->get("base_path") . '/');
    }elseif($session){
      \FileLogger::debug('try authenticate user');
      $session->authenticate($kind);
      \FileLogger::debug('tried authenticate user');
      if($session->isAuthenticated()){
        \FileLogger::debug('user authenticated '.$kind);
        //TODO handle redirect
        $redirect_url = $app->request()->get('rto');
        if(empty($redirect_url)){
          $redirect_url = '/';
        }
        $app->redirect(\SiteConfig::getInstance()->get("base_path") . $redirect_url);
      }else{
        \FileLogger::debug('unable to login with '.$kind);
        if($kind == Libs\AuthProvider::$RCTSAAI){
          $tbl = $session->listAttributeErrors();
          $app->render('login_failed_rctsaai.html.twig', [
            'attr_table' => $tbl,
            'description' => \Libs\Locale::getHtmlContent("login_failure")
          ]);
        }else{
          #unable to login with this provider
          $app->render('login_failed.html.twig', [
            'provider' => $kind
          ]);
        }
      }
    }
  }else{
    \FileLogger::debug("Provider $kind not supported");
    #provider not supported
    $app->render('login_failed.html.twig', [
      'msg' => _('Provider not supported'),
      'provider' => $kind
    ]);
  }
});

#-- logout user
$app->get('/logout', function () use ($app) {

  $session = Libs\AuthSession::getInstance(false);
  \FileLogger::debug('has auth? '.!empty($session->getAuth()));
  //logout
  $session->logout();
  //redirect to main page
  $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/");

  /*else
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/"); */
});

$app->get('/css', function () use ($app) {
  $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/assets/css");
});

//--- user specific utilities

$app->group('/user', function() use ($app) {
  require_once "users.php";
});

//add application specific utilities here -----
