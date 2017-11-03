<?php
/*
* Loads configurations to the web application
* Loaders are specified in static functions inside the ConfigLoader class
*/

namespace Fccn\Lib;

class ConfigLoader
{

  /*
  * Loads additional twig filters and extensions
  */
  public static function loadTwigFiltersAndExtensions($twig){
    //---additional extensions from http://twig-extensions.readthedocs.io/en/latest/
    #load internationalization extensions and filters
    TranslateConfigurationLoader::loadTwigConfigs($twig);

    #additional extensions TODO add this to a class in twig-utils project
    $twig->addExtension(new \JSW\Twig\TwigExtension());
    $twig->addExtension(new \Twig_Extension_Debug());
  }


  /*
  * Loads variables to twig that are accessible from all templates
  */
  public static function loadTwigGlobals($twig){
    $twig->addGlobal('config', SiteConfig::getInstance()->all());
    $twig->addGlobal('current_year', date('Y'));
  }

}
