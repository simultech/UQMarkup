<?php

App::uses('AppController', 'Controller');

class LogsController extends AppController {

	public $name = 'Logs';

	public $uses = array('Log','Submission','Logtype');
	
	public $components = array('Ldap');
	
	public $version = "1.2";
	public $webplatform = "Presenter";

	public function beforeFilter() {
		parent::beforeFilter();
		if(!$this->Ldap->loggedin) {
			echo 'not logged in';
			die();
		}
		if($this->Ldap->isAdmin()) {
			$this->courseadmin = true;
		}
	}
	
	public function presenter($run_hash,$submission_id,$logtype_id,$interaction,$meta) {
		if(!isset($run_hash) || !isset($submission_id) || !isset($submission_id) || !isset($submission_id) || !isset($submission_id)) {
			echo 'error: missing required field';
			die();
		}
		$data = array(
			'user_id'=>$this->Ldap->getUserID(),
			'logtype_id'=>$logtype_id,
			'submission_id'=>$submission_id,
			'interaction'=>$interaction,
			'meta'=>$meta,
			'platform'=>$this->webplatform,
			'version'=>$this->version,
			'sessionhash'=>$this->Ldap->sessionhash,
			'runhash'=>$run_hash,
		);
		$this->Log->create();
		if($this->Log->save($data)) {
			echo 'true';
		} else {
			echo 'false';
		}
		die();
	}
}