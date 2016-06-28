<?php

  require '../app/libs/SiteConfig.php';

  // Autoload
  require '../vendor/autoload.php';

  // Database connection
  require '../app/database.php';
  
  $tplDir = dirname(__FILE__).'/../templates';
  $tmpDir = dirname(__FILE__).'/../cache/';
  $loader = new Twig_Loader_Filesystem($tplDir);

  // force auto-reload to always have the latest version of the template
  $twig = new Twig_Environment($loader, array(
    'cache' => $tmpDir,
    'auto_reload' => true
  ));
  
  $twig->addExtension(new Twig_Extensions_Extension_I18n());
  // configure Twig the way you want

  $twig->addExtension(new \Slim\Views\TwigExtension());
  $twig->addExtension(new \JSW\Twig\TwigExtension());
  $twig->addExtension(new Twig_Extension_Debug());

  $filter  = new Twig_SimpleFilter("cast_to_array", function($stdClassObject) {    
    return null;
  });
  $twig->addFilter($filter);

  $filter  = new Twig_SimpleFilter("translate", function($stdClassObject) {        
    return null;
  });
  
  $twig->addFilter($filter);

  $filter  = new Twig_SimpleFilter("type", function($stdClassObject) {        
    return null;
  });
  $twig->addFilter($filter);
        
  // iterate over all your templates
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tplDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
  {
    // force compilation
    if ($file->isFile()) {
      $twig->loadTemplate(str_replace($tplDir.'/', '', $file));
    }
  }
  
  $php =fopen("../cache/db_dump.php", "w");
  fprintf($php, "<?php\n");

  fprintf($php, "\$a = _('anonymous');\n");

  # Iterates thru all the locales
  foreach(SiteConfig::getInstance()->get("locales") as $a) {
    fprintf($php, "\$a = _('" . $a["flag_alt"] . "');\n");
  }   
  
  fclose($php);
  
   