<?

# utils/user/*

$app->get('/check', function () use ($app) {

  $session = Libs\SAMLSession::getInstance();
  #allow only admins to get access to user info
  if ($session->isAdmin()) {
    $app->render('userCheckForm.html.twig', [
      "userinfo" => ""
    ]);
  } else { #send to unauthorized
    sendToUnauthorized();
  }

});

$app->post('/check', function() use ($app){

  $request = $app->request();
  $session = Libs\SAMLSession::getInstance();

  if ($session->isAdmin()) {

    $is_exact = $request->post("is_exact");
    $userinfo = $request->post("userinfo");
    $userlist = "";

    if($is_exact == 'on'){
      $userlist = \Model::factory('User')
        ->where_any_is(array(
          array('name' => $userinfo),
          array('email' => $userinfo)
        ))
        ->find_many();
    }else{
      $userinfo_q = $userinfo.'%';
      $userlist = \Model::factory('User')
        ->where_raw('(`name` LIKE ? OR `email` LIKE ?)',
          array($userinfo_q, $userinfo_q)
        )
        ->find_many();
    }
    //list users from database

    $app->render('userCheckForm.html.twig', [
      "userinfo" => $userinfo,
      "is_exact" => $is_exact,
      "userlist" => $userlist
    ]);
  }else { #send to unauthorized
    sendToUnauthorized();
  }

});

$app->get('/create', function () use ($app) {
  $session = Libs\SAMLSession::getInstance();
  if($session->isAdmin()){
    $app->render('userCreateForm.html.twig');
  }else { #send to unauthorized
    sendToUnauthorized();
  }
});

$app->post('/create', function () use ($app) {
  $request = $app->request();
  if($session->isAdmin()){
    $user_name = $request->post("name");
    $user_email = $request->post("email");

    $session = Libs\SAMLSession::getInstance();
    $user = \Model::factory('User')->where('email', $user_email)->find_one();
    if(!empty($user)){
      //user already exists, show error
      $app->render('userCreateForm.html.twig',[
        'name' => $user_name,
        'email' => $user_email,
        'user_exists' => true
      ]);
    }else{
      //create the new user
      $user = \Model::factory('User')->create();
      $user->email = $user_email;
      $user->name = $user_name;
      $user->localuser = 1;
      $user->session_count = 0;
      $user->auth_source = 'local';
      $user->created_at = date( 'Y-m-d H:i:s', time() );
      $user->save();

      $user_data = $user->as_array();

      AppLog("userCreateLocal", $user);
      $app->render('userCreateSuccess.html.twig',[
        'user_data' => $user_data
      ]);
    }
  }else { #send to unauthorized
    sendToUnauthorized();
  }
});

$app->get('/me', function () use ($app) {
  $session = Libs\SAMLSession::getInstance();
  $user = $session->getUser();

  $app->render('user_profile.html.twig',[
    'user_data' => $user->as_array(),
  ]);

});
?>
