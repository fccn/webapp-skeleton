<?php
/*
* Singleton SAML session manager
*/
namespace Libs;

require_once(\SiteConfig::getInstance()->get('saml_config')["ssp_base_path"].'/lib/_autoload.php');

class SAMLSession
{

  private $as;
  private $session;
  private $saml_config;
  private $authenticated;

  private static $instance;

  private function __clone() {}

  public function __construct() {
    $this->saml_config = \SiteConfig::getInstance()->get('saml_config');
    $this->as = new \SimpleSAML_Auth_Simple($this->saml_config['sp-default']);

/*
    if ($this->as->isAuthenticated()) {
      $this->attributes = $this->as->getAttributes();
      $this->authenticated = true;
    } else {
      $this->authenticated = false;
      $this->user = null;
    }
*/
    $this->authenticated = false;
    $this->user = null;
  }

  public static function getInstance() {
    \FileLogger::debug("call SAMLSession::getInstance");
  	if (!SAMLSession::$instance instanceof self) {
  		SAMLSession::$instance = new self();
  	}

  	return SAMLSession::$instance;
  }

  public function requireAuth($enforce_auth = true, $return_to = '')
  {
    $this->authenticated = self::$instance->isAuthenticated();

    if (($enforce_auth) && (!$this->authenticated)) {

      if(empty($return_to)){
        $this->as->requireAuth();
      }else{
        $this->as->requireAuth(array(
          'ReturnTo' => $return_to,
        ));
      }
      $this->authenticated = self::$instance->isAuthenticated();
    }
  }

  #-- forces SAML authentication accepts return to address
  public function authenticate($rto=''){
    $this->requireAuth(true,$rto);
  }

  public function getSession() {
    return $this->as;
  }

  public function hasAllAttributes() {

  	$result = true;

  	foreach($this->getAllExpectedAttributes() as $attribute => $params) {
  		if (!$this->findAttribute($attribute)) {
  			$result = false;
  		}
  	}

  	return $result;
  }

  public function hasMinimumAttributes() {
  	$result = true;

  	foreach($this->getAllExpectedAttributes() as $attribute => $params) {
  		if (!$this->findAttribute($attribute)) {
  			if ($params["mandatory"] == 1)
  			  $result = false;
  		}
  	}

  	if ($result) {
  	  foreach($this->getAllExpectedAttributes() as $attribute => $params) {
        if ($v = $this->findAttribute($attribute)) {
  		  if (isset($params["regex"])) {
  		    $ok = preg_match("/" . $params["regex"] . "/",$v);

  		    if (!$ok) {
  		      $result = false;
  		    }
  		  }
        }
  	  }
  	}

  	return $result;
  }

  public function getMissingOrIncorrectAttributes(){
    $res = array('missing' => [],
    'incorrect' => []);
    foreach($this->getAllExpectedAttributes() as $attribute => $params) {
      if ($v = $this->findAttribute($attribute)) {
        if (isset($params["regex"])) {
  		    $ok = preg_match("/" . $params["regex"] . "/",$v);
  		    if (!$ok) {
            \FileLogger::error('getMissingOrIncorrectAttributes: mandatory attribute <'.$attribute.'> is incorrectly formed. value '.$v.' does not follow regex:'.$params['regex']);
  		      array_push($res['incorrect'],
              array('attribute' => $attribute,
              'value' => $v,
              'regex' => $params["regex"]
            ));
  		    }
  		  }
      }else{
  			if ($params["mandatory"] == 1){
          \FileLogger::error('getMissingOrIncorrectAttributes: mandatory attribute <'.$attribute.'> not found');
  			  array_push($res['missing'], $attribute);
        }
  		}
  	}
    return $res;
  }

  public function getAllExpectedAttributes()
  {
  	return $this->saml_config['sp-expected-attributes'];
  }

  public function getAdministratorList()
  {
    return $this->saml_config["sp-administrator-list"];
  }

