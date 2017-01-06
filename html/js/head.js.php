<?
# loads head.js from node-modules folder

require_once '../../app/libs/SiteConfig.php';

header('Content-type: text/javascript');
echo file_get_contents(\SiteConfig::getInstance()->get("node_mods_path").'/headjs/dist/1.0.0/head.min.js');
?>
