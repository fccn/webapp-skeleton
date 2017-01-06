<?php
#--- configuration file skeleton
# create config.php file based on this one


date_default_timezone_set('Europe/Lisbon');

$fs_root = "/var/www"; #make sure there is no trailing / on path
$full_url = "http://localhost";

$c = array(

#------ paths
  "install_path"    => $fs_root,
  "base_path"       => "",
  "assets_path"     => "/assets",
  "locale_path" => "locale",
  "vendor_path"		=> "$fs_root/vendor",
  "node_mods_path"		=> "$fs_root/node_modules",
  "full_url" => $full_url,
  "default_css_url" => $full_url . "/css/embed.css",

#------ application logfile
  "logfile_path"   => $fs_root."/logs/application.log",
  "logfile_level"   => "WARNING", #options - "DEBUG", "INFO"


#------ email config
  "email_send_function" => "SMTP",
  "email_server_host" => "localhost",
  "email_server_port" => 25,
  "email_from_address" => "service.manager@fccn.pt",
  "email_from_name" => "Service Manager",
  "email_service_address" => "service@fccn.pt",
  "email_service_logo" => $fs_root . "/html/assets/ico/ms-icon-150x150.png",
  "email_message_top" => $fs_root . "/html/assets/imgs/top.png",
  "email_message_bottom" => $fs_root . "/html/assets/imgs/bottom.png",
  "email_message_spacer" => $fs_root . "/html/assets/imgs/spacer.png",
  "email_subject_prefix" => "Service: ",
  "email_msg_templates" => ['General'],

  "repeat-error-message" => 300, // Number of seconds before resending the send message

  "admin_list" => array("service@fccn.pt"),

#------ application specific config
  "mode"            => "development", # production
  "app_id"          => "",
  "app_name"        => "",
  "app_author"      => "",
  "app_title"       => "",
  "app_description" => "",

#------ database
  "db_host"         => "localhost",
  "db_name"         => "db_name",
  "db_username"     => "db_user",
  "db_password"     => "db_pass",
  "db_kind"         => "mysql",

#----- locale
  "defaultLocale"      => "en_GB",
  "defaultLocaleLabel" => "GB",

  "locales"            => array(
                            array("label" => "GB", "locale" => "en_GB", "flag_alt" => "English flag", "language" => "English"),
                            array("label" => "PT", "locale" => "pt_PT", "flag_alt" => "Portuguese flag", "language" => "Português"),
#                           array("label" => "HU", "locale" => "hu_HU", "flag_alt" => "Hugarian flag", "language" => "Magyar"),
#                           array("label" => "NO", "locale" => "nb_NO", "flag_alt" => "Norwegian flag", "language" => "Norsk"),
#                           array("label" => "FR", "locale" => "fr_FR", "flag_alt" => "French flag", "language" => "Francaise"),
#                           array("label" => "ES", "locale" => "es_ES", "flag_alt" => "Spanish flag", "language" => "Espagñol"),
#                           array("label" => "DE", "locale" => "de_DE", "flag_alt" => "German flag", "language" => "Deutch"),
#                           array("label" => "NL", "locale" => "nl_NL", "flag_alt" => "Dutch flag", "language" => "Nederlands"),
#                           array("label" => "IT", "locale" => "it_IT", "flag_alt" => "Italian flag", "language" => "Italiano")
# add other languages here....
                          ),

  "locale-textdomain"  => "messages",
  "locale-path"        => "locale",
  "locale-cookie-name" => "locale",

  "localeCookieName" => "locale",


#----- authentication
  "additional_auth_providers" => false, #(true|false) allow additional authentication methods besides RCTSaai
  "auth_session_id" => "fccn_service_authsess",
  "app-administrator-list" => array("service@fccn.pt"), #list of emails of admin users

  #--- SAML config (for RCTSaai)

  "saml_config" => array(
	  "ssp_base_path"    => "/opt/simplesaml",
	  "sp-default"       => "devel-userpass",
	  "gethostbyaddr" => true,

	  "sp-expected-attributes" => array(
  	  "eduPersonPrincipalName"      => array("mandatory" => 1, "regex" => "^([a-zA-Z0-9\-\.\'\+]+\@[a-zA-Z0-9\-\.]+)$"),
  	  "eduPersonScopedAffiliation"  => array("mandatory" => 1, "regex" => "(employee|staff|faculty|student|member)@([a-zA-Z0-9\-\.]+)$"),
  	  "mail"                        => array("mandatory" => 1, "regex" => "^([a-zA-Z0-9\-\.\'\+]+\@[a-zA-Z0-9\-\.]+)$"),
  		"displayName"                 => array("mandatory" => 0, "regex" => "(.+)"),
  		  "givenname"                   => array("mandatory" => 0, "regex" => "(.+)"),
  	   ),
    ),

  #--- Hybrid auth config (for social media logins) -- delete if social media logins not in auth_providers

  "hauth_config" => array(
    // "base_url" the url that point to HybridAuth Endpoint (where the index.php and config.php are found)
     "base_url" => "http://mywebsite.com/path/to/hybridauth/", #dont forget the trailing / on the end so Facebook auth works
     //list of available providers
     "providers" => array (
        // google
        "Google" => array ( // 'id' is your google client id
          "enabled" => false, //set to true to enable
           "keys" => array ( "id" => "", "secret" => "" ),
        ),
        // facebook
        "Facebook" => array ( // 'id' is your facebook application id
           "enabled" => false, //set to true to enable
           "keys" => array ( "id" => "", "secret" => "" ),
           "scope" => "email, user_about_me, user_birthday, user_hometown" // optional
        ),

        // twitter
        "Twitter" => array (
           "enabled" => false, //set to true to enable
           "keys" => array ( "key" => "****", "secret" => "****" )
        ),

        //openid
    		"OpenID" => array (
    			"enabled" => false //set to true to enable
    		)

		    //add more providers ---
    ),

	  "debug_mode" => true ,
       // to enable logging, set 'debug_mode' to true, then provide here a path of a writable file
    "debug_file" => $fs_root."/logs/hauth_debug.log",
    "allow_create" => false, #(true|false) allow create user from social account login
   ),

#----- misc

  # "google_analytics" => "UA-XXXXXXXX-X",
);
