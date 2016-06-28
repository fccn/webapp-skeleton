<?php

class AppLog extends Model {
  public static $_table = 'appLog';
  public static $_id_column = 'id';

  public function logEntityType() {
    return $this->has_one('LogEntityType');
  }
}

class LogEntityType extends Model {
  public static $_table = 'logEntityType';
  public static $_id_column = 'id';
}
