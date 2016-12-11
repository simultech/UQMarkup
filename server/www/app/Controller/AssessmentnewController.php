<?php

App::uses('AppController', 'Controller');

class AssessmentController extends AppController {

	public $name = 'Assessment';

	public $uses = array('Course','CourseRoleUser','Logtype','Submission','Attachment','Version','Rubric');
	
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
	
	public function view($submission_id_hash) {
		$submission_id = $this->decodeSubmissionID($submission_id_hash);
		if(!is_numeric($submission_id)) {
			echo 'Invalid submission'.$this->encodeSubmissionID($submission_id_hash);
			die();
		}
		$submission = $this->Submission->findById($submission_id);
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
		if($submission) {
			$valid = false;
			$published = false;
			$logactions = false;
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '6') {
					$published = true;
				}
			}
			$this->set('project_id',$submission['Project']['id']);
			$surveyavailable = false;
			if(file_exists($this->surveydir.$submission['Project']['id'].'_1.csv')) {
				$surveyavailable = true;
			}
			$this->set('surveyavailable',$surveyavailable);
			foreach($submission['Activity'] as $activity) {
				if($published) {
					if($activity['meta'] == $this->Ldap->getUQID()) {
						$valid = true;
						if($activity['state_id'] == 1) {
							$logactions = true;
						}
						break;
					}
				} else {
					if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
						$valid = true;
						break;
					}
				}
			}
			if(!$valid) {
				if($this->Ldap->isCourseCoordinator($course['Course']['uid'])) {
					$valid = true;
				} 
			}
			$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id'=>1,'course_id'=>$course['Course']['id'])));
			if(!empty($courseroleuser)) {
				$logactions = true;
			}
			if(!$valid) {
				$this->flashMessage('Permission denied','/');
			}
			$this->set('logactions',$logactions);
			$this->set('annots',$this->annotations($submission_id));
			$this->set('marks',$this->marks($submission_id));
			$coursecode = $this->Course->find('first',array('conditions'=>array('Course.id'=>$submission['Project']['course_id']),'recursive'=>-1));
			$this->set('submission',$submission);
			$this->set('rubrics',$this->Rubric->find('all',array('conditions'=>array('project_id'=>$submission['Submission']['project_id']),'recursive'=>-1)));
			$this->set('logtypes',$this->Logtype->find('list'));
			$coursecode = $coursecode['Course']['coursecode'].' - '.$submission['Project']['name'];
			$this->breadcrumbs = array('/course/create'=>'Viewing assessment feedback for '.$coursecode);	
		}
	}
	
	function annotations($submission_id,$file_id=false) {
		if($file_id) {
		    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		} else {
		    $file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		}
		$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
		$file = $path.'/annots/annots.json';
		if($file) {
		    if (file_exists($file) && is_readable ($file)) {
		    	$annotationdata = json_decode(file_get_contents($file));
		    	foreach($annotationdata as &$annotationdatafile) {
			    	if($annotationdatafile->type == 'Recording') {
					   	$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$annotationdatafile->filename);
			   		}
				}
		    	return $annotationdata;
		    } else {
		    	return array();
		    }
		} else {
	    	return array();
		}
	}
	
	function marks($submission_id,$file_id=false) {
		if($file_id) {
		    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		} else {
		    $file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		}
		$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
		$file = $path.'/marks.json';
		if($file) {
		    if (file_exists($file) && is_readable ($file)) {
		    	return json_decode(file_get_contents($file));
		    } else {
		    	return array();
		    }
		} else {
	    	return array();
		}
	}
	
	public function markedPDF($submission_id,$file_id=false) {
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
		if(!empty($submission)) {
			$valid = false;
			$published = false;
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '6') {
					$published = true;
				}
			}
			foreach($submission['Activity'] as $activity) {
				if($published) {
					if($activity['meta'] == $this->Ldap->getUQID()) {
						$valid = true;
						break;
					}
				} else {
					if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
						$valid = true;
						break;
					}
				}
			}
			if(!$valid) {
				if($this->Ldap->isCourseCoordinator($course['Course']['uid'])) {
					$valid = true;
				} 
			}
			if($this->Ldap->isAdmin()) {
				$valid = true;
			}
			if(!$valid) {
				$this->error = 'Permission denied';
				$this->httpcode = '500';	
			} else {
				if($file_id) {
					$file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				} else {
					$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				}
				$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
				$pdffile = '';
				if(!file_exists($path)) {
					$this->error = 'Submission not found';
					$this->httpcode = '404';
				} else {
					if ($handle = opendir($path)) {
					    while (false !== ($entry = readdir($handle))) {
					    	if (strpos($entry, '.pdf',1)) { 
					    		$pdffile = $entry;
					    	}
					    }
					    closedir($handle);
					}
					$file = $path.'/'.$pdffile;
					if($file) {
					    if (file_exists($file) && is_readable ($file)) {
					    	header("Content-length: ".filesize($file));
					    	header('Content-type: '.mime_content_type($file));
					    	readfile($file);
					    } else {
					    	$this->error = 'Could not load file';
					    	$this->httpcode = '500';	
					    }
					} else {
					    $this->error = 'File not found';
					    $this->httpcode = '404';
					}
				}
			}
		} else {
			$this->error = 'Submission not found';
			$this->httpcode = '404';
		}
		echo $this->error;
		die();
	}
	
	public function audio($submission_id,$filename,$file_id=false) {
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
		if(!empty($submission)) {
			$valid = false;
			$published = false;
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '6') {
					$published = true;
				}
			}
			foreach($submission['Activity'] as $activity) {
				if($published) {
					if($activity['meta'] == $this->Ldap->getUQID()) {
						$valid = true;
						break;
					}
				} else {
					if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
						$valid = true;
						break;
					}
				}
			}
			if(!$valid) {
				if($this->Ldap->isCourseCoordinator($course['Course']['uid'])) {
					$valid = true;
				} 
			}
			if($this->Ldap->isAdmin()) {
				$valid = true;
			}
			if(!$valid) {
				$this->error = 'Permission denied';
				$this->httpcode = '500';	
			} else {
				if($file_id) {
					$file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				} else {
					$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				}
				$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
				$file = $path.'/annots/'.$filename;
				if($file) {
					if (file_exists($file) && is_readable ($file)) {
						header("Content-length: ".filesize($file));
						header('Content-type: '.mime_content_type($file));
						readfile($file);
					} else {
						$this->error = 'Could not load file';
						$this->httpcode = '500';	
					}
				} else {
					$this->error = 'File not found';
					$this->httpcode = '404';
				}
			}
		} else {
			$this->error = 'Submission not found';
			$this->httpcode = '404';
		}
		echo $this->error;
		die();
	}
}