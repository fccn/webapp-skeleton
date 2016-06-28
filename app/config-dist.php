<?php
#--- configuration file skeleton
# create config.php file based on this one


date_default_timezone_set('Europe/Lisbon');

$fs_root = "/var/www";
$full_url = "http://localhost";

$c = array(

#------ paths
  "install_path"    => $fs_root,
  "base_path"       => "/webtut",
  "full_url" => $full_url,
  "default_css_url" => $full_url . "/css/embed.css",

#------ email config
  "email_server_host" => "localhost",
  "email_server_port" => 25,
  "email_from_address" => "rui.ribeiro@fccn.pt",
  "email_from_name" => "Rui Ribeiro",
  "email_service_address" => "service@fccn.pt",
  "email_service_logo" => $fs_root . "/html/assets/ico/ms-icon-150x150.png",
  "email_message_top" => $fs_root . "/html/assets/imgs/top.png",
  "email_message_bottom" => $fs_root . "/html/assets/imgs/bottom.png",
  "email_message_spacer" => $fs_root . "/html/assets/imgs/spacer.png",
  "email_subject_prefix" => "Service: ",

  "repeat-error-message" => 300, // Number of seconds before resending the send message

  "admin_list" => array("rui.ribeiro@fccn.pt","paulo.costa@fccn.pt"),

#------ application specific config
  "mode"            => "development", # production
  "app_id"          => "",
  "app_name"        => "",
  "app_author"      => "",
  "app_title"       => "",
  "app_description" => "",

#------ database
  "db_host"         => "localhost",
  "db_name"         => "webtut",
  "db_username"     => "root",
  "db_password"     => "fccn2015",

#----- locale
  "defaultLocale"      => "en_GB",
  "defaultLocaleLabel" => "GB",

  "locales"            => array(
                            array("label" => "GB", "locale" => "en_GB", "flag_alt" => "English flag", "language" => "English"),
                            array("label" => "PT", "locale" => "pt_PT", "flag_alt" => "Portuguese flag", "language" => "Português"),
                            array("label" => "HU", "locale" => "hu_HU", "flag_alt" => "Hugarian flag", "language" => "Magyar"),
                            array("label" => "NO", "locale" => "nb_NO", "flag_alt" => "Norwegian flag", "language" => "Norsk"),
                            array("label" => "FR", "locale" => "fr_FR", "flag_alt" => "French flag", "language" => "Francaise"),
                            array("label" => "ES", "locale" => "es_ES", "flag_alt" => "Spanish flag", "language" => "Espagñol"),
                            array("label" => "DE", "locale" => "de_DE", "flag_alt" => "German flag", "language" => "Deutch"),
                            array("label" => "NL", "locale" => "nl_NL", "flag_alt" => "Dutch flag", "language" => "Nederlands"),
                            array("label" => "IT", "locale" => "it_IT", "flag_alt" => "Italian flag", "language" => "Italiano")
                          ),

  "locale-textdomain"  => "messages",
  "locale-path"        => "locale",
  "locale-cookie-name" => "locale",

  "localeCookieName" => "locale",

#----- authentication
  "ssp_base_path"    => "/opt/simplesamlphp",
  "sp-default"       => "example-userpass",

  "sp-expected-attributes" => array(
      "eduPersonPrincipalName"      => array("mandatory" => 1, "regex" => "^([a-zA-Z0-9\-\.\'\+]+\@[a-zA-Z0-9\-\.]+)$"),
      "eduPersonScopedAffiliation"  => array("mandatory" => 1, "regex" => "(employee|staff|faculty|student|member)@([a-zA-Z0-9\-\.]+)$"),
      "mail"                        => array("mandatory" => 1, "regex" => "^([a-zA-Z0-9\-\.\'\+]+\@[a-zA-Z0-9\-\.]+)$"),
  		"displayName"                 => array("mandatory" => 0, "regex" => "(.+)"),
		  "givenname"                   => array("mandatory" => 0, "regex" => "(.+)"),
  	),

  "app-administrator-list" => array("rui.ribeiro@fccn.pt","paulo.costa@fccn.pt"),

  "gethostbyaddr" => true,

  "token_hash"    => "aslkdjf4oijlqwkejlakjdfkfugwiueyr",

  #"backend_protocol"     => "https",
  #"backend_host"         => "webrtc-hub.fccn.pt",
  #"backend_port"         => 8095,
  #"backend_peerjs_path"  => "/webtut",
  #"backend_api_path"     => "/api",

  #"stun_turn_rest_api_url" => "https://brain.lab.vvc.niif.hu/restapi/stun?",
  #"stun_turn_rest_api_key" => "gnThM3sJJzAcMZyvn8nSyyLaFUXBrwPj",

  #"callstats_app_id" => 107423264,
  #"callstats_app_secret" => "zwBsiEKP3VSTjJQeWEjr9eE05VA=",

  //"google_analytics" => "UA-74325233-1",
);
