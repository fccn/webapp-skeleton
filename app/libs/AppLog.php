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
    $entityType = Model::factory('LogEntityType')->where("label", $objectClassName)->find_one();
    if(empty($entityType)){
      //create new entity type
      $entity = Model::factory('LogEntityType')->create();
      $entity->label = $objectClassName;
      $entity->save();
      $entityType = Model::factory('LogEntityType')->where("label", $objectClassName)->find_one();
    }
    $log->log_entity_type_id = $entityType->get("id");

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