  public function isAuthenticated() {
    return $this->hasMinimumAttributes();
  }

  public function userIsAuthenticated() {
  	return $this->hasMinimumAttributes();
  }

  public function getAttributes() {
    return $this->as->getAttributes();
  }

  public function getFQDN_AuthSourceId()
  {
    return preg_replace('/https:\/\/([0-9A-Za-z\-\.]+)\/.*/', '$1', $this->getIdP());
  }

  public function getAuthSourceId()
  {
  	if($this->as->getAuthSource()  != null)
    	return $this->as->getAuthSource()->getAuthId();
  	else
  		return "";
  }

  public function getIdP(){
    if($this->getAuthData('saml:sp:IdP')){
      return $this->getAuthData('saml:sp:IdP');
    }
    return "";
  }

  public function getAuthData($name)
  {
    return $this->as->getAuthData($name);
  }

  public function findAttribute($attr_id) {

    $attributes = $this->getAttributes();

    if (isset($attributes[$attr_id][0]))
      return $attributes[$attr_id][0];

    return null;
  }

  public function getUniqueID()
  {
  	return $this->findAttribute("eduPersonPrincipalName");
  }

  public function getEmail()
  {
    return $this->findAttribute("mail");
  }

  /*
   * Returns array with the following session attributes:
   * - user_email (mandatory)
   * - user_friendly_name (mandatory)
   * - auth_source (mandatory)
   * - institution (optional)
  */
  public function getSessionAttributes(){
    return array(
      'user_email' => $this->getEmail(),
      'user_friendly_name' => $this->getFriendlyName(),
      'auth_source' => $this->getAuthSourceId()
    );
  }

  public function getEntityFQDN()
  {
    // Note! Change Federation available fields!!!
    return preg_replace('/.*\@(.*)/', '$1', $this->getEmail());
  }

  public function getMailDomain()
  {
    // Note! Change Federation available fields!!!
    return preg_replace('/.*\@(.*)/', '$1', $this->getEmail());
  }

  public function getMailID()
  {
    // Note! Change Federation available fields!!!
    return preg_replace('/(.*)\@.*/', '$1', $this->getEmail());
  }

  public function getDisplayName()
  {
    return $this->findAttribute("displayName");
  }

  public function getAffiliation()
  {
    return preg_replace('/(.*)\@.*/', '$1', $this->findAttribute("eduPersonScopedAffiliation"));
  }

  public function getGivenName()
  {
    return $this->findAttribute("givenname");
  }

  public function getFriendlyName()
  {
    $result = $this->getDisplayName();

    if ($result == "")
  	  $result = $this->getGivenName();

    return $result;
  }

  public function getTelephone()
  {
    return $this->findAttribute("urn:oid:2.5.4.20");
  }

  public function getOrganizationName()
  {
    if($this->findAttribute('organizationName')){
      return $this->findAttribute('organizationName');
    }
    return "";
  }

  public function isAdmin()
  {
  	return ($this->isAuthenticated()
         && in_array($this->getEmail(), $this->saml_config["app-administrator-list"]));
  }

  public function logout($return_to = '')
  {
    \FileLogger::debug('call SamlSession::logout - return_to: '.$return_to);
    if(empty($return_to)){
      $this->as->logout();
    }else{
      $this->as->logout(array(
        'ReturnTo' => $return_to
      ));
    }
  }

  public function listAttributeErrors(){
    $expected = $this->getAllExpectedAttributes();
    $tbl = array();
    foreach($expected as $attribute => $params) {
      $status = array(
        'attribute' => $attribute,
        'mandatory' => $params["mandatory"] ? true : false,
        'value' => $this->findAttribute($attribute),
        'regex' => 1
      );
      if ($status['value']) {
        if (isset($params["regex"])) {
           $status['regex'] = preg_match("/" . $params["regex"] . "/",$status['value']);
        }
      }
      $tbl[] = $status;
    }
    return $tbl;
  }

}
