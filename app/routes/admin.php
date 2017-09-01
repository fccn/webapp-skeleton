<?php

$app->get('/', function () use ($app) {

    $ss = Libs\AuthSession::getInstance();

    if ($ss->isAdmin()) {
        $app->render('admin.html.twig',[]);
    } else {
      $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/profile/me");
    }

});

$app->get('/stats', function () use ($app) {

    $ss = Libs\AuthSession::getInstance();

    if ($ss->isAdmin()) {

      $sql = 'select t.label tlabel, l.label llabel, count(*) cnt from app_log l, log_entity_type t where t.id = l.log_entity_type_id group by t.label, l.label';

      $s = array();
      $statistics = ORM::for_table('AppLog')
        ->raw_query($sql)
        ->find_many();

      foreach ($statistics as $stat) {
        if($stat["tlabel"] && $stat["tlabel"] != null && $stat["tlabel"] != 'Null'){
          $s[$stat["tlabel"]][$stat["llabel"]] = $stat["cnt"];
        }else{
          $s['General'][$stat["llabel"]] = $stat["cnt"];
        }
      }
      \FileLogger::debug("s = ".print_r($s,true));

      $app->render('global_stats.html.twig', [
        "global_statistics" => $s
      ]);
    } else {
      $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/profile/me");
    }

});

$app->get('/configfile', function () use ($app) {

  $ss = Libs\AuthSession::getInstance();
  if ($ss->isAdmin()) {
    $app->render('config_file.html.twig', [

    ]);
  } else {
    $app->redirect(\SiteConfig::getInstance()->get("base_path") . "/profile/me");
  }

});

# /admin/sysinfo
$app->get('/sysinfo', function () use ($app) {
  $ss = Libs\AuthSession::getInstance();

  ob_start();
  phpinfo();
  $phpinfo = ob_get_clean();

  if ($ss->isAdmin()) {
    $app->render('sysinfo.html.twig', [
      "php_info" => $phpinfo
    ]);
  }else{#send to unauthorized
    sendToUnauthorized();
  }
});

#get /admin/sendmail
$app->get('/sendmail', function () use ($app) {
  \FileLogger::debug('GET /admin/sendmail');
  $ss = Libs\AuthSession::getInstance();

  if ($ss->isAdmin()) {
    $app->render('adm_sendmail.html.twig', [
      "from" => \SiteConfig::getInstance()->get('email_from_address'),
      "msg_templates" => array_keys(\SiteConfig::getInstance()->get('email_msg_templates')),
    ]);
  }else{#send to unauthorized
    sendToUnauthorized();
  }
});

#post /admin/sendmail
$app->post('/sendmail', function () use ($app) {
  \FileLogger::debug('POST /admin/sendmail');
  $ss = Libs\AuthSession::getInstance();
  $request = $app->request();

  if ($ss->isAdmin()) {
    $mailer = new \WebMailer;
    //get POST data
    //$template = $request->post("template");
    $to = $request->post("to");
    $bcc = $request->post("bcc");
    $subject = $request->post("subject");
    $body = $request->post("msgbody");

    //add subject
    $mailer->Subject = $subject;
    //construct message body
    $mailer->constructBrandedMessage("<br/>" . $body . "<br/><br/>");
    $status = $mailer->sendTo($to,$bcc);

    $app->render('adm_sendmail.html.twig', [
      "from" => \SiteConfig::getInstance()->get('email_from_address'),
      "msg_templates" => array_keys(\SiteConfig::getInstance()->get('email_msg_templates')),
      "to" => $to,
      "bcc" => $bcc,
      "subject" => $subject,
      "msgbody" => $body,
      "status" => $status
    ]);

  }else{#send to unauthorized
    sendToUnauthorized();
  }
});
