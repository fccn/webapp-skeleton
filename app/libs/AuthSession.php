<?php
/*
* Singleton user session manager
*  Users can authenticate via SimpleSAML or HybridAuth
*
*  Centralized authentication validator
*  that uses SAMLSession and HybridAuthSession to manage users
*/
namespace Libs;

require_once 'SAMLSession.php';
require_once 'HybridAuthSession.php';



class AuthSession extends \Slim\Middleware
{
  private $auth;
  private $authenticated;
  private $user;
  private static $instance;

  private function __clone() {}

  public function __construct() {
    $this->authenticated = false;
    $this->user = null;
  }

  public static function getInstance($enforce_auth = true) {
  	if (!self::$instance instanceof self) {
  		self::$instance = new self();
  	}

    self::$instance->requireAuth($enforce_auth);
  	return self::$instance;
  }

  # slim middleware function
  public function call()
  {
    session_start();
    $app = \Slim\Slim::getInstance();
    $session = self::getInstance(false);

    //TODO check attributes
    $session_info = $session->getSessionAttributes();
    $attr = array();
    foreach ($session_info as $key => $value) {
      $attr[$key] = $value;
    }
    $attr["admin"] = $this->isAdmin();
    $attr["authenticated"] = $this->isAuthenticated();
    $app->view()->set("ss", $attr);
    $this->next->call();
  }

  #check authentication status and enforce authentication
  public function requireAuth($enforce_auth = true)
  {
    $this->authenticated = $this->isAuthenticated();
    if (($enforce_auth) && (!$this->authenticated)) {
      //TODO show authentication page
      $this->authenticated = $this->isAuthenticated();
    }
  }

  /*
   * Returns array with the following session attributes:
   * - user_email (mandatory)
   * - user_friendly_name (mandatory)
   * - auth_source (mandatory)
   * - institution (optional)
  */
  public function getSessionAttributes()
  {
    if($this->validateAuthProvider()){
      return $this->auth->getSessionAttributes();
    }
    return [];
  }

  public function getUser(){
    if(empty($this->user)){
      $s_data = $this->getSessionAttributes();
      if(empty($s_data)){
        return null;
      }
      $user = \User::find_by_email($s_data['user_email']);
      if(!empty($user)){
        return $user;
      }
    }
    return $this->user;
  }

  public function isAuthenticated()
  {
    //check session var
    if(isset($_SESSION) && !empty($_SESSION[\SiteConfig::getInstance()->get('auth_session_id')])){
      return !empty($_SESSION[\SiteConfig::getInstance()->get('auth_session_id')]['authenticated']);
    }
    //check authenticator
    if($this->validateAuthProvider()){
      return $this->auth->isAuthenticated();
    }
    return false;
  }

  public function isAdmin()
  {
    if($this->isAuthenticated()){
      $s_attr = $this->getSessionAttributes();
      if(!empty($s_attr)){
        return in_array($s_attr['user_email'], \SiteConfig::getInstance()->get('app-administrator-list'));
      }
    }
  	return false;
  }

  public function authenticate($provider = 'rctsaai', $oid_url = ''){
    \FileLogger::debug('Authenticate using '.$provider);
    $this->setAuthProvider($provider,true);
    if(empty($this->auth)){
      return false;
    }
    //try authenticating
    $this->auth->authenticate();
    \FileLogger::debug("User is authenticated? ".$this->auth->isAuthenticated());
    //set user only if authenticated
    if ($this->auth->isAuthenticated()) {
      $uuid = '';
      if(empty($this->user)){
        $s_data = $this->auth->getSessionAttributes();
        $user = \User::find_by_email($s_data['user_email']);
        $already_authenticated = false;
        if (empty($user) && $this->canCreateUser($provider)) {
          //create user
          $user = \User::create_from_session([
            'email' => $s_data['user_email'],
            'name' => $s_data['user_friendly_name'],
            'auth_id' => $s_data['auth_source'],
          ]);
        } elseif (empty($user)){
          //cannot create user
          //force logout from auth
          $this->auth->logout();
        } else {
          $already_authenticated = $user->in_session;
        }

        //check if user is allowed
        if(!empty($user)){
          if ($already_authenticated == false) {
            $user->update_authdata();
            AppLog("login", $user);
          }
          $this->user = $user;
        }
      }
      if(!empty($this->user)){
        $uuid = $this->user->email;
      }
      //set session var
      $_SESSION[\SiteConfig::getInstance()->get('auth_session_id')] = array(
        'provider' => $provider,
        'authenticated' => $this->auth->isAuthenticated(),
        'uuid' => $uuid
      );
    }
  }

  #-- logout user - accepts return URL as optional param
  public function logout($return_to=''){
    \FileLogger::debug("user logging out...");
    #if($this->validateAuthProvider()){
      //logout from provider
    #  $this->auth->logout();
    #}
    //clear session data
    if(isset($_SESSION)){
      \FileLogger::debug("clearing session data");
      session_unset();
    }
  }

 public function listAttributeErrors(){
    if($this->validateAuthProvider()){
      return $this->auth->listAttributeErrors();
    }
    return [];
 }

 public function canCreateUser($provider){
   if($provider == AuthProvider::$RCTSAAI){
     #-- RCTSaai can always create user
     return true;
   }
   if(empty(\SiteConfig::getInstance()->get('additional_auth_providers'))){
     #-- no additional auth providers, cannot create user
     return false;
   }
   #-- check if social media accounts can create user
   if(!empty(\SiteConfig::getInstance()->get('hauth_config'))){
     return !empty(\SiteConfig::getInstance()->get('hauth_config')['allow_create']);
   }
   return false;
 }

#----- helpers

  /* verifies if $this->auth param is set and if not tries to set it using
   * info from session validator
   * Returns false if there is no way to get authentication provider,
   * true if $this->auth is not empty
   */
  private function validateAuthProvider(){
    if(empty($this->auth)){
      #\FileLogger::debug('Auth provider not found, trying to set');
      if(!isset($_SESSION)){
        #start session if there are no session vars
        session_start();
      }
      if(!empty($_SESSION[\SiteConfig::getInstance()->get('auth_session_id')]) && !empty($_SESSION[\SiteConfig::getInstance()->get('auth_session_id')]['provider'])){
        $this->setAuthProvider($_SESSION[\SiteConfig::getInstance()->get('auth_session_id')]['provider']);
      }
    }
    return !empty($this->auth);
  }

  /* sets $this->auth param -- the authentication provider
   * @param_name the given provider name
   */
  private function setAuthProvider($provider_name,$force_auth = false){
    \FileLogger::debug("Setting auth provider $provider_name. Force authentication: $force_auth");
    switch ($provider_name) {
      case AuthProvider::$RCTSAAI:
        $this->auth = SAMLSession::getInstance($force_auth);
        break;
      case AuthProvider::$GOOGLE:
      case AuthProvider::$FACEBOOK:
      case AuthProvider::$TWITTER:
      case AuthProvider::$OPENID:
        $this->auth = HybridAuthSession::getInstance($force_auth);
        break;
      default:
        #nothing to
        \FileLogger::warn('Unknown authentication provider: '.$provider_name);
        break;
    }
  }

}

class AuthProvider
{
  public static $RCTSAAI = 'rctsaai';
  public static $GOOGLE = 'google';
  public static $FACEBOOK = 'facebook';
  public static $TWITTER = 'twitter';
  public static $OPENID = 'openid';

  public static function isHybridAuth($provider){
    return in_array($provider,[self::$GOOGLE,self::$FACEBOOK,self::$TWITTER,self::$OPENID]);
  }
}

?>
