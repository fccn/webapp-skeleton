<?
/*
 * loads external javascript libs defined in composer and npm
 */
require_once '../../app/libs/SiteConfig.php';

header('Content-type: text/javascript');
if($lib = $_GET['lib']){
  switch ($lib) {
    case 'jquery':
      echo file_get_contents(\SiteConfig::getInstance()->get('vendor_path').'/components/jquery/jquery.min.js')."\n";
      break;
    case 'bootstrap':
      echo file_get_contents(\SiteConfig::getInstance()->get('vendor_path').'/components/bootstrap/js/bootstrap.min.js')."\n";
      break;
    case 'moment':
      echo file_get_contents(\SiteConfig::getInstance()->get('vendor_path').'/moment/moment/min/moment-with-locales.min.js')."\n";
      break;
    case 'datetimepicker':
      echo file_get_contents(\SiteConfig::getInstance()->get('vendor_path').'/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')."\n";
      break;
    case 'bootbox':
        echo file_get_contents(\SiteConfig::getInstance()->get('node_mods_path').'/bootbox/bootbox.min.js')."\n";
        break;
    case 'chart_js':
      echo file_get_contents(\SiteConfig::getInstance()->get('node_mods_path').'/chart.js/dist/Chart.min.js')."\n";
      break;
    case 'datatables_net':
        echo file_get_contents(\SiteConfig::getInstance()->get('node_mods_path').'/datatables.net/js/jquery.dataTables.js')."\n";
        break;
    case 'datatables_bs_net':
        echo file_get_contents(\SiteConfig::getInstance()->get('node_mods_path').'/datatables.net-bs/js/dataTables.bootstrap.js')."\n";
        break;
    case 'cookieconsent':
        echo file_get_contents(\SiteConfig::getInstance()->get('node_mods_path').'/cookieconsent/build/cookieconsent.min.js')."\n";
        break;
    #--- Add project specific external libs from here on -----
    default:
      # code...
      break;
  }
}
