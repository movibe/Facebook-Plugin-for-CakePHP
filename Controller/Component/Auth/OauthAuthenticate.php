<?php
/**
 * oAuth component for core authentication via Facebook. It's a modified version of danielauener's FacebookAuthenticate
 *
 * @package		app.Plugin.Facebook.Controller.Component.Auth
 * @author 		danielauener (https://github.com/danielauener)
 * @link 		https://github.com/danielauener/cake-social-custom-auth
 * @copyright	Copyright 2012, danielauener
 */
	App::uses('CakeSession', 'Model/Datasource');
	App::uses('BaseAuthenticate', 'Controller/Component/Auth');

	class OauthAuthenticate extends BaseAuthenticate {
		public $settings = array();
		
		private function loadSettings() {
			$this->settings = array(
				'app_id' => Configure::read('FacebookAppId'),
		        'app_secret' => Configure::read('FacebookAppSecret'),
			   	'url' => Configure::read('OauthRedirectUrl')
			);
		}

        public function authenticate(CakeRequest $request, CakeResponse $response) {
        	$this->loadSettings();
           	$session = new CakeSession();
            if (isset($request->query) && isset($request->query['code']) && isset($request->query['state'])) {
                if($request->query['state'] == $session->read('state')) {
                    $token_url = "https://graph.facebook.com/oauth/access_token?"
                        . "client_id=" . $this->settings["app_id"]
                        . "&redirect_uri=" . urlencode($this->settings["url"])
                           . "&client_secret=" . $this->settings["app_secret"]
                           . "&code=" . $request->query['code'];
                           
                    $response = file_get_contents($token_url);
                    $params = null;
                    parse_str($response, $params);
                    if (isset($params['access_token'])) {
						// Saves access_token in Session
						$_SESSION['access_token'] = $params['access_token'];
						
                        App::uses('FacebookUser', 'Facebook.Model');
						$FacebookUser = new FacebookUser();
                        $fb_user = $FacebookUser->getFullData();
						
                        App::uses('User', 'Model');
                        $User = new User();
                        $user = $User->find("first",array("conditions" => array("username" => $fb_user['FacebookUser']['username'])));
                        if (!$user) {
                            $user = array(
                                "User" => array(
                                    "username" => $fb_user['FacebookUser']['username']
                                )
                            );
                            $User->create();
                            $User->save($user);
                            $user["User"]["id"] = $User->getLastInsertID();
                        }
                        return $user["User"];
                    }
                }
            }    
            return false;        
        }    
    	
	}
?>