<?php

App::uses('AppController', 'Controller');

class TutorController extends AppController {

	public $name = 'Tutor';

	public $uses = array('Course','CourseRoleUser','Project','State','Submission','Assignedstudent','Activity');
	
	public $components = array('Ldap');
	
	public $courseadmin = false;
	
	public $ignore_projects = array('90');

	public function beforeFilter() {
		parent::beforeFilter();
		if(!$this->Ldap->loggedin()) {
			$this->redirect(array('controller'=>'users','action'=>'login'));
		}
		if($this->Ldap->isAdmin()) {
			$this->courseadmin = true;
		}
	}
	
	public function classlist($courseuid) {
		$this->breadcrumbs = array('/tutor/admin/'.$courseuid=>'Marking tools for '.$courseuid,'/tutor/classlist'=>'Classlist');
		$course = $this->Course->findByUid($courseuid);
		if(empty($course)) {
			$this->permissionDenied('Not a valid course');
		}
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'course_id'=>$course['Course']['id'],'role_id >'=>'1')));
		if(empty($courseroleuser)) {
			$this->permissionDenied('You do not have access to this course');
		}
		$role_id = $this->getRoleID('Student');
		$studentids = $this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('role_id'=>$role_id,'course_id'=>$course['Course']['id'])));
		$students = $this->User->find('all',array('conditions'=>array('id'=>$studentids),'order'=>array('uqid')));
		$markerlist = $this->Assignedstudent->find('all',array('conditions'=>array('courseroleuser_id'=>array_keys($studentids))));
		$automarklist = array();
		foreach($markerlist as $markerlistitem) {
		    $automarklist[$markerlistitem['Student']['user_id']] = $markerlistitem['Marker']['uqid'];
		}
		$this->set('students',$students);
		$this->set('automarklist',$automarklist);
	}
	
	public function admin($courseuid) {
		$this->breadcrumbs = array('/tutor/admin'=>'Marking tools for '.$courseuid);
		$course = $this->Course->findByUid($courseuid);
		if(empty($course)) {
			$this->permissionDenied('Not a valid course');
		}
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'course_id'=>$course['Course']['id'],'role_id >'=>'1')));
		if(empty($courseroleuser)) {
			$this->permissionDenied('You do not have access to this course');
		}
		$this->set('tutordetails',$courseroleuser);
		$coursecode = $course['Course']['coursecode'];
		$this->set('course',$course);
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$course['Course']['id']),'recursive'=>-1));
		foreach($projects as &$project) {
			$this->Submission->unbindModel(array('hasMany' => array('Log')));
			$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project['Project']['id'])));
			foreach($submissions as &$submission) {
			    $submission['Submission']['encode_id'] = $this->encodeSubmissionID($submission['Submission']['id']);
			}
			$project['Submission'] = $submissions;
		}
		if(!empty($this->data)) {
			if(!empty($_FILES) && isset($_FILES['uq_id_list']) && $_FILES['uq_id_list']['error'] == 0) {
				$ext = pathinfo($_FILES['uq_id_list']['name'], PATHINFO_EXTENSION);
				if($ext != 'csv') {
					$this->flashMessage('Please provide a CSV file',$this->referer());
					die();
				}
				//file should be all good
				if (($handle = fopen($_FILES['uq_id_list']['tmp_name'], "r")) !== FALSE) {
					$csvdata = file_get_contents($_FILES['uq_id_list']['tmp_name']);
					$csvdata = explode("\r",$csvdata);
					if(!is_array($csvdata)) {
						$csvdata = explode("\n",$csvdata);
					}
					$i = 0;
					$studentids = array();
					foreach($csvdata as $csvline) {
						$i++;
						//ignore the headers
						if($i<2) {
							continue;
						}
						$csvcolumns = explode(",",$csvline);
						if($csvcolumns[0] != "") {
							$studentids[] = $csvcolumns[0];
						}
					}
					fclose($handle);
					$assignedusers = 0;
					foreach($studentids as $studentid) {
						//get cru 
						$assignedusers += $this->assignStudent(trim($studentid),$this->Ldap->getUQID(),$course['Course']['id']);
					}
					$this->flashMessage('Assigned '.$assignedusers.' students',$this->referer(),true);
				} else {
					$this->flashMessage('Could not open file',$this->referer());
				}
			} else {
				//should be re-assigning of some kind
				if(isset($this->data['course_id'])) {
					$assignedusers = 0;
					$assignedusers += $this->assignStudent($this->data['uq_id'],$this->Ldap->getUQID(),$this->data['course_id']);
					$this->flashMessage('Assigned '.$assignedusers.' students',$this->referer(),true);
				} else {
					$submissions = $this->Submission->find('list',array('conditions'=>array('project_id'=>$this->data['project_id'])));
					$identifyactivites = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submissions,'state_id'=>'1','meta'=>$this->data['uq_id']),'recursive'=>-1));
					if(!empty($identifyactivites)) {
						$output = '';
						foreach($identifyactivites as $identifyactivity) {
							$states = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$identifyactivity['Activity']['submission_id']),'recursive'=>-1));
							$maxstate = 0;
							$maxstateactivity;
							foreach($states as $state) {
								if($state['Activity']['state_id'] > $maxstate) {
									$maxstate = $state['Activity']['state_id'];
									$maxstateactivity = $state;
								}
							}
							if($maxstate <1 || $maxstate > 2) {
								$output .= 'Submission '.$identifyactivity['Activity']['submission_id'].' already marked.  ';
							} else {
								if ($maxstate == 2) {
									//delete current assign
									$this->Activity->delete($maxstateactivity['Activity']['id']);
								}
								sleep(0.1);
								$newactivity = array(
									'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
									'state_id'=>'2',
									'submission_id'=>$maxstateactivity['Activity']['submission_id'],
									'meta'=>$this->Ldap->getUQID()
								);
								$this->Activity->create();
								$this->Activity->save($newactivity);
								$output .= 'Submission '.$identifyactivity['Activity']['submission_id'].' reassigned.  ';
							}
						}
						$this->flashMessage($output,$this->referer(),true);
					} else {
						$this->flashMessage('Could not find a submission for '.$this->data['uq_id'],$this->referer());
					}
				}
			}
		}
		
		$studentlist = $this->Assignedstudent->find('all',array('conditions'=>array('marker_id'=>$this->Ldap->getUserId(),'Assignedstudent.course_id'=>$course["Course"]['id'])));
		$studentids = array();
		foreach($studentlist as $studentlistitem) {
			$studentids[] = $studentlistitem['Student']['user_id'];
		}
		$students = $this->User->find('all',array('conditions'=>array('id'=>$studentids),'recursive'=>-1));
		$this->set('students',$students);
		$this->set('projects',$projects);
	}
	
	function assignStudent($studentid,$tutorid,$course_id) {
		//setup the user
		$theuser = $this->User->find('first',array('conditions'=>array('uqid'=>$studentid),'recursive'=>-1));
		$thetutor = $this->User->find('first',array('conditions'=>array('uqid'=>$tutorid),'recursive'=>-1));
		$assignedusers = 0;
	    if(!empty($theuser)) {
	        $courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$theuser['User']['id'],'course_id'=>$course_id),'recursive'=>-1));
	        if(!empty($courseroleuser)) {
	        	//check if it exists
	        	$existingassigned = $this->Assignedstudent->find('first',array('conditions'=>array('courseroleuser_id'=>$courseroleuser['CourseRoleUser']['id']),'recursive'=>-1));
	        	if($existingassigned) {
	        		$this->Assignedstudent->id = $existingassigned['Assignedstudent']['id'];
	        		$this->Assignedstudent->saveField('marker_id',$thetutor['User']['id']);
	        	} else {
	        		$assigned_data = array(
	        			'courseroleuser_id'=>$courseroleuser['CourseRoleUser']['id'],
	        			'marker_id'=>$thetutor['User']['id'],
	        			'course_id'=>$course_id,
	        		);
	        		$this->Assignedstudent->create();
	        		if($this->Assignedstudent->save($assigned_data)) {
	        			$assignedusers++;
	        		}
	        	}
	        	//find submissions that belong to the user for the course
		        $projects = array_keys($this->Project->find('list',array('conditions'=>array('course_id'=>$course_id, 'not'=>array('option_disable_autoassign'=>'1')))));
		        $projects = array_diff($projects, $this->ignore_projects);
		        $potentialsubmissions = $this->Activity->find('all',array('conditions'=>array('meta'=>$studentid,'state_id'=>1,'Submission.project_id'=>$projects)));
		        foreach($potentialsubmissions as $potentialsubmission) {
		            $states = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$potentialsubmission['Submission']['id'])));
		            $maxstate = 0;
		            foreach($states as $state) {
		                if($state['Activity']['state_id'] > $maxstate) {
		                	$maxstate = $state['Activity']['state_id'];
		                }
		            }
		            if($maxstate == 2) {
			            foreach($states as $state) {
			                if($state['Activity']['state_id'] == 2) {
			            		$this->Activity->delete($state['Activity']['id']);
			            	}
			            }
			            $maxstate = 1;
		            }
		            if($maxstate == 1) {
		            	//good for assigning
		            	sleep(0.1);
		                $newactivity = array(
		            		'course_role_users_id'=>$courseroleuser['CourseRoleUser']['id'],
		            		'state_id'=>'2',
		            		'submission_id'=>$potentialsubmission['Submission']['id'],
		            		'meta'=>$thetutor['User']['uqid']
		            	);
		            	$this->Activity->create();
		            	$this->Activity->save($newactivity);
		            }
		        }
	        	//$assignedusers++;
	        }
	    }
	    return $assignedusers;
	}
	
	public function unassignstudent($course_id,$user_id) {
		$course = $this->Course->findById($course_id);
		if(empty($course)) {
			$this->permissionDenied('Not a valid course');
		}
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'course_id'=>$course['Course']['id'],'role_id >'=>'1')));
		if(empty($courseroleuser)) {
			$this->permissionDenied('You do not have access to this course');
		}
		$studentcourseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$course_id,'user_id'=>$user_id),'recursive'=>-1));
		if(!empty($studentcourseroleuser)) {
			$existingassigned = $this->Assignedstudent->find('first',array('conditions'=>array('courseroleuser_id'=>$studentcourseroleuser['CourseRoleUser']['id'],'marker_id'=>$this->Ldap->getUserID()),'recursive'=>-1));
			if($existingassigned) {
				if($this->Assignedstudent->delete($existingassigned['Assignedstudent']['id'])) {
					$this->flashMessage('Unassigned student',$this->referer(),true);
				} else {
					$this->flashMessage('Could not unassign student',$this->referer());
				}
			} else {
				$this->flashMessage('Invalid student',$this->referer());
			}
		} else {
			$this->flashMessage('Invalid student',$this->referer());
		}
	}
}
