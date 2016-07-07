<?php

class AppLog extends Model {
  public static $_table = 'app_log';
  public static $_id_column = 'id';

  public function logEntityType() {
    return $this->has_one('logEntityType');
  }
}

class LogEntityType extends Model {
  public static $_table = 'log_entity_type';
  public static $_id_column = 'id';
}
