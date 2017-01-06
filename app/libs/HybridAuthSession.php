<?
/*
* Singleton Hybrid Auth session manager
*/

namespace Libs;

class HybridAuthSession
{

  private $ha;
  private $session;
  private $authenticated;
  private $provider;
  private $adapter;

  private static $instance;

  private function __clone() {}

  private function __construct() {
    try{
      $this->ha = new \Hybrid_Auth( \SiteConfig::getInstance()->get('hauth_config') );
    }catch( Exception $e ){
      error_log('Error when creating HybridAuth: '.$e->getMessage());
      $this->ha = '';
    }
    $this->authenticated = false;
    $this->adapter = null;
    $this->session = null;
    $this->provider = null;
  }

  public static function getInstance() {

  	if (!self::$instance instanceof self) {
  		self::$instance = new self();
  	}
    if(empty(self::$instance->ha)){
      return '';
    }
  	return self::$instance;
  }

  public static function isValidProvider($provider){
    return !empty($provider) && in_array($provider,array(
      "google",
      "twitter",
      "facebook",
      "openid",
    ));
  }

  public function getUserProfile()
  {
    if(!empty($this->adapter)){
      return $this->adapter->getUserProfile();
    }
    return '';
  }

  /* tries to authenticate with the stored provider */
  public function authenticate($oid_url = ''){
    if (!empty($this->provider) && !empty($this->ha)) {
      try{
        if($this->provider == 'openid'){
          $this->adapter = $this->ha->authenticate($this->provider, array( "openid_identifier" => $oid_url));
        }else{
          $this->adapter = $this->ha->authenticate($this->provider);
        }
        // call Hybrid_Auth::getSessionData() to get stored data
        $this->session = $this->ha->getSessionData();
        //store data in cookies
        setcookie('_hauth_sdata',self::base64url_encode(gzcompress($this->session)), time() + (1200), "/");
        /* multiple cookies
        foreach ($session as $name => $value) {
          setcookie('_has_'.$name, $value, time() + (1200), "/");
        }
        */
      }catch( Exception $e ){
        error_log('Error when authenticating in HybridAuth: '.$e->getMessage());
      }
    }
  }

  /* checks if is authenticated */
  public function isAuthenticated(){
    if(empty($this->ha)){
      return false;
    }
    if(empty($this->session)){
      //try getting session data from cookies
      if(!empty($_COOKIE['_hauth_sdata'])){
        $this->session = gzuncompress(self::base64url_decode($_COOKIE['_hauth_sdata']));
      }
      /* multiple cookies
      $cookie_name = '_has_';
      foreach ($_COOKIE as $name => $value) {
        if (stripos($name,$cookie_name) === 0) {

        }
      }
      */
    }
    if(empty($this->provider) && isset($_COOKIE['_utoken_authprv']) && self::isValidProvider($_COOKIE['_utoken_authprv']) ){
      //try getting provider from cookie
      $this->provider = $_COOKIE['_utoken_authprv'];
    }
    if(empty($this->session) || empty($this->provider)){
      return false;
    }
    try{
      // then call Hybrid_Auth::restoreSessionData() to get stored data
       $this->ha->restoreSessionData($this->session);
       // call back an instance of adapter
       $this->adapter = $this->ha->getAdapter($this->provider);
       return true;
    }catch( Exception $e ){
      error_log('Error when validating HybridAuth: '.$e->getMessage());
      return false;
    }
  }

  /*
   * Returns array with the following session attributes:
   * - user_email (mandatory)
   * - user_friendly_name (mandatory)
   * - auth_source (mandatory)
   * - institution (optional)
  */
  public function getSessionAttributes(){
    //TODO
  }

  public function getAdapter(){
    return $this->adapter;
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

  /* logs out and cleans cookies */
  public function logout(){
    if($this->isAuthenticated()){
      try{
        $this->adapter->logout();
      }catch( Exception $e ){
        error_log('Error when logging out of HybridAuth: '.$e->getMessage());
      }
    }elseif(!empty($this->ha)){
      //force logout from all providers
      $this->ha->logoutAllProviders();
    }
    //clean cookies
    setcookie('_hauth_sdata', '', 0, '', "/");
  }

}
