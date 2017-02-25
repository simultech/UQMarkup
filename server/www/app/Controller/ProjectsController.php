<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class ProjectsController extends AppController {

	public $name = 'Projects';

	public $uses = array('Course','CourseRoleUser','Project','Tag','Rubric','Submission','Attachment','Activity','State','Log','Version','Assignedstudent');
	
	public $components = array('Ldap');
	
	public $courseadmin = false;
	
	var $rubrictypes = array(
		'table'=>'Table',
		'number'=>'Number',
		'boolean'=>'Boolean',
		'text'=>'Text',
	);

	public function beforeFilter() {
		parent::beforeFilter();
		if(!$this->Ldap->loggedin()) {
			$this->redirect(array('controller'=>'users','action'=>'login'));
		}
		if($this->Ldap->isAdmin()) {
			$this->courseadmin = true;
		}
		Configure::write('debug', 2);
	}
	
	public function loadSubmissions($project_id) { 
		$project = $this->Project->findById($project_id);
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$numberofprojectsadded = $this->loadProjectSubmissions($project);
				$this->flashMessage($numberofprojectsadded.' submissions added','/projects/submissionmanager/'.$project_id,true);
			}
		}
	}
	
	public function folderlist($project_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$courseroleusers = array();
				foreach($submissions as &$submission) {
					$submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
					//$submission['annotationz'] = $this->annotations($submission['Submission']['id']);
					foreach($submission['Activity'] as $activity) {
						if($activity['state_id'] == '4') {
							$submission['marked'] = true;
						} 
						if($activity['state_id'] == '1') {
							$submission['course_role_users_id'] = $activity['course_role_users_id'];
							//if(!isset($activity['course_role_users_id'])) {
								$submission['Student']['uqid'] = $activity['meta'];
							//}
							$courseroleusers[] = $activity['course_role_users_id'];
						}
					}
				}
				//$courseroleusers = $this->CourseRoleUser->find('all',array('conditions'=>array('CourseRoleUser.id'=>$courseroleusers)));
				/*foreach($submissions as &$submission) {
					foreach($courseroleusers as $courseroleuser) {
						if($courseroleuser['CourseRoleUser']['id'] == $submission['course_role_users_id']) {
							$submission['Student'] = $courseroleuser['User'];
						}
					}
				}*/
				foreach($submissions as $submission) {
					if(isset($submission['marked']) && $submission['marked']) {
						$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission['Submission']['id']),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
						$subhash = '<br />sudo vim '.Configure::read('path_webdav').'/versions/'.$submission['Submission']['id'].'/'.$version['Version']['path'].'/marks.json';
						echo '<p>'.$submission['Student']['uqid'].' - '.$subhash.'</p>';
					}
				}
				//print_r($submissions);
				die();
			}
		}
		echo 'folder';
		die();
	}
	
	public function modifygrades($submission_id) {
		$submission = $this->Submission->findById($submission_id);
		$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		if($this->courseadmin) {
			$project = $this->Project->findById($submission['Project']['id']);
			$course = $this->Course->findByUid($project['Course']['uid']);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($course['Course']['uid'])) {
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['uid']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project['Project']['id']=>'Manage '.$project['Project']['name'],'/projects/submissionmanager/'.$project['Project']['id']=>'Submission Manager','/projects/modifygrades/'.$project['Project']['id']=>'Modify Grades');
				$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
				$file = $path.'/marks.json';
				if($file) {
				    if (file_exists($file) && is_readable ($file)) {
					    if(!empty($this->data)) {
						    $filedata = file_get_contents($file);
						    $fileobj = json_decode($filedata);
						    foreach($this->data as $rubricid=>$rubricval) {
							    if($rubricval != '') {
								    foreach($fileobj->marks as &$mrk) {
									    if($mrk->rubric_id==$rubricid) {
										    $mrk->value=$rubricval;
									    }
								    }
							    }
						    }
						    $filewrite = json_encode($fileobj);
						    file_put_contents($file, $filewrite);
						    $this->flashMessage('Marks updated',$this->referer(),true);
					    }
					    $this->set('file',$file);
					    $this->set('rubrics',$this->Rubric->find('all',array('order'=>array('Rubric.section'),'conditions'=>array('Rubric.project_id'=>$submission['Project']['id']))));
					    $this->set('marks',json_decode(file_get_contents($file)));
					    $this->set('submission',$submission);
				    } else {
					    $this->flashMessage('Bad marks',$this->referer(),true);
					    die();
				    }
				} else {
					$this->flashMessage('Bad marks',$this->referer(),true);
					die();
				}
			} else {
				$this->flashMessage('Bad marks',$this->referer(),true);
				die();
			}
		} else {
			$this->flashMessage('Bad marks',$this->referer(),true);
			die();
		}
	}
	
	public function admin($project_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name']);
				$this->set('project',$project);
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$this->set('submissions',$this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id))));
				$rubrics = $this->Rubric->find('all',array('conditions'=>array('Rubric.project_id'=>$project_id)));
				$this->set('rubrics',$rubrics);
				$tags = $this->Tag->find('all',array('conditions'=>array('Tag.project_id'=>$project_id)));
				$this->set('tags',$tags);
				
				$rubricstatus = 'incomplete';
				if(!empty($rubrics) > 0) {
					$rubricstatus = 'complete';
				}
				
				$tagsstatus = 'incomplete';
				if(!empty($tags) > 0) {
					$tagsstatus = 'complete';
				}
				
				$submissionstatus = 'incomplete';
				if(sizeOf($project['Submission']) > 0) {
					$submissionstatus = 'complete';
				}
				
				$identifystatus = 'complete';
				$reviewstatus = 'incomplete';
				$moderationstatus = 'incomplete';
				$publishstatus = 'incomplete';
				
				if($identifystatus != 'incomplete') {
					$reviewstatus = 'recursive';
					$moderationstatus = 'recursive';
				}
				
				$workflow = array(
			        array(
			            'name'=>'Create Project',
			            'class'=>'icon_addcourse',
			            'status'=>'complete',
			        ),
			        array(
			            'name'=>'Add Rubrics',
			            'class'=>'icon_rubrics',
			            'status'=>$rubricstatus,
			            'link'=>$this->baseURL.'/projects/rubrics/'.$project_id,
			        ),
			        /*array(
			            'name'=>'Add Colour Tags',
			            'class'=>'icon_colourtags',
			            'status'=>$tagsstatus,
			            'link'=>$this->baseURL.'/projects/tags/'.$project_id,
			        ),*/
			        array(
			            'name'=>'Upload Submissions',
			            'class'=>'icon_uploadsubmissions',
			            'status'=>$submissionstatus,
			            'link'=>$this->baseURL.'/projects/submissionmanager/'.$project_id,
			        )
			    );
			    $workflow_iterative = array(
			        array(
			            'name'=>'Assign Submissions',
			            'class'=>'icon_identifysubmissions',
			            'status'=>$identifystatus,
			            'link'=>$this->baseURL.'/projects/submissionmanager/'.$project_id,
			        ),
			        array(
			            'name'=>'Review Submissions',
			            'class'=>'icon_reviewsubmissions',
			            'status'=>$reviewstatus,
			            'link'=>$this->baseURL.'/projects/create/'.$course['Course']['uid'],
			        ),
			        array(
			            'name'=>'Moderate Feedback',
			            'class'=>'icon_moderatesubmissions',
			            'status'=>$moderationstatus,
			            'link'=>$this->baseURL.'/projects/create/'.$course['Course']['uid'],
			        ),
			        array(
			            'name'=>'Publish Feedback',
			            'class'=>'icon_publishfeedback',
			            'status'=>$publishstatus,
			            'link'=>$this->baseURL.'/projects/create/'.$course['Course']['uid'],
			        ),
				);
				$this->set('workflow',$workflow);
				$this->set('workflow_iterative',$workflow_iterative);
				
				if(!empty($this->data)) {
					$this->Project->set($this->data);
					if($this->Project->validates()) {
						if($this->Project->save($this->data)) {
							$this->flashMessage('Assessment updated',$this->referer(),true);
						} else {
							$this->flashMessage('Could not updated assessment','false');
						}
					} else {
						$formerrors = $this->Project->invalidFields();
						$this->set('formerrors',$formerrors);
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function sbms_students($project_id,$type='raw') {
		set_time_limit(180);
		$this->set('type',$type);
		if($type == 'raw') {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=submissionlist_".$project_id.".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			$this->layout = false;
		}
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				echo 'Student ID,State'."\n";
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$maxstate = 0;
					$studentid = '';
					foreach($submission['Activity'] as $activity) {
						if($maxstate < $activity['state_id']) {
							$maxstate = $activity['state_id'];
						}
						if($activity['state_id'] == '1') {
							$studentid = $activity['meta'];
							//$courseroleusers[] = $activity['course_role_users_id'];
						}
					}
					if($maxstate > 0) {
						echo $studentid.','.$states[$maxstate]."\n";
					}
				}
			}
		}
		die();
	}
	
	public function submissionlist($project_id,$stage_id='',$type='all') {
		set_time_limit(180);
		$this->set('type',$type);
		if($type == 'raw') {
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename=submissionlist_".$project_id.".csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			$this->layout = false;
		}
		$this->set('stage_id',$stage_id);
		$this->set('uploadfolder',$this->getuploadfolder($project_id));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/projects/submissionmanager/'.$project_id=>'Submission Manager');
				$errors = array();
				$this->Submission->unbindModel(
					array('hasMany' => array('Log'))
				);
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				//print_r($states);
				//die();
				$courseroleusers = array();
				foreach($submissions as &$submission) {
					$submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
					$submission['annotationz'] = $this->annotations($submission['Submission']['id']);
					foreach($submission['Activity'] as $activity) {
						if($activity['state_id'] == '1') {
							$submission['course_role_users_id'] = $activity['course_role_users_id'];
							if(!isset($activity['course_role_users_id'])) {
								$submission['Student']['uqid'] = $activity['meta'];
							}
							$courseroleusers[] = $activity['course_role_users_id'];
						}
					}
				}
				$courseroleusers = $this->CourseRoleUser->find('all',array('conditions'=>array('CourseRoleUser.id'=>$courseroleusers)));
				/*foreach($submissions as &$submission) {
					foreach($courseroleusers as $courseroleuser) {
						if($courseroleuser['CourseRoleUser']['id'] == $submission['course_role_users_id']) {
							$submission['Student'] = $courseroleuser['User'];
						}
					}
				}*/
				$this->set('submissions',$submissions);
				$this->set('project',$project);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function submissionlisttext($project_id,$stage_id='',$type='all') {
		set_time_limit(280);
		Configure::write('debug', 2);
		ini_set('memory_limit','384M');
		$this->set('type',$type);
		$this->set('stage_id',$stage_id);
		$this->set('uploadfolder',$this->getuploadfolder($project_id));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/projects/submissionmanager/'.$project_id=>'Submission Manager');
				$errors = array();
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
				}
				$this->set('submissions',$submissions);
				$this->set('project',$project);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function tutorsubmissionlist($project_id,$stage_id='',$type='all') {
		$this->set('type',$type);
		$this->set('stage_id',$stage_id);
		$this->set('uploadfolder',$this->getuploadfolder($project_id));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		$course = $this->Course->findByUid($courseuid);
		$tutordetails = $this->CourseRoleUser->find('first',array('conditions'=>array('role_id > 1','user_id'=>$this->Ldap->getUserID(),'course_id'=>$course['Course']['id'])));
		if(!empty($tutordetails)) {
			$this->set('tutordetails',$tutordetails);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			$this->breadcrumbs = array('/projects/submissionmanager/'.$project_id=>'Tutor Submission List');
			$errors = array();
			$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
			foreach($submissions as &$submission) {
			    $submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
			}
			$this->set('submissions',$submissions);
			$this->set('project',$project);
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function submissionmanager($project_id,$stage_id='') {
		$this->set('stage_id',$stage_id);
		$this->set('uploadfolder',$this->getuploadfolder($project_id));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$sizewarning = '';
				$size = round(disk_free_space(getcwd())/1000000);
				if($size < 5000) {
					$sizewarning = '<div id="flashMessage" class="message"><p>Warning, only '.$size.'MB remaining on disk</p></div>';
				}
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/projects/submissionmanager/'.$project_id=>'Submission Manager');
				$errors = array();
				if(!empty($this->data)) {
					//Auto to mark
					if($this->data['selected_action'] == 'marked') {
						$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id'=>3,'course_id'=>$project['Course']['id']),'recursive'=>-1));
						if(!empty($courseroleuser)) {
							foreach($this->data['submissionchecked'] as $submission_id=>$val) {
								if($val == 'on') {
									$submission = $this->Submission->findById($submission_id);
									$isidentified = false;
									$alreadymarked = false;
									foreach($submission['Activity'] as $activity) {
										if($activity['state_id'] == 1) {
											$isidentified = true;
										}
										if($activity['state_id'] > 3) {
											$alreadymarked = true;
										}
									}
									if($isidentified && !$alreadymarked) {									
										if(!file_exists($this->versionsdir.$submission_id)) {
											mkdir($this->versionsdir.$submission_id);
										}
										$versiondata = array();
										$versiondata['submission_id'] = $submission_id;
										$versiondata['meta'] = '{"submitted_by":"'.$this->Ldap->getUQID().'"}';
										$this->Version->create();
										$this->Version->save($versiondata);
										$versionid = $this->Version->id;
										$path = sha1($submission_id.'/'.$versionid);
										$this->Version->saveField('path',$path);
										if(!file_exists($this->versionsdir.$submission_id.'/'.$path)) {
											mkdir($this->versionsdir.$submission_id.'/'.$path);
										}
										copy($this->binarydir.$submission['Attachment'][0]['path'],$this->versionsdir.$submission_id.'/'.$path.'/'.$submission['Attachment'][0]['title']);
										$activitydata = array();
										$activitydata['meta'] = $this->Ldap->getUQID();
										$activitydata['state_id'] = 4;
										$activitydata['submission_id'] = $submission_id;
										$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
										$this->Activity->create();
										$this->Activity->save($activitydata);
										//need to upload the assignment
									} else if(!$isidentified) {
										$errors[] = $submission_id.' is not yet identified';
									} else {
										$errors[] = $submission_id.' has already been marked';
									}
								}
							}
						}
					//Auto to publish
					} else if($this->data['selected_action'] == 'publish') {
						$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id'=>3,'course_id'=>$project['Course']['id']),'recursive'=>-1));
						if(!empty($courseroleuser)) {
							foreach($this->data['submissionchecked'] as $submission_id=>$val) {
								if($val == 'on') {
									$submission = $this->Submission->findById($submission_id);
									$readytopublish = false;
									$studentids = array();
									foreach($submission['Activity'] as $activity) {
										if($activity['state_id'] == 1) {
											$studentids[] = $activity['meta'];
										}
										if($activity['state_id'] > 3 && $activity['state_id'] < 6) {
											$readytopublish = true;
										}
									}
									if($readytopublish) {
										$studentemails = array();
										foreach($studentids as $studentid) {
											$thestudent = $this->User->find('first',array('conditions'=>array('uqid'=>$studentid)));
											$studentemails[$thestudent['User']['email']] = $thestudent['User']['name'];
										}
										$activitydata = array();
										$activitydata['meta'] = $this->Ldap->getUQID();
										$activitydata['state_id'] = 6;
										$activitydata['submission_id'] = $submission_id;
										$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
										$this->Activity->create();
										$this->Activity->save($activitydata);
										foreach($studentemails as $studentemail=>$studentname) {
											$this->emailToStudent($studentname,$course,$project,$submission,$studentemail);
										}
									} else {
										$errors[] = $submission_id.' has not yet been marked';
									}
								}
							}
						}
					//Auto to delete
					} else if($this->data['selected_action'] == 'delete') {
						$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id'=>3,'course_id'=>$project['Course']['id']),'recursive'=>-1));
						if(!empty($courseroleuser)) {
							foreach($this->data['submissionchecked'] as $submission_id=>$val) {
								if($val == 'on') {
									if($this->Submission->delete($submission_id)) {
										$attachments = $this->Attachment->find('all',array('conditions'=>array('submission_id'=>$submission_id)));
										foreach($attachments as $attachment) {
											$path = $this->binarydir.$attachment['Attachment']['path'];	
											if(file_exists($path)) @unlink($path);
										}
										$this->Activity->deleteAll(array('submission_id'=>$submission_id));
										$this->Log->deleteAll(array('submission_id'=>$submission_id));
										$this->Attachment->deleteAll(array('submission_id'=>$submission_id));
										$this->Version->deleteAll(array('submission_id'=>$submission_id));
									} else {
										$errors[] = $submission_id.' could not be deleted';
									}
								}
							}
						}
					} else {
						/* Identify */
						if(isset($this->data['identify'])) {
							foreach($this->data['identify'] as $submission_id=>$uqidlist) {
								if(trim($uqidlist) != '') {
									$uqids = explode(',', $uqidlist);
									foreach($uqids as $uqid) {
										$uqid = trim($uqid);
										$student = $this->User->find('first',array('conditions'=>array('uqid'=>$uqid),'recursive'=>-1));
										if(!empty($student)) {
											$identifyrole = 1;
											if(isset($_POST['usetutors']) && $_POST['usetutors'] == 'on') {
												$identifyrole = 2;
											}
											$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('role_id'=>$identifyrole,'user_id'=>$student['User']['id'],'course_id'=>$project['Course']['id'])));
											if(empty($courseroleuser) && $identifyrole == 2) {
												$identifyrole = 3;
												$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('role_id'=>$identifyrole,'user_id'=>$student['User']['id'],'course_id'=>$project['Course']['id'])));
											}
											if(!empty($courseroleuser)) {
												$activitydata = array(
													'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
													'state_id'=>1,
													'submission_id'=>$submission_id,
													'meta'=>$uqid,
												);
												if($this->Activity->find('count',array('conditions'=>$activitydata)) == 0) {
													$this->Activity->create();
													$this->Activity->save($activitydata);
												}
												if($project['Project']['option_disable_autoassign'] == 0) {
                                                    $this->autoassign($submission_id);
                                                }
											} else {
												$errors[] = 'User is not in class list '.$uqid;
											}
										} else {
											$errors[] = 'Could not find user '.$uqid;
										}
									}
									
								}
							}
						}
						/* Assign */
						if(isset($this->data['assign'])) {
							foreach($this->data['assign'] as $submission_id=>$uqid) {
								if($uqid != '') {
									$uqids = explode(',', $uqid);
									foreach($uqids as $uqid) {
										$tutor = $this->User->find('first',array('conditions'=>array('uqid'=>$uqid),'recursive'=>-1));
										if(!empty($tutor)) {
									   		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('role_id > 1','user_id'=>$tutor['User']['id'],'course_id'=>$project['Course']['id'])));
									   		if(!empty($courseroleuser)) {
									    		$activitydata = array(
									    			'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
									    			'state_id'=>2,
									    			'submission_id'=>$submission_id,
									    			'meta'=>$uqid,
									    		);
									    		if($this->Activity->find('count',array('conditions'=>$activitydata)) == 0) {
									    			$this->Activity->create();
									    			$this->Activity->save($activitydata);
									    		}
									    	} else {
									    		$errors[] = 'User is not a course tutor/coordinator '.$uqid;
									    	}
									    } else {
									    	$errors[] = 'Could not find user '.$uqid;
									    }
									}
								}
							}
						}
						/* Moderation */
						if(isset($this->data['moderate'])) {
						
						}
					}
					if(sizeOf($errors) == 0) {
						$this->flashMessage('Submissions updated','false',true);
					} else {
						$this->flashMessage(implode(',',$errors),'false');
					}
				}
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id),'fields'=>array('Submission.*')));
				foreach($submissions as &$submission) {
					$submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
				}
				$this->set('submissions',$submissions);
				$this->set('project',$project);
				$this->set('sizewarning',$sizewarning);
			}
		}
	}
	
	public function submissionmanagerajax($project_id) {
		$this->submissionmanager($project_id);
	}
	
	public function submissionfinish($submission_id) {
		
	}
	
	public function deletesubmission($submission_id) {
		if($this->Submission->delete($submission_id)) {
			$attachments = $this->Attachment->find('all',array('conditions'=>array('submission_id'=>$submission_id)));
			foreach($attachments as $attachment) {
				$path = $this->binarydir.$attachment['Attachment']['path'];	
				if(file_exists($path)) @unlink($path);
			}
			$this->Activity->deleteAll(array('submission_id'=>$submission_id));
			$this->Log->deleteAll(array('submission_id'=>$submission_id));
			$this->Attachment->deleteAll(array('submission_id'=>$submission_id));
			$this->Version->deleteAll(array('submission_id'=>$submission_id));
			$this->flashMessage('Submission Deleted',$this->referer(),true);
		} else {
			$this->flashMessage('Could not delete submission',$this->referer());
		}
		die();
	}
	
	public function downloadsubmission($submission_id_hash) {
		$submission_id = $this->decodeSubmissionID($submission_id_hash);
		if(!is_numeric($submission_id)) {
			echo 'Invalid submission'.$this->encodeSubmissionID($submission_id_hash);
			die();
		}
		$this->archiveSubmission($submission_id);
	}
	
	public function testing($uqid) {
		$data = $this->Ldap->lookupuser($uqid);
		print_r($data);
		die();
	}
	
	public function submissionhistory($submission_id) {
		$submission = $this->Submission->findById($submission_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$project_id = $project['Project']['id'];
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
		//print_r($submission);
		//die();
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/projects/submissionmanager/'.$project_id=>'Submission Manager','Submission History for ID:'.$submission_id);
				$this->set('states',$this->State->find('list',array('recursive'=>-1)));
				foreach($submission['Activity'] as &$activity) {
					if($activity['meta'] != '') {
						$userdata = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>-1));
						if(!empty($userdata)) {
							$activity['meta'] .= ' ('.$userdata['User']['name'].')';
						}
					}
				}
				$this->set('submission',$submission);
				$this->set('version',$this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc'))));
				$this->set('submissionhash',$this->encodeSubmissionID($submission_id));
			}
		}
	}
	
	public function duplicaterubrics($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$oldrubrics = $this->Rubric->find('all',array('conditions'=>array('project_id'=>$_POST['othercourse'])));
				foreach($oldrubrics as $oldrubric) {
					$newrubric = $oldrubric['Rubric'];
					unset($newrubric['id']);
					$newrubric['project_id'] = $project_id;
					$this->Rubric->create();
					$this->Rubric->save($newrubric);
				}
				$this->flashMessage('rubrics duplicated','/projects/rubrics/'.$project_id,true);
			}
		}
		die();
	}
	
	public function rubrics($project_id) {
		
		$this->set('rubrictypes',$this->rubrictypes);
	
	
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/projects/rubrics/'.$project_id=>'Manage rubrics');
				$othercourses = $this->Course->find('all');
				$this->set('othercourses',$othercourses,array('order'=>array('created'=>'desc')));
				$this->set('project',$project);
				$this->set('rubrics',$this->Rubric->find('all',array('conditions'=>array('Rubric.project_id'=>$project_id))));
				if(!empty($this->data)) {
					if(isset($this->data['importrubric'])) {
						if(isset($_FILES['rubric']) && $_FILES['rubric']['error'] == 0) {
							ini_set("auto_detect_line_endings", 1);
							$rubric_array = $this->get2DArrayFromCsv($_FILES['rubric']['tmp_name'],",");
							$errors = array();
							$line = 0;
							foreach($rubric_array as $rubric_line) {
								if($rubric_line[0] == 'Name' && $rubric_line[1] == 'Section') {
									$line++;
									continue;
								}
								if($rubric_line[0] == '' && $rubric_line[2] == '') {
									$line++;
									continue;
								}
								$type = strtolower($rubric_line[2]);
								$types = array('table','boolean','text','number');
								if(!in_array($type, $types)) {
									$errors[] = 'Invalid type '.$rubric_line[2];
									break;
								}
								$meta = array();
								switch($type) {
									case 'text':
									case 'boolean':
										$meta['description'] = $rubric_line[3];
										break;
									case 'number':
										$meta['description'] = $rubric_line[3];
										$meta['min'] = $rubric_line[4];
										$meta['max'] = $rubric_line[5];
										break;
									case 'table':
										$count = $rubric_line[6];
										for($i=0; $i<$count; $i++) {
											if(isset($rubric_line[7+$i])) {
												if(isset($rubric_array[$line+1][7+$i])) {
													$meta[] = array('name'=>$rubric_line[7+$i],'description'=>$rubric_array[$line+1][7+$i]);
												} else {
													$errors[] = 'Invalid table structure for line '.($line+1);
												}
											} else {
												$errors[] = 'Invalid table structure for line '.($line+1);
											}
										}
										break;
								}
								$data = array(
									'project_id'=>$project['Project']['id'],
									'name'=>$rubric_line[0],
									'type'=>$type,
									'section'=>$rubric_line[1],
									'meta'=>json_encode($meta),
								);
								$this->Rubric->create();
								$this->Rubric->save($data);
								$line++;
							}
							if(!empty($errors)) {
								$this->flashMessage('Could not import rubric file at line '.($line+1).' - '.$errors[0],'false');
							} else {
								$this->flashMessage('Imported rubrics',$this->referer(),true);
							}
						} else {
							$this->flashMessage('Could not upload rubric file','false');
						}
						die();	
					} else {
						$rubricdata = $this->data;
						$rubricdata['meta'] = json_encode($rubricdata['meta']);
						$this->Rubric->set($rubricdata);
						if($this->Rubric->validates()) {
						    if($this->Rubric->save($rubricdata)) {
						    	$this->flashMessage('Rubric added',$this->referer(),true);
						    } else {
						    	$this->flashMessage('Could not add rubric','false');
						    }
						} else {
						    $formerrors = $this->Rubric->invalidFields();
						    $this->set('formerrors',$formerrors);
						}
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
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
	
	public function removerubric($rubric_id) {
		$rubric = $this->Rubric->find('first',array('conditions'=>array('Rubric.id'=>$rubric_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$rubric['Rubric']['project_id'])));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin || empty($tag)) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Rubric->delete($rubric_id);
				$this->flashMessage('Rubric removed',$this->referer(),true);
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not a valid rubric ID');
		}
	}
	
	public function editrubric($rubric_id) {
		$rubric = $this->Rubric->find('first',array('conditions'=>array('Rubric.id'=>$rubric_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$rubric['Rubric']['project_id'])));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin || empty($tag)) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project['Project']['id']=>'Manage '.$project['Project']['name'],'/projects/rubrics/'.$project['Project']['id']=>'Mange rubrics','/projects/rubrics/'.$project['Project']['id'].'/'.$rubric_id=>'Edit rubric');
				$this->set('meta',json_decode($rubric['Rubric']['meta']));
				$this->set('rubric',$rubric);
				$this->set('project',$project);
				if(!empty($this->data)) {
					$rubricdata = $this->data;
					$rubricdata['meta'] = json_encode($this->data['meta']);
					$this->Rubric->set($rubricdata);
					if($this->Rubric->validates()) {
						if($this->Rubric->save($rubricdata)) {
							$this->flashMessage('Rubric updated','/projects/rubrics/'.$project['Project']['id'],true);
						} else {
							$this->flashMessage('Could not update rubric','false');
						}
					} else {
						$formerrors = $this->Rubric->invalidFields();
						$this->set('formerrors',$formerrors);
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not a valid rubric ID');
		}
	}
	
	public function removetag($tag_id) {
		$tag = $this->Tag->find('first',array('conditions'=>array('Tag.id'=>$tag_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$tag['Tag']['project_id'])));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin || empty($tag)) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Tag->delete($tag_id);
				$this->flashMessage('Colour tag removed',$this->referer(),true);
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not a valid tag');
		}
	}
	
	public function tags($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/admin/'.$project_id=>'Mange assessment','/projects/tags/'.$project_id=>'Mange colour tags');
				$this->set('project',$project);
				$this->set('tags',$this->Tag->find('all',array('conditions'=>array('Tag.project_id'=>$project_id))));
				if(!empty($this->data)) {
					$this->Tag->set($this->data);
					if($this->Tag->validates()) {
						if($this->Tag->save($this->data)) {
							$this->flashMessage('Colour tag added',$this->referer(),true);
						} else {
							$this->flashMessage('Could not add colour tag','false');
						}
					} else {
						$formerrors = $this->Tag->invalidFields();
						$this->set('formerrors',$formerrors);
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function assigntutors($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0) {
					$ext = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
					if($ext != 'csv') {
						$this->flashMessage('Please provide a CSV file','/projects/submissionmanager/'.$project_id);
						die();
					}
					//file should be all good
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
						$csvdata = file_get_contents($_FILES['csv']['tmp_name']);
						$csvdata = explode("\r",$csvdata);
						if(sizeOf($csvdata) < 2) {
							$csvdata = explode("\n",$csvdata);
						}
						$i = 0;
						$ids = array();
						foreach($csvdata as $csvline) {
							$i++;
							//ignore the headers
							if($i<2) {
								continue;
							}
							$csvcolumns = explode(",",$csvline);
							if($csvcolumns[2] != "") {
								$ids[$csvcolumns[0]] = $csvcolumns[2];
							}
						}
						asort($ids);
						$identified = 0;
						$invalidtutors = array();
						foreach($ids as $id=>$tutorname) {
							$activitydata = array();
							$user = $this->User->find('first',array('conditions'=>array('uqid'=>$tutorname)));
							if($user) {
							    $courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$user['User']['id'],'role_id > 1','course_id'=>$project['Course']['id'])));
							    if(empty($courseroleuser)) {
								    $invalidtutors[] = $tutorname;
							    } else {
								    $activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
								    $activitydata['state_id'] = $this->getStateID('Identified');
								    $activitydata['submission_id'] = $id;
								    $activitydata['meta'] = $tutorname;
								    $this->Activity->create();
								    $this->Activity->save($activitydata);
								    $identified++;
								}
							} else {
								$invalidtutors[] = $tutorname;
							}
						}
						if(sizeOf($invalidtutors) > 0) {
							$invalidtutorstring = '';
							foreach($invalidtutors as $invalidtutor) {
								$invalidtutorstring .= $invalidtutor.', ';
							}
							$this->flashMessage($identified.' Tutors assigned, could not assign tutors '.$invalidtutorstring,'/projects/submissionmanager/'.$project_id,true);
						} else {
							$this->flashMessage($identified.' Tutors assigned','/projects/submissionmanager/'.$project_id,true);
						}
						fclose($handle);
					}
					
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function getassigntutorscsv($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				header("Content-Type: text/csv");
				header("Content-Disposition: inline; filename=".$project_id."_tutorallocation.csv ");
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as $submissionkey=>&$submission) {
					$activitylevel = 0;
					$activitymeta = "";
					foreach($submission['Activity'] as $activity) {
						if($activitylevel < $activity['state_id']) {
							$activitylevel = $activity['state_id'];
							$submission['Activitymeta'] = $activity['meta'];
						}
					}
					if($activitylevel != $this->getStateID('Submitted')) {
						unset($submissions[$submissionkey]);
					}
				}
				echo "Submission ID,Student ID,Tutor ID\n";
				foreach($submissions as $submission) {
					echo $submission['Submission']['id'].",".$submission['Activitymeta'].",\n";
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function identifysubmissionsfromname($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$identified = 0;
				foreach($submissions as $submission) {
					//check if its only been uploaded, nothing has happened
					if(sizeOf($submission['Activity']) == 0) {
						if(sizeOf($submission['Attachment'] > 0)) {
							$student_ids = array();
							$student_id = $submission['Attachment'][0]['title'];
							$student_id = str_replace(".pdf", "", $student_id);
							if($project['Project']['option_group_project'] == 1) {
								$student_ids = explode('_', $student_id);
							} else {
								$student_ids[] = $student_id;
							}
							foreach($student_ids as $student_id) {
								//if its a group, we need to loop
								$user = $this->User->find('first',array('conditions'=>array('uqid'=>$student_id)));
								//if there is a user with this name
								if(!empty($user)) {
									$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$user['User']['id'],'role_id'=>'1','course_id'=>$project['Course']['id'])));
									if(!empty($courseroleuser)) {
										$activitydata = array(
											'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
											'state_id'=>$this->getStateID('Submitted'),
											'submission_id'=>$submission['Submission']['id'],
											'meta'=>$student_id
										);
										$this->Activity->create();
										$this->Activity->save($activitydata);
										if($project['Project']['option_disable_autoassign'] == 0) {
                                            $this->autoassign($submission['Submission']['id']);
                                        }
										$identified++;
									}
								}
							}
						}
					}
				}
				$this->flashMessage($identified.' students identified','/projects/submissionmanager/'.$project_id,true);
				die();
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function biol1040identify($project_id) {
		$project = $this->Project->findById($project_id);
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$identified = 0;
				//remove duplicates
				$submissionsbystudent = array();
				foreach($submissions as $submission) {
					//check if its only been uploaded, nothing has happened
					if(sizeOf($submission['Activity']) < 2) {
						if(sizeOf($submission['Attachment'] > 0)) {
							$student_ids = array();
							$student_id = $submission['Attachment'][0]['title'];
							$student_id = str_replace(".pdf", "", $student_id);
							$filename = explode("_", $student_id);
							$date = strtotime($filename[3]);//date('YYYY-mm-dd HH_ii_ss',strtotime($filename[3]));
							$date = DateTime::createFromFormat('j-m-Y g-i-s A', $filename[3]);
							if($date) {
								$date = $date->format('Y-m-d H:i:s');
							} else {
								$date = DateTime::createFromFormat('j-m-Y g-i A', $filename[3]);
								$date = $date->format('Y-m-d H:i:s');
							}
							if($date) {
								$submissionsbystudent[$filename[2]][$date] = $submission;
							}
						}
					}
				}
				foreach($submissionsbystudent as $submissionbystudent) {
					if(sizeOf($submissionbystudent) > 1) {
						$biggesttime = 0;
						foreach($submissionbystudent as $date=>$submissionbystudent_sub) {
							if(strtotime($date) > strtotime($biggesttime)) {
								$biggesttime = $date;
							}
						}
						foreach($submissionbystudent as $date=>$submissionbystudent_sub) {
							if(strtotime($date) < strtotime($biggesttime)) {
								$delete_subid = $submissionbystudent_sub['Submission']['id'];
								//delete the submission
								$this->Submission->delete($delete_subid);
								$attachments = $this->Attachment->find('all',array('conditions'=>array('submission_id'=>$delete_subid)));
								foreach($attachments as $attachment) {
									$path = $this->binarydir.$attachment['Attachment']['path'];	
									if(file_exists($path)) @unlink($path);
								}
								$this->Activity->deleteAll(array('submission_id'=>$delete_subid));
								$this->Log->deleteAll(array('submission_id'=>$delete_subid));
								$this->Attachment->deleteAll(array('submission_id'=>$delete_subid));
								$this->Version->deleteAll(array('submission_id'=>$delete_subid));
							}
						}
					}
				}
				foreach($submissions as $submission) {
					//check if its only been uploaded, nothing has happened
					if(sizeOf($submission['Activity']) == 0) {
						if(sizeOf($submission['Attachment'] > 0)) {
							$student_ids = array();
							$student_id = $submission['Attachment'][0]['title'];
							$student_id = str_replace(".pdf", "", $student_id);
							$filename = explode("_", $student_id);
							$creationdate = $filename[3];
							$student_id = $filename[2];
							$user = $this->User->find('first',array('conditions'=>array('uqid'=>$student_id)));
							//if there is a user with this name
							if(!empty($user)) {
								$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$user['User']['id'],'role_id'=>'1','course_id'=>$project['Course']['id'])));
								//print_r($courseroleuser['CourseRoleUser']['id']);
								//die();
								if(!empty($courseroleuser)) {
									$activitydata = array(
										'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
										'state_id'=>$this->getStateID('Submitted'),
										'submission_id'=>$submission['Submission']['id'],
										'meta'=>$student_id
									);
									$this->Activity->create();
									$this->Activity->save($activitydata);
									if($project['Project']['option_disable_autoassign'] == 0) {
                                        $this->autoassign($submission['Submission']['id']);
                                    }
									$identified++;
								}
							}
						}
					}
				}
				$this->flashMessage($identified.' students identified','/projects/submissionmanager/'.$project_id,true);
				die();
			}
		}
	}
	
	public function biol1040identifyblackboard($project_id) {
		$project = $this->Project->findById($project_id);
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			$submissionlist = array_values($this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id),'recursive'=>-1)));
			$assigns = array_unique(array_values($this->Activity->find('list',array('fields'=>array('meta'),'conditions'=>array('submission_id'=>$submissionlist,'state_id'=>'1'),'recursive'=>-1))));
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$identified = 0;
				//remove duplicates
				$submissionsbystudent = array();
				foreach($submissions as $submission) {
					//check if its only been uploaded, nothing has happened
					if(sizeOf($submission['Activity']) < 2) {
						if(sizeOf($submission['Attachment'] > 0)) {
							if(strpos($submission['Attachment'][0]['title'],'_attempt_') != '-1') {
								$filename = substr($submission['Attachment'][0]['title'],0,strpos($submission['Attachment'][0]['title'],'_attempt_'));
								$student_id = substr($filename,strpos($filename,'_')+1);
								if(!in_array($student_id, $assigns)) {
									$user = $this->User->find('first',array('conditions'=>array('uqid'=>$student_id)));
									//if there is a user with this name
									if(!empty($user)) {
									    $courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$user['User']['id'],'role_id'=>'1','course_id'=>$project['Course']['id'])));
									    if(!empty($courseroleuser)) {
									    	$activitydata = array(
									    		'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
									    		'state_id'=>$this->getStateID('Submitted'),
									    		'submission_id'=>$submission['Submission']['id'],
									    		'meta'=>$student_id
									    	);
									    	$this->Activity->create();
									    	$this->Activity->save($activitydata);
									    	if($project['Project']['option_disable_autoassign'] == 0) {
                                                $this->autoassign($submission['Submission']['id']);
                                            }
									    	$identified++;
									    }
									}
								}
							}
						}
					}
				}
				$this->flashMessage($identified.' students identified','/projects/submissionmanager/'.$project_id,true);
				die();
			}
		}
	}
	
	public function checkfilelistblackboard($project_id,$onlynew='') {
		$onlynew = 'new';
		$project = $this->Project->findById($project_id);
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			$submissionlist = array_values($this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id),'recursive'=>-1)));
			$assigns = array_unique(array_values($this->Activity->find('list',array('fields'=>array('meta'),'conditions'=>array('submission_id'=>$submissionlist,'state_id'=>'1'),'recursive'=>-1))));
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$files = explode("\n",$this->data['text']);
				//print_r($files);
				echo '<table><tr><th>File</th><th>Student ID</th><th>Status</th></tr>';
				foreach($files as $file) {
					$student_id = 'Unknown';
					$status = 'New';
					if(strpos($file,'_attempt_') != '-1') {
						$filename = substr($file,0,strpos($file,'_attempt_'));
						$student_id = substr($filename,strpos($filename,'_')+1);
					}
					if(in_array($student_id, $assigns)) {
						$status = 'Existing';
					}
					$show = true;
					if($onlynew == 'new') {
						if($status != 'New') {
							$show = false;
						}
					}
					if($show) {
						echo '<tr><td>'.$file.'</td><td>'.$student_id.'</td><td>'.$status.'</td></tr>';
					}
				}
				echo '</table>';
				die();
				print_r($assigns);
				//$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				//$identified = 0;
				//remove duplicates
				//$submissionsbystudent = array();
				//foreach($submissions as $submission) {
				
				//}
			}
		}
		echo 'done';
		die();
	}
	
	public function publishsubmissions($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				//check if its ready
				$publishedsubmissions = 0;
				foreach($submissions as $submission) {
					$publishing = false;
					$studentuqids = array();
					foreach($submission['Activity'] as $activity) {
						if($activity['state_id'] == '1') {
							$studentuqids[] = $activity['meta'];
						}
						if($activity['state_id'] == '4') {
							$publishing = true;
						}
						if($activity['state_id'] == '6') {
							$publishing = false;
						}
					}
					$students = $this->User->find('list',array('fields'=>array('email','name'),'conditions'=>array('uqid'=>$studentuqids)));
					if($publishing) {
						//set the activity
						//print_r($submission);
						$activitydata = array();
						$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id'=>'3','course_id'=>$course['Course']['id'])));
						$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
						$activitydata['state_id'] = $this->getStateID('Published');
						$activitydata['submission_id'] = $submission['Submission']['id'];
						$activitydata['meta'] = $this->Ldap->getUQID();
						$this->Activity->create();
						$this->Activity->save($activitydata);
						foreach($students as $studentemail=>$studentname) {
							$this->emailToStudent($studentname,$course,$project,$submission,$studentemail);
						}
						$publishedsubmissions++;
					}
				}
				if($publishedsubmissions == 1) {
					$publishedsubmissions = '1 submission';
				} else {
					$publishedsubmissions = $publishedsubmissions.' submissions';
				}
				$this->flashMessage('Published '.$publishedsubmissions.'','/projects/submissionmanager/'.$project_id,true);
				die();
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function deleteactivity($activity_id) {
		$activity = $this->Activity->findById($activity_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$activity['Submission']['project_id'])));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Activity->delete($activity_id);
				$this->flashMessage('Process deleted','/projects/submissionhistory/'.$activity['Submission']['id'],true);
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function reassignstudents($submission_id) {
		$submission = $this->Submission->findById($submission_id);
		$project = $this->Project->findById($submission['Project']['id']);
		$course = $this->Course->findByUid($project['Course']['uid']);
		if($this->courseadmin) {
			//find the new students
			$newstudents = explode(',', $_POST['studentids']);
			$studentemails = array();
			$courseroleusers = array();
			
			foreach($newstudents as $newstudent) {
				$usr = $this->User->find('first',array('conditions'=>array('uqid'=>$newstudent),'recursive'=>0));
				$studentemails[$usr['User']['email']] = $usr['User']['name'];
				if($usr && !empty($usr)) {
					$cru = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$usr['User']['id'],'course_id'=>$submission['Project']['course_id'])));
					if($cru && !empty($cru)) {
						$courseroleusers[] = $cru['CourseRoleUser']['id'];
					}
				}
			}
			if(sizeOf($courseroleusers) != sizeOf($newstudents)) {
				$this->permissionDenied('Invalid students entered');
			}
			
			//unassign existing students
			foreach($submission['Activity'] as $activity) {
				if($activity['state_id'] == 1) {
					//delete the activity
					$this->Activity->delete($activity['id']);
				}
			}
			$count = 0;
			$good = true;
			foreach($courseroleusers as $courseroleuser) {
				$newuser = array(
					'course_role_users_id'=>$courseroleuser,	
					'state_id'=>1,
					'submission_id'=>$submission_id,
					'meta'=>$newstudents[$count]
				);
				$this->Activity->create();
				if($this->Activity->save($newuser)) {
					//echo 'good';
					foreach($studentemails as $studentemail=>$studentname) {
						$this->emailToStudent($studentname,$course,$project,$submission,$studentemail);
					}
				} else {
					$good = false;
				}
				$count++;
			}
			if($good) {
				$this->flashMessage('Students reassigned','/projects/submissionhistory/'.$submission_id,true);
			} else {
				$this->flashMessage('Students could not all be assigned','/projects/submissionhistory/'.$submission_id,true);
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function renameattachment($attachment_id) {
		$attachment = $this->Attachment->find('first',array('conditions'=>array('Attachment.id'=>$attachment_id)));
		$project_id = $attachment['Submission']['project_id'];
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Attachment->id = $attachment_id;
				$filename = $this->data['filename'];
				
				$filename = $filename.'.pdf';
				if($this->Attachment->saveField('title',$filename)) {
					$this->flashMessage('Renamed submission file','/projects/submissionmanager/'.$project_id,true);
				} else {
					$this->flashMessage('Could not rename submission file','/projects/submissionmanager/'.$project_id);					
				}
			}
		}
	}
	
	public function parsewithturnitin($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if(!isset($this->data['prepend']) || $this->data['prepend'] == '') {
					$this->flashMessage('Please add a prepend','/projects/submissionmanager/'.$project_id);
				}
				if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0) {
					$ext = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
					if($ext != 'csv') {
						$this->flashMessage('Please provide a CSV file (Turnitin save as CSV from Excel)','/projects/submissionmanager/'.$project_id);
					}
					//file should be all good
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
						$csvdata = file_get_contents($_FILES['csv']['tmp_name']);
						$csvdata = explode("\r",$csvdata);
						if(sizeOf($csvdata) < 2) {
							$csvdata = explode("\n",$csvdata);
						}
						$i = 0;
						$ids = array();
						foreach($csvdata as $csvline) {
							$i++;
							//ignore the headers
							if($i<3) {
								continue;
							}
							$csvcolumns = explode(",",$csvline);
							if(sizeOf($csvcolumns) > 1) {
								$name = str_replace(' ','_',trim($csvcolumns[1])).'_'.str_replace(' ','_',trim($csvcolumns[0])).'-';
								$ids[$csvcolumns[2]] = $name;
							}
						}
						if(sizeOf($ids) != sizeOf(array_unique($ids))) {
							$common = array_unique(array_diff_assoc($ids,array_unique($ids))); 
							foreach($common as $comkey=>$comval) {
								foreach($ids as $idkey=>$idval) {
									if($idval == $comval) {
										unset($ids[$idkey]);
									}
								}
							}
						}
						if(sizeOf($ids) == sizeOf(array_unique($ids))) {	
							//no duplicates
							$identified = 0;
							$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
							foreach($submissions as $submission) {
								if(empty($submission['Activity'])) {
									$attachmentprefix = $submission['Attachment'][0]['title'];
									$attachmentprefix = substr($attachmentprefix,0,strpos($attachmentprefix, '-')+1);
									if(in_array($attachmentprefix, $ids)) {
										$student_id = array_search($attachmentprefix,$ids);
										if($student_id != '') {
											//save file name
											$prepend = $this->data['prepend'];
											foreach($submission['Attachment'] as $attachment) {
												if(substr($attachment['title'], 0, strlen($prepend)) != $prepend) {
													$this->Attachment->id = $attachment['id'];
													$newtitle = $prepend.'_'.$attachment['title'];
													$this->Attachment->saveField('title',$newtitle);
												}
											}
											//reassign
											$activitydata = array();
											$user = $this->User->find('first',array('conditions'=>array('uqid'=>$student_id)));
											if($user) {
												$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$user['User']['id'],'role_id'=>'1','course_id'=>$project['Course']['id'])));
												$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
												$activitydata['state_id'] = $this->getStateID('Submitted');
												$activitydata['submission_id'] = $submission['Submission']['id'];
												$activitydata['meta'] = $student_id;
												$this->Activity->create();
												$this->Activity->save($activitydata);
												if($project['Project']['option_disable_autoassign'] == 0) {
												    $this->autoassign($submission['Submission']['id']);
												}
												$identified++;
											}
										}
									}
									//die();
								}
							}
							$this->flashMessage($identified.' students identified','/projects/submissionmanager/'.$project_id,true);
						}
						fclose($handle);
					}
					
				} else {
					$this->flashMessage('Please provide a CSV file (Turnitin save as CSV from Excel)','/projects/submissionmanager/'.$project_id);
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function manualassign($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0) {
					$ext = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
					if($ext != 'csv') {
						$this->flashMessage('Please provide a CSV file (Turnitin save as CSV from Excel)','/projects/submissionmanager/'.$project_id);
						die();
					}
					//file should be all good
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
						$csvdata = file_get_contents($_FILES['csv']['tmp_name']);
						$csvdata = explode("\r",$csvdata);
						if(sizeOf($csvdata) < 2) {
							$csvdata = explode("\n",$csvdata);
						}
						$i = 0;
						$ids = array();
						foreach($csvdata as $csvline) {
							$i++;
							//ignore the headers
							if($i<2) {
								continue;
							}
							$csvcolumns = explode(",",$csvline);
							if(trim($csvcolumns[0]) != '') {
								$ids[$csvcolumns[0]] = $csvcolumns[1];
							}
						}
						asort($ids);
						//no duplicates
						$identified = 0;
						$prepend = $this->data['pgroup'];
						$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
						foreach($submissions as $submission) {
							$studentid = '';
							$match = false;
							foreach($submission['Activity'] as $activity) {
								if($activity['state_id'] == 1 && in_array($activity['meta'],array_keys($ids))) {
									$studentid = $activity['meta'];
									$match = true;
								}
								if($activity['state_id'] == 2) {
									$match = false;
								}
							}
							if($match) {
								echo 'found';
								$marker = $this->User->find('first',array('conditions'=>array('uqid'=>$ids[$studentid]),'recursive'=>-1));
								$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$marker['User']['id'],'course_id'=>$course['Course']['id'],'role_id > '=>'1'),'recursive'=>-1));
								if(!empty($courseroleuser)) {
								    //yes, identify
								    sleep(0.5); //delay on save
								    $this->Activity->create();
								    $activitydata = array(
								    	'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
								    	'state_id'=>'2',
								    	'submission_id'=>$submission['Submission']['id'],
								    	'meta'=>$marker['User']['uqid'],
								    );
								    $this->Activity->save($activitydata);
								    $identified++;
								}
						    }
						}
						$this->flashMessage($identified.' submissions assigned','/projects/submissionmanager/'.$project_id,true);
						fclose($handle);
					}
					
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function addpgroup($project_id) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0) {
					$ext = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
					if($ext != 'csv') {
						$this->flashMessage('Please provide a CSV file (Turnitin save as CSV from Excel)','/projects/submissionmanager/'.$project_id);
						die();
					}
					//file should be all good
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
						$csvdata = file_get_contents($_FILES['csv']['tmp_name']);
						$csvdata = explode("\r",$csvdata);
						if(sizeOf($csvdata) < 2) {
							$csvdata = explode("\n",$csvdata);
						}
						$i = 0;
						$ids = array();
						foreach($csvdata as $csvline) {
							$i++;
							//ignore the headers
							if($i<3) {
								continue;
							}
							$csvcolumns = explode(",",$csvline);
							if(trim($csvcolumns[1]) != '') {
								$ids[] = $csvcolumns[1];
							}
						}
						asort($ids);
						//no duplicates
						$identified = 0;
						$prepend = $this->data['pgroup'];
						$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
						foreach($submissions as $submission) {
							$match = false;
							foreach($submission['Activity'] as $activity) {
								if($activity['state_id'] == 1 && in_array($activity['meta'],$ids)) {
									$match = true;
								}
							}
							if($match) {
								foreach($submission['Attachment'] as $attachment) {
									if(substr($attachment['title'], 0, strlen($prepend)) != $prepend) {
										$this->Attachment->id = $attachment['id'];
										$newtitle = $prepend.'_'.$attachment['title'];
										$this->Attachment->saveField('title',$newtitle);
									}
								}
								$identified++;
						    }
						}
						$this->flashMessage($identified.' submissions renamed','/projects/submissionmanager/'.$project_id,true);
						fclose($handle);
					}
					
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
		die();
	}
	
	public function create($courseuid) {
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/projects/create/'.$courseuid=>'Add assessment');
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('course',$course);
				if(!empty($this->data)) {
				    $data = $this->data;
				    if ($data['start_date'] == '') {
                        $data['start_date'] = date('d-m-Y');
                    }
                    if ($data['submission_date'] == '') {
                        $data['submission_date'] = date('d-m-Y');
                    }
                    if ($data['end_date'] == '') {
                        $data['end_date'] = date('d-m-Y');
                    }
					$this->Project->set($data);
					if($this->Project->validates()) {
						if($this->Project->save($data)) {
							$this->refreshWebdavPermissions($course['Course']['id']);
							$this->flashMessage('Assessment created','/course/admin/'.$courseuid,true);
						} else {
							$this->flashMessage('Could not create assessment','false');
						}
					} else {
						$formerrors = $this->Project->invalidFields();
						$this->set('formerrors',$formerrors);
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function autoassign($submission_id) {
		$idents = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1));
		$maxstate = 1;
		$courseroleusers = array();
		foreach($idents as $ident) {
			if($maxstate < $ident['Activity']['state_id']) {
				$maxstate = $ident['Activity']['state_id'];
			}
			if($ident['Activity']['state_id'] == 1) {
				$courseroleusers[] = $ident['Activity']['course_role_users_id'];
			}
		}
		if($maxstate == 1) {
			//try to assign
			$autoassign = $this->Assignedstudent->find('first',array('conditions'=>array('courseroleuser_id'=>$courseroleusers)));
			if(!empty($autoassign)) {
				$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$autoassign['Marker']['id'],'course_id'=>$autoassign['Assignedstudent']['course_id'],'role_id > '=>'1'),'recursive'=>-1));
				if(!empty($courseroleuser)) {
					//yes, identify
					sleep(0.5); //delay on save
					$this->Activity->create();
					$activitydata = array(
						'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
						'state_id'=>'2',
						'submission_id'=>$submission_id,
						'meta'=>$autoassign['Marker']['uqid'],
					);
					$this->Activity->save($activitydata);
				}
			}
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
		    	foreach($annotationdata as $key=>&$annotationdatafile) {
			    	if(isset($annotationdatafile->type) && isset($annotationdatafile->filename) && $annotationdatafile->type == 'Recording') {
					   	$annotationdatafile->duration = $this->getAudioDuration($path.'/annots/'.$annotationdatafile->filename);
			   		} else {
			   			unset($annotationdata[$key]);
			   		}
				}
				usort($annotationdata, array($this, 'sortAnnotatations'));
		    	return $annotationdata;
		    } else {
		    	return array();
		    }
		} else {
	    	return array();
		}
	}
	
	function sortAnnotatations($a,$b) {
		$al = $a->page_no+($a->y_percentage/100);
		$bl = $b->page_no+($b->y_percentage/100);
		if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
	}
}
