<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class AssessmentController extends AppController {

	public $name = 'Assessment';

	public $uses = array('Course','CourseRoleUser','Logtype','Submission','Attachment','Version','Rubric','Surveyresult','Aggregatedmark');
	
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
	
	public function moderation($submission_id) {
		if($this->courseadmin) {
			$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
			$course = $this->Course->findById($submission['Project']['course_id']);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$this->breadcrumbs = array('/course/admin/'.$course['Course']['uid']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$submission['Project']['id']=>'Manage '.$submission['Project']['name'],'/projects/submissionmanager/'.$submission['Project']['id']=>'Submission Manager','/jojo'=>'Moderating '.$submission_id);
			$submission_id_hash = $this->encodeSubmissionID($submission_id);
			$this->set('submission_hash',$submission_id_hash);
			$students = array();
			$markers = array();
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '1') {
					$students[] = $activity['meta'];
				}
				if($activity['state_id'] == '2') {
					$markers[] = $activity['meta'];
				}
			}
			$this->set('students',implode(', ',$students));
			$this->set('markers',implode(', ',$markers));
			if(!empty($this->data)) {
				$markerlist = array();
				foreach($markers as $marker) {
					$themarker = $this->User->find('first',array('conditions'=>array('uqid'=>$marker),'recursive'=>-1));
					$markerlist[$themarker['User']['email']] = $themarker['User']['name'];
				}
				foreach($markerlist as $markeremail=>$markername) {
					$this->moderationEmail($markername,$course,$this->data['feedback'],$submission,$markeremail);
				}
				$this->Session->setFlash('Feedback sent to tutor email','flash_success');
			}
		}
	}
	
	public function download($submission_id_hash) {
		$submission_id = $this->decodeSubmissionID($submission_id_hash);
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id),'recursive'=>0));
		if(!is_numeric($submission_id)) {
			echo 'Invalid submission: '.$this->encodeSubmissionID($submission_id_hash);
			die();
		}
		if($submission['Project']['option_downloadable'] != 1) {
			//echo 'Unable to download: '.$this->encodeSubmissionID($submission_id_hash);
			//die();
		}
		$this->archiveSubmission($submission_id);
	}
	
	public function view_old($submission_id_hash,$nowrapper='',$selectedversion='') {
		$this->view($submission_id_hash,$nowrapper,$selectedversion);
	}
	
	public function view($submission_id_hash,$nowrapper='',$selectedversion='') {
		$testing = false;
		if($this->Ldap->getUQID() == 'uqadekke') {
			$testing = true;
		}
		$this->set('testing',$testing);
		$submission_id = $this->decodeSubmissionID($submission_id_hash);
		if(!is_numeric($submission_id)) {
			echo 'Invalid submission'.$this->encodeSubmissionID($submission_id_hash);
			die();
		}
		$this->set('nowrapper',$nowrapper);
		$this->set('submission_id_hash',$submission_id_hash);
		$this->Submission->unBindModel(array('hasMany' => array('Log')));
		$submission = $this->Submission->findById($submission_id);
		$multiplemarkers = false;
		if($submission['Project']['option_multiple_markers'] == 1) {
			$themarkers = array();
			$versions = $this->Version->find('all',array('conditions'=>array('submission_id'=>$submission_id),'order'=>array('created'=>'desc'),'recursive'=>-1));
			$multiplemarkers = true;
			//die();
			foreach($versions as $version) {
				$meta = json_decode($version['Version']['meta']);
				if(isset($meta->submitted_by)) {
					if(!isset($themarkers[$meta->submitted_by])) {
						$themarkers[$meta->submitted_by] = $version['Version']['id'];
					}
				}
			}
			/*foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '4') {
					$themarkers[] = $activity['id'];
				}
			}*/
			$this->set('themarkers',$themarkers);
			$this->set('selectedmarker',$selectedversion);
		}
		$this->set('multiplemarkers',$multiplemarkers);
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
		if($submission) {
			$student = '';
			$valid = false;
			$published = false;
			$logactions = false;
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '6') {
					$published = true;
				}
			}
			$this->set('project_id',$submission['Project']['id']);
			$downloadable = false;
			if($submission['Project']['option_downloadable'] == 1) {
				$downloadable = true;
			}
			$this->set('downloadable',$downloadable);
			$surveyavailable = false;
			if(file_exists($this->surveydir.$submission['Project']['id'].'_1.csv')) {
				$surveyavailable = true;
				/* Osmosis prac survey for those that didn't fill out the first */
				if($submission['Project']['id'] == '32') {
					$count31 = $this->Surveyresult->find('count',array('conditions'=>array('project_id'=>'31','survey_id'=>'1','user_id'=>$this->Ldap->getUserID())));
					if($count31 > 0) {
						$surveyavailable = false;
					}
				}
				if($submission['Project']['id'] == '40') {
					$count31 = $this->Surveyresult->find('count',array('conditions'=>array('project_id'=>'39','survey_id'=>'1','user_id'=>$this->Ldap->getUserID())));
					if($count31 > 0) {
						$surveyavailable = false;
					}
				}
				if($surveyavailable) {
					$this->constructSurvey($submission,'student');
				}
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
				if($activity['state_id'] == 1) {
					$student .= $activity['meta'].', ';
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
			$this->set('annots',$this->annotations($submission_id,$selectedversion));
			$this->set('marks',$this->marks($submission_id,$selectedversion));
			$rubrics = $this->Rubric->find('all',array('conditions'=>array('project_id'=>$submission['Submission']['project_id']),'recursive'=>-1,'order'=>array('section', 'id')));
			if($submission['Project']['option_multiple_markers'] == 1) {
				$marks = $this->Aggregatedmark->findBySubmissionId($submission_id);
				$marks = (array)json_decode($marks['Aggregatedmark']['marks']);
				$finalmarks = new Object();
				$finalmarks->marks = array();
				foreach($marks as $rubric_id=>$value) {
					$mark = new Object();
					$mark->rubric_id = $rubric_id;
					$mark->value = $value;
					$finalmarks->marks[] = $mark;
				}
				/*switch($submission['Submission']['project_id']) {
					case '28':
						$rubrics[] = array('Rubric'=>array('id'=>0,'project_id'=>28,'name'=>'Final Grade','type'=>'number','section'=>'Final','meta'=>'{"description":"","min":"0","max":"100"}'));
						$mark = new Object();
						$mark->rubric_id = '0';
						$mark->value = '79';
						$finalmarks->marks[] = $mark;
						break;
				}*/
				$this->set('marks',$finalmarks);
			}
			$coursecode = $this->Course->find('first',array('conditions'=>array('Course.id'=>$submission['Project']['course_id']),'recursive'=>-1));
			$this->set('submission',$submission);
			$this->set('rubrics',$rubrics);
			$this->set('logtypes',$this->Logtype->find('list'));
			$details = $submission['Attachment'][0]['title'].': '.$student;
			$details .= 'submission ID '.$submission_id;
			$this->set('details',$details);
			$coursecode = $coursecode['Course']['coursecode'].' - '.$submission['Project']['name'];
			$this->breadcrumbs = array('/course/create'=>'Viewing assessment feedback for '.$coursecode);	
		}
	}
	
	public function view_moderation($submission_id_hash,$nowrapper='') {
		$submission_id = $this->decodeSubmissionID($submission_id_hash);
		if(!is_numeric($submission_id)) {
			echo 'Invalid submission'.$this->encodeSubmissionID($submission_id_hash);
			die();
		}
		$this->set('nowrapper',$nowrapper);
		$this->set('submission_id_hash',$submission_id_hash);
		$submission = $this->Submission->findById($submission_id);
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
		if($submission) {
			$valid = false;
			$published = false;
			$logactions = false;
			$students = array();
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == '6') {
					$published = true;
				}
				if($activity['state_id'] == '1') {
					$thestudent = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>-1));
					$students[$activity['meta']] = $thestudent['User']['name'];
				}
			}
			$this->set('project_id',$submission['Project']['id']);
			$downloadable = false;
			if($submission['Project']['option_downloadable'] == 1) {
				$downloadable = true;
			}
			$this->set('downloadable',$downloadable);
			$surveyavailable = false;
			if(file_exists($this->surveydir.$submission['Project']['id'].'_1.csv')) {
				$surveyavailable = true;
			}
			$this->set('surveyavailable',$surveyavailable);
			foreach($submission['Activity'] as $activity) {
				if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
					$valid = true;
					break;
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
			$this->set('annots',$this->moderationAnnotations($submission_id));
			$this->set('marks',$this->marks($submission_id));
			$coursecode = $this->Course->find('first',array('conditions'=>array('Course.id'=>$submission['Project']['course_id']),'recursive'=>-1));
			$this->set('submission',$submission);
			$this->set('rubrics',$this->Rubric->find('all',array('conditions'=>array('project_id'=>$submission['Submission']['project_id']),'recursive'=>-1)));
			$this->set('logtypes',$this->Logtype->find('list'));
			$coursecode = $coursecode['Course']['coursecode'].' - '.$submission['Project']['name'];
			$this->breadcrumbs = array('/course/create'=>'Moderation feedback for  <strong style="color:orange;">'.implode(',',array_values($students)).' ('.implode(',',array_keys($students)).')</strong>');	
		}
	}
	
	function annotations($submission_id,$file_id=false) {
		if($file_id) {
		    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
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
			    		if(isset($annotationdatafile->filename)) {
				    		$audiofile = str_replace('.m4a','.mp3',$annotationdatafile->filename);
				    		$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$annotationdatafile->filename);
				    		$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$audiofile);
				    	}
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
	
	function moderationAnnotations($submission_id) {
		$path = $this->moderationdir.$submission_id.'/';
		$file = $path.'/annots/annots.json';
		if($file) {
		    if (file_exists($file) && is_readable ($file)) {
		    	$annotationdata = json_decode(file_get_contents($file));
		    	foreach($annotationdata as &$annotationdatafile) {
			    	if($annotationdatafile->type == 'Recording') {
			    		$audiofile = str_replace('.m4a','.mp3',$annotationdatafile->filename);
					   	$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$annotationdatafile->filename);
					   	$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$audiofile);
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
		    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
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
					$file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
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
					
					if($submission_id == 502) {
						$file = '/var/www/webdav/versions/502/2035b5963688f0924f3e118317e30b1deb24b4d4/jojo.pdf';
					}
					if($submission_id == 1049) {
						$file = '/var/www/webdav/versions/1049/2ad4348ba02a6606d5a49f4990ccdc153167d7c5/s4264737_new.pdf';
					}
					if($file) {
					    if (file_exists($file) && is_readable ($file)) {
					    	header("Content-length: ".filesize($file));
					    	header('Content-type: '.mime_content_type($file));
					    	readfile($file);
					    } else {
					    	if(!file_exists($file)) {
						    	$this->error = 'Could not load file '.$file;
						    }
						    if(!is_readable($file)) {
						    	$this->error = 'Could not load file 2 *'.$file.'*';
						    }
						    if (file_exists($file) && is_readable ($file)) {
						    	header("Content-length: ".filesize($file));
						    	header('Content-type: '.mime_content_type($file));
					    		readfile($file);
					    	}
					    	$this->httpcode = '500';	
					    	echo $file, file_exists($file) ? ' exists' : ' does not exist', "\n";
					    	echo $file, is_readable($file) ? ' is readable' : ' is NOT readable', "\n";
							echo $file, is_writable($file) ? ' is writable' : ' is NOT writeable', "\n";
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
	
	public function moderation_markedPDF($submission_id,$file_id=false) {
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
				if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
					$valid = true;
					break;
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
				$path = $this->moderationdir.$submission_id.'/';
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
					
					if($submission_id == 502) {
						$file = '/var/www/webdav/versions/502/2035b5963688f0924f3e118317e30b1deb24b4d4/jojo.pdf';
					}
					if($submission_id == 1049) {
						$file = '/var/www/webdav/versions/1049/2ad4348ba02a6606d5a49f4990ccdc153167d7c5/s4264737_new.pdf';
					}
					if($file) {
					    if (file_exists($file) && is_readable ($file)) {
					    	header("Content-length: ".filesize($file));
					    	header('Content-type: '.mime_content_type($file));
					    	readfile($file);
					    } else {
					    	if(!file_exists($file)) {
						    	$this->error = 'Could not load file '.$file;
						    }
						    if(!is_readable($file)) {
						    	$this->error = 'Could not load file 2 *'.$file.'*';
						    }
						    if (file_exists($file) && is_readable ($file)) {
						    	header("Content-length: ".filesize($file));
						    	header('Content-type: '.mime_content_type($file));
					    		readfile($file);
					    	}
					    	$this->httpcode = '500';	
					    	echo $file, file_exists($file) ? ' exists' : ' does not exist', "\n";
					    	echo $file, is_readable($file) ? ' is readable' : ' is NOT readable', "\n";
							echo $file, is_writable($file) ? ' is writable' : ' is NOT writeable', "\n";
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
	
	public function audiotest($submission_id,$filename,$file_id=false) {
		if(isset($_GET['version'])) {
			$file_id = $_GET['version'];
		}
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
					$file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				} else {
					$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				}
				$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
				$file = $path.'/annots/'.$filename;
				if($file) {
					if (file_exists($file) && is_readable ($file)) {
						header("Content-length: ".filesize($file));
						header('Content-type: audio/mpeg');//.mime_content_type($file));
						header("Accept-Ranges: bytes");
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
	
	public function audio($submission_id,$filename,$file_id=false) {
		if(isset($_GET['version'])) {
			$file_id = $_GET['version'];
		}
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
					$file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				} else {
					$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
				}
				$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
				if($filename == '') {
					if(strpos($this->request->url, ":") !== false) {
						$filename = substr($this->request->url, strpos($this->request->url, $submission_id)+strlen($submission_id)+1);
					}
				}
				$file = $path.'/annots/'.$filename;
				if($file) {
					if (file_exists($file) && is_readable ($file)) {
						header("Content-length: ".filesize($file));
						header('Content-type: audio/mpeg');//.mime_content_type($file));
						header("Accept-Ranges: bytes");
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
	
	public function moderation_audio($submission_id,$filename,$file_id=false) {
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
				if($activity['meta'] == $this->Ldap->getUQID() && $activity['state_id'] != '1') {
					$valid = true;
					break;
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
				$path = $this->moderationdir.$submission_id.'/';
				$file = $path.'/annots/'.$filename;
				if($file) {
					if (file_exists($file) && is_readable ($file)) {
						header("Content-length: ".filesize($file));
						header('Content-type: audio/mpeg');//.mime_content_type($file));
						header("Accept-Ranges: bytes");
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
	
	public function savesurveyanswer($project_id,$survey_id) {
		$data = array(
			'project_id'=>$project_id,
			'survey_id'=>$survey_id,
			'question_id'=>$this->data['question_id'],
			'answer'=>$this->data['answer'],
			'user_id'=>$this->Ldap->getUserID()
		);
		$this->Surveyresult->create();
		if($this->Surveyresult->save($data)) {
			echo 'true';
			die();
		}
		echo 'false';
		die();
	}
	
	function constructSurvey($project,$survey_name) {
		switch($survey_name) {
			case 'student':
				$survey_id = 1;
				break;
			case 'tutor_receive':
				$survey_id = 2;
				break;
			case 'tutor_give':
				$survey_id = 3;
				break;
			case 'coursecoordinator':
				$survey_id = 4;
				break;
		}
		$responseData = $this->Surveyresult->find('all',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'project_id'=>$project['Project']['id'],'survey_id'=>$survey_id)));
		$existingresponses = array();
		foreach ($responseData as $rdata) {
		    $existingresponses[$rdata['Surveyresult']['question_id']] = $rdata['Surveyresult']['answer'];
		}
      
		$this->set('existingresponses',$existingresponses);
		//load the csv
		ini_set("auto_detect_line_endings", 1);
		$surveypath = $this->surveydir.$project['Project']['id'].'_'.$survey_id.'.csv';
		if(file_exists($surveypath)) {
		    $questions = $this->get2DArrayFromCsv($surveypath,",");
		    $this->set('questions',$questions);
		    $this->set('survey_name', $survey_name);
		    $this->set('project_id', $project['Project']['id']);
		} else {
		    $this->permissionDenied('You cannot complete this survey, could not find survey');
		}
	}
	
	function get2DArrayFromCsv($file,$delimiter) { 
		$data2DArray = array();
        if (($handle = fopen($file, "r")) !== FALSE) { 
            $i = 0; 
            while (($lineArray = fgetcsv($handle, 4000, $delimiter)) !== FALSE) { 
                for ($j=0; $j<count($lineArray); $j++) { 
                    $data2DArray[$i][$j] = $lineArray[$j]; 
                } 
                $i++; 
            } 
            fclose($handle); 
        } 
        return $data2DArray; 
    }
}