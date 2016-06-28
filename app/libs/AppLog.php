<?php

function AppLog($label, $object, $param = null)
{
    if (is_object($object)) {
      $objectClassName = get_class($object);
    } else {
      $objectClassName = "Null";
    }
    $log = Model::factory('AppLog')->create();
    $log->label = $label;
    $log->timestamp = date( 'Y-m-d H:i:s', time() );
    $log->logEntityType_id = Model::factory('LogEntityType')->where("label", $objectClassName)->find_one()->get("id");

    if ($objectClassName != "Null") {
      $log->entity = json_encode($object->as_array());
      $log->entity_id = $object->id;
    }

    $session = Libs\SAMLSession::getInstance(false);
    if ($session->isAuthenticated()) {
      $log->user_id = $session->getUser()->id;
    } else {
      $log->user_id = null;
    }

    $log->param = $param;
    $log->save();

}
