<?php
/*
* This is a sample of the main configuration file. Place all required configuration settings
* here with dummy values. When publishing the site copy this file to config.php and
* place the real configuration settings there.
*
* The config.php file is listed in .gitignore so you can put sensitive configuration values there
* without them being stored in git.
*/

//common vars to be used on file
$fs_root = "/opt/webapp_skeleton_v2"; //point to the project root folder
$app_env = "development"; //possible options 'development','production','staging'
$full_url = "http://devel-stv.fccn.pt:9090"; //public url of the site

$templates_path =  __DIR__ . '/../templates';
$cache_path = __DIR__ . '/../cache';

$log_level = "DEBUG";
if ($app_env == 'production') {
    $log_level = "WARNING";
}

//the configuration array - make sure it is called $c
$c = array(
//-------- common application settings -------------------
//- path settings
  "install_path"    => $fs_root,
  "base_path"       => "",
  "assets_path"     => "/assets",
  "full_url" => $full_url,
//- application settings
  "app_id"          => "adkleio",
  "app_name"        => "webapp_skeleton",
  "app_author"      => "Paulo Costa",
  "app_title"       => "Webapp Skeleton",
  "app_description" => "A Slim Framework skeleton application",
//- slim settings
  'app_mode' => $app_env,
  'slim_settings' => [
      'displayErrorDetails' => $app_env == "production" ? false : true,
      'addContentLengthHeader' => false, // Allow the web server to send the content-length header
      'debug' => $app_env == "production" ? false : true,
    ],

  'twig_templates_path' => $templates_path,
  'twig_cache_path' => $cache_path,
  'twig_debug' => $app_env == "production" ? false : true,

#  'log_level' => $log_level,
#  'slim_log_path' => __DIR__ . '/../logs/slim.log',
#  'app_log_path' => __DIR__ . '/../logs/application.log',

//--------     logging            --------------------------

  "logfile_path"    => __DIR__ . '/../logs/application.log',
  "logfile_level"  => $log_level,

//--------     Web utilities settings      -------------------

  "vendor_path"     => __DIR__."/../vendor/",
  "node_mods_path"  => __DIR__."/../node_modules/",

//--------     locale settings           -------------------
  "defaultLocale"      => "pt_PT",          # Setup the default locale
  "defaultLocaleLabel" => "PT",             # and default locale label

   #- array of available locales
   "locales"            => array(
                               array("label" => "GB", "locale" => "en_GB", "flag_alt" => "English flag", "language" => "English"),
                               array("label" => "PT", "locale" => "pt_PT", "flag_alt" => "Portuguese flag", "language" => "Português"),
                               # add other languages here....
                               ),

   "locale_textdomain"  => "messages",
   "locale_path"        => __DIR__ . "/../locale", #path of the locale folder
   "locale_cookie_name" => "locale",    #name of the cookie to store locale information

   #-twig parser configurations
   "twig_parser_templates_path" => $templates_path,   #path for twig templates folder
   "twig_parser_cache_path" => $cache_path,            #path for cache folder

//-------- additional settings -------------------

);
