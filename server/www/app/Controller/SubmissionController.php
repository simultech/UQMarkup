<?php

App::uses('AppController', 'Controller');

class SubmissionController extends AppController {

	public $name = 'Submission';

	public $uses = array('Course','Submission','Attachment','Project','User','CourseRoleUser');
	
	public $components = array('Ldap');
	
	public $courseadmin = false;
	
	public function beforeFilter() {
		parent::beforeFilter();
		if(!$this->Ldap->loggedin()) {
			$this->redirect(array('controller'=>'users','action'=>'login'));
		}
		if($this->Ldap->isAdmin()) {
			$this->courseadmin = true;
		}
	}
 
	public function display($submission_id,$file_id=null) {
		if($this->courseadmin) {
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$course = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$courseuid = $course['Course']['uid'];
		if(!empty($submission)) {
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if($file_id) {
					$file = $this->Attachment->find('first',array('conditions'=>array('Attachment.id'=>$file_id),'recursive'=>-1));
				} else {
					$file = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1));
				}
				if($file) {
					$path = $this->binarydir.$file['Attachment']['path'];
					if (file_exists($path) && is_readable ($path)) {
					header("Content-length: ".filesize($path));
						header('Content-type: '.mime_content_type($path));
						readfile($path);
					} else {
						$this->error = 'Could not load file';
						$this->httpcode = '500';	
					}
				} else {
					$this->error = 'File not found';
					$this->httpcode = '404';
				}
			} else {
				$this->error = 'Submission not found';
				$this->httpcode = '404';
			}
		}
		}
	}
}
