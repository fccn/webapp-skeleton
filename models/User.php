<?php

class User extends Model {
	public static $_table = 'user';
  public static $_id_column = 'id';

	public static function find_by_email($email){
		return Model::factory('User')->where('email',$email)->find_one();
	}

	#creates a new user using authentication session data
	# $saml_data = {email,name,auth_id}
	public static function create_from_session($session_data){
		$user = \Model::factory('User')->create();
		//TODO add/replace this based on user model
		$user->email = $session_data['email'];
		$user->name = $session_data['name'];
		$user->auth_source = $session_data['auth_id'];;
		$user->created_at = date( 'Y-m-d H:i:s', time() );
		$user->save();
		return $user;
	}

	#Updates user authentication data
	public function login(){
		//TODO fill in user data according to your user model
		#$this->locale = Locale::getCurrentLang();
		#$this->last_login = date( 'Y-m-d H:i:s', time() );
		#$this->in_session = true;
		#$this->session_count++;
		#$this->save();
	}

	#Returns a friendly name to display in user menu
	public function get_display_name(){
		//TODO modify according to your user model
		return $this->name;
	}

	#Registers user logout in database
	public static function logout($uuid){
		//TODO change this according to your user model
		$user = \Model::factory('User')
			->where('email',$uuid)->find_one();
		if($user){
			#$user->in_session = false;
			#$user->save();
		}
	}

}
