<?php
App::uses('AppModel', 'Model');
/**
 * Album Model
 *
 * @property Website $Website
 * @package       app.Model
 */
class FacebookUser extends AppModel {
    public $useDbConfig = 'facebook';
	public $useTable = "user";
	
	public function getFullData() {
		$fields = array(
            "uid",
            "username"
		);
		return $this->find('all', array('fields' => $fields));
	}
}
?>