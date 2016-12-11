<?php

App::uses('AppController', 'Controller');

class UsersController extends AppController {

	public $name = 'Users';

	public $uses = array('Course','User');
	
	public $components = array('Ldap');
 
	public function login() {
		if(!empty($this->data)) {
			$rememberme = false;
			if(isset($this->data['rememberme']) && $this->data['rememberme'] == 'on') {
				$rememberme = true;
			}
			$result = $this->Ldap->login($this->data['username'],$this->data['password'],$rememberme);
			if($this->Ldap->loggedin()) {
				$user = $this->User->find('first',array('conditions'=>array('User.id'=>$this->Ldap->getUserID())));
				if($user['User']['termsagreed'] < 1) {
					$this->redirect('/pages/ethicalclearance');
				} else {
					$this->redirect($this->Ldap->referer());
				}
			} else {
				$this->set('error',$result['error']);
			}
		}
	}
	
	public function logout() {
		$this->Ldap->logout();
		$this->redirect($this->referer());
	}
}
