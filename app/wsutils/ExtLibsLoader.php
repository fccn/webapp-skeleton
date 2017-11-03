<?php
/*
* Singleton class to load external web resources located in
*/

namespace Fccn\WsUtils;

class ExtLibsLoader
{

  private static $instance;
  private $libraries;

  public function __construct()
  {
    $config = \Fccn\Lib\SiteConfig::getInstance();
    //load library paths here
    $this->libraries = array(
      "headjs" => $config->get('node_mods_path')."/headjs/dist/1.0.0/head.min.js",
      "jquery" => $config->get('vendor_path').'/components/jquery/jquery.min.js',
      "moment" => $config->get('vendor_path').'/moment/moment/min/moment-with-locales.min.js',
      "bootstrap" => $config->get('vendor_path').'/components/bootstrap/js/bootstrap.min.js',
      "bootbox" => $config->get('node_mods_path').'/bootbox/bootbox.min.js',
      "datetimepicker" => $config->get('vendor_path').'/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
      "cookieconsent" => $config->get('node_mods_path').'/cookieconsent/build/cookieconsent.min.js',
      "datatables_net" => $config->get('node_mods_path').'/datatables.net/js/jquery.dataTables.js',
      "datatables_net_bs" => $config->get('node_mods_path').'/datatables.net-bs/js/dataTables.bootstrap.js',
      "chartjs" => $config->get('node_mods_path').'/chart.js/dist/Chart.min.js',
    );
  }

  public static function getInstance()
  {
    if (!ExtLibsLoader::$instance instanceof self) {
      ExtLibsLoader::$instance = new self();
    }

    return ExtLibsLoader::$instance;
  }

  /*
  * checks if library is registered and file exists
  */
  public function exists($lib_name){
    if(isset($this->libraries[$lib_name])){
      $path = $this->libraries[$lib_name];
      \Fccn\Lib\FileLogger::debug("ExtLibsLoader::exists there is a record for library [$lib_name], located in: $path");
      return file_exists($path);
    }
    return false;
  }

  public function load($lib_name){
    if($this->exists($lib_name)){
      return file_get_contents($this->libraries[$lib_name]);
    }
    return false;
  }

  public function add($lib_name,$path){
    if(!file_exists($path)){
      \Fccn\Lib\FileLogger::error("ExtLibsLoader::add - Could not find library - $lib - in path: $path");
    }
    $libraries[$libname] = $path;
  }


}
