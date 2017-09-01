<?php

  class WebMailer extends PHPMailer {

  	public function __construct()
  	{
  	  $MTA = \SiteConfig::getInstance()->get('email_send_function');

  	  if ($MTA == "SMTP") {
  	    $this->isSMTP(); // Set mailer to use SMTP
  	    $this->Host = \SiteConfig::getInstance()->get ( 'email_server_host' ); // Specify main SMTP server host
  	    $this->Port = \SiteConfig::getInstance()->get ( 'email_server_port' ); // Specify main SMTP server port
  	  }
  	  if ($MTA == "MAIL") {
  	    $this->isMail(); // use mail function
  	  }

  	  $this->setFrom(\SiteConfig::getInstance()->get ('email_from_address'), \SiteConfig::getInstance()->get ('email_from_name'));
  	  $this->CharSet = 'UTF-8';

  	  // Override messages with current language.
  	  $this->language = array(
  	  		'authenticate' => _('SMTP Error: Could not authenticate.'),
  	  		'connect_host' => _('SMTP Error: Could not connect to SMTP host.'),
  	  		'data_not_accepted' => _('SMTP Error: data not accepted.'),
  	  		'empty_message' => _('Message body empty'),
  	  		'encoding' => _('Unknown encoding: '),
  	  		'execute' => _('Could not execute: '),
  	  		'file_access' => _('Could not access file: '),
  	  		'file_open' => _('File Error: Could not open file: '),
  	  		'from_failed' => _('The following From address failed: '),
  	  		'instantiate' => _('Could not instantiate mail function.'),
  	  		'invalid_address' => _('Invalid address'),
  	  		'mailer_not_supported' => _('Mailer is not supported.'),
  	  		'provide_address' => _('You must provide at least one recipient email address.'),
  	  		'recipients_failed' => _('SMTP Error: The following recipients failed: '),
  	  		'signing' => _('Signing Error: '),
  	  		'smtp_connect_failed' => _('SMTP connect() failed.'),
  	  		'smtp_error' => _('SMTP server error: '),
  	  		'variable_set' => _('Cannot set or reset variable: ')
  	  );
  	}

  	private function strip_html($message)
  	{
  		$pattern = array();
  		$replace = array();

  		$pattern[] = "/<li>(.*)<\/li>/i";
  		$replace[] = " * $1\n";
  		$pattern[] = "/<p>/i";
  		$replace[] = "\n \n";
  		$pattern[] = "/<br\/>/i";
  		$replace[] = "\n";
  		$pattern[] = "/<br>/i";
  		$replace[] = "\n";

  		$result = preg_replace($pattern, $replace, $message);

  		$result = strip_tags($result);

  		$pattern = array();
  		$replace = array();

  		$pattern[] = "/\n\n/i";
  		$replace[] = "\n";
  		$pattern[] = "/  /";
  		$replace[] = " ";

  		$result = preg_replace($pattern, $replace, $result);

  		$result = wordwrap($result, 75, "\n");

  		return $result;
  	}

  	public function constructBrandedMessage($message, $text_message = null, $template = 'general')
  	{
      //load template
      $email_msg_templates = \SiteConfig::getInstance()->get('email_msg_templates');
      \FileLogger::debug("WebMailer::constructBrandedMessage - email templates: ".json_encode($email_msg_templates));
      if(!array_key_exists($template, $email_msg_templates)){
        \FileLogger::warn("Message template <$template> not configured, reseting to default");
        if(!array_key_exists('general', $email_msg_templates)){
          \FileLogger::error("WebMailer::constructBrandedMessage - Cannot send email, no email message templates were configured");
          return false;
        }
        $template = 'general';
      }
      $email_template = $email_msg_templates[$template];
  		if ($text_message == null) {
  		  $text_message = 	$this->strip_html($message);
  		}
      \FileLogger::debug("message: $message :: text message: $text_message");

  		$message_vars = array (
  		  "{subject}"        => $this->Subject,
	  	  "{html_message}"   => $message,
  		  "{text_message}"   => $text_message,
  		  "{baseurl}"        => \SiteConfig::getInstance()->get ( 'full_url' ),
  		  "{servicecontact}" => \SiteConfig::getInstance()->get ( 'email_service_address' ),
        "{year}" => date("Y")
  		);

      $plain_msg_template = "mail_message_template_".$template."_text";
      $html_msg_template = "mail_message_template_".$template."_html";
      //TODO check if templates exist, if not fall back to general

  		$plain_text = \Libs\Locale::processFile($plain_msg_template, $message_vars );
  		$html_text = \Libs\Locale::processFile($html_msg_template, $message_vars );

        // Don't enable this again... Adds strange '!\n' to message
  		// $html_text = preg_replace('!\s+!', ' ', $html_text);
  		// $html_text = preg_replace('!> <!', '><', $html_text);

  		$html_text = str_replace("  ", " ", $html_text);
  		$html_text = str_replace("> <", "><", $html_text);

      //add images
      foreach ($email_template as $tag => $source) {
        $this->addEmbeddedImage($source,$tag);
      }
      /*
  		$this->addEmbeddedImage(\SiteConfig::getInstance()->get ( 'email_service_logo' ), 'logo');
  		$this->addEmbeddedImage(\SiteConfig::getInstance()->get ( 'email_message_top' ), 'top');
  		$this->addEmbeddedImage(\SiteConfig::getInstance()->get ( 'email_message_bottom' ), 'bottom');
      $this->addEmbeddedImage(\SiteConfig::getInstance()->get ( 'email_message_spacer' ), 'spacer');
      */

  		$this->Body = $html_text;
  		$this->AltBody = $plain_text;
  	}


    private function isValidEmailAddress($email){
      return !(filter_var( $email, FILTER_VALIDATE_EMAIL ) === false);
    }

    public function sendTo($emails_to, $emails_bcc = ''){
      //prepare destination emails
      $emails_to = str_replace([',',';',"\n"],";",trim($emails_to));
      $emails_bcc = str_replace([',',';',"\n"],";",trim($emails_bcc));
      $to_arr = explode ( ";", $emails_to );
      $bcc_arr = explode ( ";", $emails_bcc );
      $messages = array(
        "invalid" => array(),
        "status" => ''
      );
      #add to mails
      foreach ( $to_arr as $email ) {
        \FileLogger::debug('Checking TO mail address: '.$email);
        if($this->isValidEmailAddress($email)) {
          $this->addAddress ( $email );
        }elseif (!empty($email)) {
          \FileLogger::error('invalid email address: '.$email);
          array_push($messages['invalid'],$email);
        }
      }
      #add bcc mails
      foreach ( $bcc_arr as $email ) {
        \FileLogger::debug('Checking BCC mail address: '.$email);
      	if($this->isValidEmailAddress($email)) {
          $this->addBCC ( $email );
        }elseif (!empty($email)) {
          \FileLogger::error('invalid email address: '.$email);
          array_push($messages['invalid'],$email);
        }
      }
      \FileLogger::debug("sending message TO <$emails_to>, BCC: <$emails_bcc>, subject: $this->Subject");
      #try sending
      if ($this->send ()) {
      	$messages['status'] = "ok";
      } else {
        $messages['status'] = "error";
        $messages["errorInfo"] = $this->ErrorInfo;
      }

      return $messages;
    }

  	public function SendToAdmin($more_emails = null)
  	{
  	  foreach(\SiteConfig::getInstance()->get('admin_list') as $email)
  		$this->addAddress ( $email );

  	  if ($more_emails) {
  	    foreach($more_emails as $email)
  	      $this->addAddress ( $email );
  	  }

  	  return $this->send ();
  	}
  }

  class ThrottleWebMailer extends WebMailer
  {
  	public function send() {

  		$filename = "/tmp/throttle-mail-message-" . md5($this->Subject);

  		if (file_exists($filename)) {
  	      $filemtime = filemtime ($filename);
  		  if (time() - $filemtime < \SiteConfig::getInstance()->get ( 'repeat-error-message' ) ) {
  			return false;
  		  }
  		}

  		touch($filename);

  		return parent::send();
  	}
  }
