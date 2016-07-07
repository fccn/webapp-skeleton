<?php

  require '../app/libs/SiteConfig.php';

  // Autoload
  require '../vendor/autoload.php';

  // Database connection
  require '../app/database.php';

  //get database kind - default to mysql
  $db_kind = \SiteConfig::getInstance()->get('db_kind');
  if(empty($db_kind)){
    $db_kind = "mysql";
  }
  //get config database
  $db = ORM::get_db();

echo "generating tables for $db_kind \n";

try{
  // get correct database create script
  if($db_kind == 'mysql'){
    require 'db/gen_mysql.php';
  }else{
    echo "unknown database type: $db_kind - nothing to be done \n";
  }
} catch (Exception $e) {
    echo 'script thrown exception: ',  $e->getMessage(), "\n";
}
