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
  private $session_config;
  private static $instance;

  private function __clone() {}

  public function __construct() {
    $this->authenticated = false;
    $this->user = null;
    $this->session_config = array(
      "session_id" => \SiteConfig::getInstance()->get('auth_session_id'),
      "more_auth_providers" => \SiteConfig::getInstance()->get('additional_auth_providers'),
      "allow_create_social_auth" => !empty(\SiteConfig::getInstance()->get('hauth_config')) && \SiteConfig::getInstance()->get('hauth_config')['allow_create'],
      "app_admins" => \SiteConfig::getInstance()->get('app-administrator-list'),
    );
  }

  public function getAuth(){
    return $this->auth;
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
    \FileLogger::debug('calling AuthSession::requireAuth');
    $this->authenticated = $this->isAuthenticated();
    if (($enforce_auth) && (!$this->authenticated)) {
      #\FileLogger::debug('AuthSession::requireAuth - user not authenticated');
      //check if there is an authentication provider
      if($this->validateAuthProvider() && empty($this->session_config['more_auth_providers'])){
        //validate using SAML provider
        $this->auth->requireAuth(true);
      }else{
        //redirect to authentication page
        $app = \Slim\Slim::getInstance();
        $app->redirect(\SiteConfig::getInstance()->get("base_path") . '/utils/select_login?rto='.urlencode(\SiteConfig::getInstance()->get('base_path').$_SERVER['REQUEST_URI']));
      }
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
    \FileLogger::debug('call AuthSession::isAuthenticated');
    //check session var
    if(isset($_SESSION) && !empty($_SESSION[$this->session_config['session_id']])){
      return !empty($_SESSION[$this->session_config['session_id']]['authenticated']);
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
        return in_array($s_attr['user_email'], $this->session_config['app_admins']);
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
    #\FileLogger::debug("User is authenticated? ".$this->auth->isAuthenticated());
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
            $user->login();
            AppLog("login", $user);
          }
          $this->user = $user;
        }
      }
      if(!empty($this->user)){
        $uuid = $this->user->email;
      }
      //set session var
      $_SESSION[$this->session_config['session_id']] = array(
        'provider' => $provider,
        'authenticated' => $this->auth->isAuthenticated(),
        'uuid' => $uuid
      );
    }
  }

  #-- logout user - accepts return URL as optional param
  public function logout($return_to=''){
    \FileLogger::debug("call AuthSession::logout - return_to=$return_to");
    if(empty($return_to)){
      $return_to = \SiteConfig::getInstance()->get('base_path').'/';
    }
    if($this->validateAuthProvider()){
      if(isset($_SESSION[$this->session_config['session_id']])){
        //logout user in DB
        \User::logout($_SESSION[$this->session_config['session_id']]['uuid']);
        //clear authentication session data
        #\FileLogger::debug("clearing authentication session data");
        unset($_SESSION[$this->session_config['session_id']]);
      }
      //logout from provider
      $this->auth->logout($return_to);
    }else{
      if(isset($_SESSION)){
        if(isset($_SESSION[$this->session_config['session_id']])){
          //logout user in DB
          \User::logout($_SESSION[$this->session_config['session_id']]['uuid']);
        }
        \FileLogger::debug("clearing all session data");
        session_unset();
      }
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
   if(empty($this->session_config['more_auth_providers'])){
     #-- no additional auth providers, cannot create user
     return false;
   }
   #-- check if social media accounts can create user
   return $this->session_config['allow_create_social_auth'];
 }

#----- helpers

  /* verifies if $this->auth param is set and if not tries to set it using
   * info from session validator
   * Returns false if there is no way to get authentication provider,
   * true if $this->auth is not empty
   */
  private function validateAuthProvider(){
    \FileLogger::debug('call AuthSession::validateAuthProvider');
    if(empty($this->auth)){
      #\FileLogger::debug('--Auth provider not found, trying to set');
      if(!isset($_SESSION)){
        #start session if there are no session vars
        session_start();
      }
      if(!empty($_SESSION[$this->session_config['session_id']]) && !empty($_SESSION[$this->session_config['session_id']]['provider'])){
        #\FileLogger::debug('--setting auth provider '.$_SESSION[$this->session_config['session_id']]['provider']);
        $this->setAuthProvider($_SESSION[$this->session_config['session_id']]['provider']);
      }
    }
    #\FileLogger::debug('AuthSession::validateAuthProvider - returning '.!empty($this->auth));
    return !empty($this->auth);
  }

  /* sets $this->auth param -- the authentication provider
   * @param_name the given provider name
   */
  private function setAuthProvider($provider_name,$force_auth = false){
    \FileLogger::debug("call AuthSession::setAuthProvider - provider_name: $provider_name. Force authentication: $force_auth");
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
    #\FileLogger::debug("AuthSession::setAuthProvider - auth: ".print_r($this->auth,true));
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
