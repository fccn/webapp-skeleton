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

class FileLogger{

  private $logger;
  private static $instance;

  private function __construct() {
    $this->logger = new \Monolog\Logger('app_log');
    $log_lvl = '';
    switch (\SiteConfig::getInstance()->get('logfile_level')) {
      case 'DEBUG':
        $log_lvl = \Monolog\Logger::DEBUG;
        break;
      case 'ERROR':
        $log_lvl = \Monolog\Logger::ERROR;
        break;
      case 'INFO':
        $log_lvl = \Monolog\Logger::INFO;
        break;
      default:
        $log_lvl = \Monolog\Logger::WARNING;
        break;
    }
    // config log format
    $dateFormat = "Y-m-d H:i:s";
    $output = "[%level_name%]::%datetime%: %message% %context% %extra%\n";
    //create a formatter
    $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat);
    $stream = new \Monolog\Handler\StreamHandler(\SiteConfig::getInstance()->get('logfile_path'));
    $stream->setFormatter($formatter);
    $this->logger->pushHandler($stream, $log_lvl);
  }

  public static function getInstance() {
  	if (!FileLogger::$instance instanceof self) {
  		FileLogger::$instance = new self();
  	}
  	return FileLogger::$instance;
  }

  public static function error($message){
    FileLogger::getInstance()->logger->error($message);
  }

  public static function warn($message){
    FileLogger::getInstance()->logger->warning($message);
  }

  public static function debug($message){
    FileLogger::getInstance()->logger->debug($message);
  }

}
