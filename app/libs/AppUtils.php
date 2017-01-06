<?php

namespace Libs;

class AppUtils{

  #compresses parameters into a single token for page load requests
  public static function compress_params($params){
    return self::base64url_encode(gzcompress(json_encode($params),9));
  }

  #decompresses a token into a set of parameters
  public static function decompress_params($token){
    return json_decode(gzuncompress(self::base64url_decode($token)));
  }

  public static function base64url_encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
  }

  public static function base64url_decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  #generates a n digit pin
  public static function generate_pin($length = 8) {
    $factory = new \RandomLib\Factory;
    $generator = $factory->getLowStrengthGenerator();
    #--with easy to speak letters
    #$alfabet = range('A', 'Z');
    #$consonants = array_diff($alfabet, array('Y', 'H', 'W', 'Q', 'C')) ;
    #$chars = implode('',$consonants);
    #--full
    #$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-#';
    #--only numbers
    $chars = '0123456789';
    $randomString = $generator->generateString($length, $chars);
    return $randomString;
  }

  /* Generates a random string from the provided text */
  public static function generate_rand_for($text = '', $length = 10, $url_friendly = true){
    if(strlen(trim($text)) < 5){
      $c_array = array_diff(range('a', 'z'), array('y', 'h', 'w', 'q', 'c')) ;
      $text = '0123456789_'.implode('',$c_array);
    }
    if($url_friendly){
      $text = preg_replace('/[^a-zA-Z0-9]+/', '', $text);
    }
    $factory = new \RandomLib\Factory;
    $generator = $factory->getLowStrengthGenerator();
    return $generator->generateString($length,$text);
  }

  #gets the short fdqn for the logged user
  public static function get_short_fqdn($session = ''){
    \FileLogger::debug('Calling get_short_fqdn');
    if(empty($session)){
      $session = AuthSession::getInstance();
    }
    if(!empty($session->getFQDN_AuthSourceId())){
      $ent_arr = explode('.',$session->getFQDN_AuthSourceId());
      //remove last element
      array_pop($ent_arr);
      //check first element
      if(strtolower($ent_arr[0]) == 'idp'){
        array_shift($ent_arr);
      }
      return implode('_',$ent_arr);
    }elseif (!empty($session->getOrganizationName())) { #create from org name
      $name = $session->getOrganizationName();
      #check for spaces
      if(preg_match('/([ ])+/',$name)){
        $names_arr = explode(' ',$name);
        $name = '';
        foreach ($names_arr as $name_part) {
          $name .= substr($name_part,0,1);
        }
      }
      return strtolower($name);
    }else{ #create from email
      //get email from EntityFQDN
      return self::get_short_fqdn_from_domain($session->getEntityFQDN());
    }
  }

  #gets the short fdqn from email domain
  public static function get_short_fqdn_from_domain($domain){
    \FileLogger::debug('Calling get_short_fqdn_from_email with email domain = '.$domain);
    $ent_arr = explode('.',$domain);
    //remove last element
    array_pop($ent_arr);
    return implode('.',$ent_arr);
  }

  public static function bytes_pretty_print($bytes) {

    if ($bytes < 1024) {
      return $bytes . " B";
    }

    $kbytes = $bytes / 1024;

    if ($kbytes < 1024) {
      return round($kbytes,3) . " KB";
    }

    $mbytes = $kbytes / 1024;

    if ($mbytes < 1024) {
      return round($mbytes,3) . " MB";
    }

    $tbytes = $mbytes / 1024;

    if ($tbytes < 1024) {
      return round($tbytes,3) . " TB";
    }

    $gbytes = $tbytes / 1024;

    return round($gbytes,3) . " GB";
  }

  public static function r_implode( $glue, $pieces, $convertToint = true )
  {
    $retVal = array();
    foreach( $pieces as $r_pieces )
    {
      switch (gettype($r_pieces)) {

        case "array":
                $retVal[] = "[" . self::r_implode( $glue, $r_pieces ) . "]";
              break;

        case "object":
                if (get_class($r_pieces) == "DateTime") {
                  $retVal[] = "new Date(" . $r_pieces->format('U') * 1000 . ")";
                  // print "new Date('" . $r_pieces->format('Y-m-d H:i:s') . "')";
                }
              break;

        case "string":
                if (is_numeric($r_pieces) && $convertToint) {
                  $retVal[] = $r_pieces;
                } else {
                  $retVal[] = "'" . $r_pieces . "'";
                }
              break;

        default:
           $retVal[] = $r_pieces;

      }
    }
    return implode( $glue, $retVal );
  }

}

 ?>
