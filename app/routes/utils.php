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

#-- login user
$app->get('/login/:kind', function ($kind) use ($app) {
  \FileLogger::debug("login user with provider -$kind-");
  //get valid auth schemes
  $valid_auths = [Libs\AuthProvider::$RCTSAAI];
  $social_auths = \SiteConfig::getInstance()->get('hauth_config');
  foreach ($social_auths['providers'] as $sa_name => $sa_status) {
    if(!empty($sa_status['enabled'])){
      $valid_auths << strtolower($sa_name);
    }
  }
  if(in_array($kind,$valid_auths)) {
    //get session instance and force authentication
    $session = Libs\AuthSession::getInstance();
    if($session && $session->isAuthenticated()){
      //user is already authenticated
      $app->redirect(\SiteConfig::getInstance()->get("base_path") . '/');
    }elseif($session){
      \FileLogger::debug('try authenticate user');
      $session->authenticate($kind);
      \FileLogger::debug('tried authenticate user');
      if($session->isAuthenticated()){
        \FileLogger::debug('user authenticated '.$kind);
        $app->redirect(\SiteConfig::getInstance()->get("base_path") . '/');
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
    /*  if($session && $session->isSAML()) {
      $expected = $session->getAllExpectedAttributes();
      $tbl = array();
      foreach($expected as $attribute => $params) {
        $status = array(
          'attribute' => $attribute,
          'mandatory' => $params["mandatory"] ? true : false,
          'value' => $session->findAttribute($attribute),
          'regex' => 1
        );
        if ($status['value']) {
          if (isset($params["regex"])) {
        	   $status['regex'] = preg_match("/" . $params["regex"] . "/",$status['value']);
          }
        }
        $tbl[] = $status;
      }
      $app->render('login-failed-rctsaai.html.twig', [
        'attr_table' => $tbl,
        'description' => \Libs\Locale::getHtmlContent("login_failure")
      ]);
    }else{
      #not allowed to login with this provider
      $app->render('login-failed.html.twig', [
        'provider' => $kind
      ]);
    } */
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

  $session = Libs\AuthSession::getInstance();

  if ($session->isAuthenticated()) {

    /* TODO handle user logout on DB
    $user = Model::factory('User')->where('email',$session->getEmail())->find_one();

    if (($user != false) && (get_class($user) == "User")) {
      $user->in_session = false;
      $user->save();
      AppLog("logout", $user);
    }
    */
  }
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
