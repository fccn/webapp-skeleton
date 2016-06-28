<?php

$app->get('/getVersion', function() use ($app) {
  $result = array("version" => 1);
  $app->render(200, array("result" => $result));
});
