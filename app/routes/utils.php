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

#-- show select login page. if only one login type is available then redirect directly to it
$app->get('/select_login', function() use ($app) {
  FileLogger::debug('GET /utils/select_login');
  $rto = $app->request()->get('rto');
  FileLogger::debug("rto: $rto");

  if(!empty(\SiteConfig::getInstance()->get('additional_auth_providers'))){
    \FileLogger::debug("rendiring login selection page with rto=$rto");
    $app->render('select_login.html.twig', [
      'description' => \Libs\Locale::getHtmlContent("select_login_intro"),
      'rto' => $rto
    ]);
  }else{
    \FileLogger::debug("redirecting to rctsaai login with rto=$rto");
    //go directly to rctsaai login
    $app->redirect($app->urlFor('user.login',array('kind' => 'rctsaai'))."?rto=$rto");
  }

})->name('user.select_login');

#-- login user
$app->get('/login/:kind', function ($kind) use ($app) {
  \FileLogger::debug("GET /utils/login/$kind");
  //get valid auth schemes
  $valid_auths = [Libs\AuthProvider::$RCTSAAI];
  $social_auths = \SiteConfig::getInstance()->get('hauth_config');
  //handle redirects
  $redirect_url = $app->request()->get('rto');
  if(empty($redirect_url)){
    $redirect_url = '/';
  }
  \FileLogger::debug("login redirect_url: $redirect_url");
  #\FileLogger::debug('social auths: '.print_r($valid_auths,true));
  foreach ($social_auths['providers'] as $sa_name => $sa_status) {
    if(!empty($sa_status['enabled'])){
      #\FileLogger::debug('sa_name: '.$sa_name.' status '.print_r($sa_status,true));
      array_push($valid_auths, strtolower($sa_name));
      #\FileLogger::debug('valid auths after: '.print_r($valid_auths,true));
    }
  }
  \FileLogger::debug('valid auths: '.print_r($valid_auths,true));
  //get session instance
  $session = Libs\AuthSession::getInstance(false);
  $pre_login_url = $app->urlFor('user.login',array('kind' => 'pre'));
  $from_pre = $app->request()->get('from_pre');
  $referrer = $app->request()->getReferrer();
  //$from_login_message_page = strpos($referrer, $pre_login_url) !== false;
  FileLogger::debug("login referrer: $referrer");
  if($session && $session->isAuthenticated()){
    //user is already authenticated
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . $redirect_url);
  }elseif($kind == 'rctsaai' && !isset($_COOKIE["dont_show_prelogin_message_again"]) && empty($from_pre)){
    //show pre-login message for rctsaai authentication
    \FileLogger::debug("redirecting to pre login message page...");
    $app->redirect($app->urlFor('user.login',array('kind' => 'pre'))."?rto=$redirect_url");
  }elseif(in_array($kind,$valid_auths)) {
    if($session){
      \FileLogger::debug('try authenticate user');
      $session->authenticate($kind);
      \FileLogger::debug('tried authenticate user');
      if($session->isAuthenticated()){
        \FileLogger::debug('user authenticated '.$kind);
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
    }else{
      #unable to login with this provider
      $app->render('login_failed.html.twig', [
        'provider' => $kind
      ]);
    }
  }elseif ($kind == 'pre') {
    $login_url = $app->request()->get('login_url');
    if(empty($login_url)){
      $login_url = $app->urlFor('user.login',array('kind' => 'rctsaai'));
    }
    #get redirect to url
    $redirect_url = $app->request()->get('rto');
    #render pre-login message
    $app->render('pre_login.html.twig', [
      'pre_login_message' => \Libs\Locale::getHtmlContent("pre_login_message"),
      'login_url' => $login_url,
      'redirect_url' => $redirect_url,
    ]);
  }else{
    \FileLogger::debug("Provider $kind not supported");
    #provider not supported
    $app->render('login_failed.html.twig', [
      'msg' => _('Provider not supported'),
      'provider' => $kind
    ]);
  }
})->name('user.login')->conditions(array('kind' => '\w+'));

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
