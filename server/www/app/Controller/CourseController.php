<?php

App::uses('AppController', 'Controller');

class CourseController extends AppController {

	public $name = 'Course';

	public $uses = array('Course','CourseRoleUser','Assignedstudent','User','Activity','Project');
	
	public $components = array('Ldap');
	
	public $courseadmin = false;

	public $superadmin = false;

	public function beforeFilter() {
		parent::beforeFilter();
		if(!$this->Ldap->loggedin()) {
			$this->redirect(array('controller'=>'users','action'=>'login'));
		}
		if($this->Ldap->isAdmin()) {
			$this->courseadmin = true;
		}
        if($this->Ldap->isSuperAdmin()) {
            $this->superadmin = true;
        }
	}
	
	public function create() {
		$this->breadcrumbs = array('/course/create'=>'Add a new course');
		if($this->courseadmin) {
			if(!empty($this->data)) {
				$data = $this->data;
				$data['coursecode'] = strtoupper($data['coursecode']);
				$data['uid'] = $data['year'].'_'.$data['semester'].'_'.$data['coursecode'];
				$this->Course->set($data);
				if($this->Course->validates()) {
					if($this->Course->save($data)) {
						//Assign the user to be the course coordinator
						$association = array();
						$association['user_id'] = $this->Ldap->getUserIDForUQLogin($this->Ldap->getUQID());
						$association['role_id'] = $this->getRoleID('Course Coordinator');
						$association['course_id'] = $this->Course->id;
						$this->CourseRoleUser->save($association);
						$course = $this->Course->find('first',array('conditions'=>array('Course.id'=>$association['course_id'])));
						if(!empty($course)) {
							$this->refreshClassList($course);
						}
						$this->refreshWebdavPermissions($association['course_id']);
						$this->flashMessage('Course created','/',true);
					} else {
						$this->flashMessage('Could not create course','/');
					}
				} else {
					$this->flashMessage('Could not create course, course already exists','/');
					$formerrors = $this->Course->invalidFields();
					$this->set('formerrors',$formerrors);
					print_r($formerrors);
				}
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function getstaff($course_id) {
		$coordinatorids = $this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('course_id'=>$course_id,'role_id'=>$this->getRoleId('Course Coordinator'))));
		$tutorids = $this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('course_id'=>$course_id,'role_id'=>$this->getRoleId('Tutor'))));
		$coordinators = $this->User->find('all',array('conditions'=>array('id'=>$coordinatorids)));
		$this->set('roles',$this->roles);
		$tutors = $this->User->find('all',array('conditions'=>array('id'=>$tutorids)));
		$staff = array();
		$staff['Course Coordinator'] = $coordinators;
		$staff['Tutor'] = $tutors;
		return $staff;
	}
	
	public function managestaff($courseuid) {
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode,'/course/managestaff/'.$courseuid=>'Manage Staff');
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('course',$course);
				$this->set('staff',$this->getstaff($course['Course']['id']));
				
				//if we are saving data
				if(!empty($this->data)) {
					//check if it is a uq user
					$user_id = $this->Ldap->getUserIDForUQLogin($this->data['uqid']);
					if($user_id > 0) {
						//save the relationship
						if($this->CourseRoleUser->save(array('course_id'=>$this->data['course_id'],'role_id'=>$this->data['role_id'],'user_id'=>$user_id))) {
							$this->refreshWebdavPermissions($this->data['course_id']);	
							$this->flashMessage('Staff member added',$this->referer(),true);
						} else {
							$this->flashMessage('Could not add staff member','false');
						}
					} else {
						$this->flashMessage('Not a valid UQ ID','false');
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}

    public function changestaff($courseuid,$user_id) {
        if($this->courseadmin) {
            $course = $this->Course->findByUid($courseuid);
            if(empty($course)) {
                $this->permissionDenied('Not a valid course');
            }
            $coursecode = $course['Course']['coursecode'];
            //check if they are a course coordinator
            if($this->Ldap->isCourseCoordinator($courseuid)) {
                $association = $this->CourseRoleUser->find('first',array('conditions'=>array('CourseRoleUser.user_id'=>$user_id,'course_id'=>$course['Course']['id'],),'recursive'=>-1));
                if(!empty($association)) {
                    $role = $this->roles[$association['CourseRoleUser']['role_id']];
                    if ($association['CourseRoleUser']['role_id'] == 3) {
                        $association['CourseRoleUser']['role_id'] = 2;
                    } else if ($association['CourseRoleUser']['role_id'] == 2) {
                        $association['CourseRoleUser']['role_id'] = 3;
                    }
                    $this->CourseRoleUser->save($association);
                    $this->flashMessage('Staff member status changed',$this->referer(),true);
                } else {
                    $this->flashMessage('Not a valid association',$this->referer());
                }
            }  else {
                $this->permissionDenied('You are not a coordinator for this course');
            }
        } else {
            $this->permissionDenied('Not an authorised administrator');
        }
    }
	
	public function removestaff($courseuid,$user_id) {
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$association = $this->CourseRoleUser->find('first',array('conditions'=>array('CourseRoleUser.user_id'=>$user_id,'course_id'=>$course['Course']['id'],),'recursive'=>-1));
				if(!empty($association)) {
					$role = $this->roles[$association['CourseRoleUser']['role_id']];
					$candelete = false;
					if($role != "Course Coordinator" || $this->superadmin) {
						$candelete = true;
					}
					if($candelete) {
						$this->CourseRoleUser->delete($association['CourseRoleUser']['id']);
						$this->flashMessage('Staff member removed',$this->referer(),true);
					} else {
						$this->flashMessage('Could not remove staff member, please ask a super user to remove course coordinators',$this->referer());
					}
				} else {
					$this->flashMessage('Not a valid association',$this->referer());
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function updateassign($courseuid) {
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
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
                        $csvdata = preg_split("/\\r\\n|\\r|\\n/", $csvdata);
			    		$i = 0;
			    		$tutorassigns = array();
			    		foreach($csvdata as $csvline) {
			    			$i++;
			    			//ignore the headers
			    			if($i<2) {
			    				continue;
			    			}
			    			$csvcolumns = explode(",",$csvline);
			    			if($csvcolumns[0] != "") {
			    				$tutorassigns[$csvcolumns[1]][] = $csvcolumns[0];
			    			}
			    		}
			    		fclose($handle);
			    		$assignedusers = 0;
			    		foreach($tutorassigns as $tutorassign=>$students) {
			    			foreach($students as $student) {
			    				//get cru )
			    				$assignedusers += $this->assignStudent(trim($student),trim($tutorassign),$course['Course']['id']);
			    			}
			    		}
			    		$this->flashMessage('Assigned '.$assignedusers.' students',$this->referer(),true);
			    	} else {
			    		$this->flashMessage('Could not open file',$this->referer());
			    	}
			    }
			}
		}
	}
	
	public function comparelists($courseuid) {
		ini_set('memory_limit','384M');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode);
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->compareClassLists($course);
			}
		}
		$this->flashMessage('Course list refreshed','/course/admin/'.$courseuid,true);
	}
	
	public function refreshlist($courseuid) {
		ini_set('memory_limit','384M');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode);
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->refreshClassList($course);
			}
		}
		$this->flashMessage('Course list refreshed','/course/admin/'.$courseuid,true);
	}
	
	public function admin($courseuid,$showclasslist='') {
		ini_set('memory_limit','384M');
		ini_set('max_execution_time','480');
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$coursecode);
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('course',$course);
				//$this->refreshClassList($course);
				//get the student list
				$role_id = $this->getRoleID('Student');
				$studentids = $this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('role_id'=>$role_id,'course_id'=>$course['Course']['id'])));
				if($showclasslist == 'showclass') {
					$students = $this->User->find('all',array('conditions'=>array('id'=>$studentids),'order'=>array('uqid')));
					$markerlist = $this->Assignedstudent->find('all',array('conditions'=>array('courseroleuser_id'=>array_keys($studentids))));
					$automarklist = array();
					foreach($markerlist as $markerlistitem) {
						$automarklist[$markerlistitem['Student']['user_id']] = $markerlistitem['Marker']['uqid'];
					}
					$this->set('students',$students);
					$this->set('automarklist',$automarklist);
				}
				$staff = $this->getstaff($course['Course']['id']);
				$this->set('staff',$staff);
				$staffstatus = 'incomplete';
				$projectstatus = 'incomplete';
				if(sizeOf($staff['Course Coordinator']) + sizeOf($staff['Tutor']) > 1) {
					$staffstatus = 'complete';	
				}
				if(isset($course['Project']) && sizeOf($course['Project']) > 0) {
					$projectstatus = 'complete';	
				}
				$workflow = array(
			        array(
			            'name'=>'Create Course',
			            'class'=>'icon_addcourse',
			            'status'=>'complete',
			        ),
			        array(
			            'name'=>'Add Teaching Staff',
			            'class'=>'icon_addstaff',
			            'status'=>$staffstatus,
			            'link'=>$this->baseURL.'/course/managestaff/'.$course['Course']['uid'],
			        ),
			        array(
			            'name'=>'Create Projects',
			            'class'=>'icon_projects',
			            'status'=>$projectstatus,
			            'link'=>$this->baseURL.'/projects/create/'.$course['Course']['uid'],
			        )
				);
				$this->set('workflow',$workflow);
				$this->set('projects',$this->Project->find('all',array('conditions'=>array('course_id'=>$course['Course']['id']),'order'=>array('Project.name'))));
				
				//if we are saving data
				if(!empty($this->data)) {
					$this->Course->set($this->data);
					if($this->Course->validates()) {
						$data = $this->data;
						$data['coursecode'] = strtoupper($data['coursecode']);
						$data['uid'] = $data['year'].'_'.$data['semester'].'_'.$data['coursecode'];
						if($this->Course->save($data)) {
							$course = $this->Course->find('first',array('conditions'=>array('Course.id'=>$this->data['id'])));
							if(!empty($course)) {
								$this->refreshClassList($course);
							}
							$this->flashMessage('Course updated','/course/admin/'.$data['uid'],true);
						} else {
							$this->flashMessage('Could not update course','false');
						}
					} else {
						$formerrors = $this->Course->invalidFields();
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
	
	public function staffcsv() {
		header("Content-type: application/x-msdownload");
		header("Content-Disposition: attachment; filename=stafflist.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo "UQ ID,'Tutor' or 'Course Coordinator'\n";
		die();
	}
	
	public function staffcsvupload() {
		$course_id = $this->data['course_id'];
		if($this->courseadmin) {
			$course = $this->Course->findById($course_id);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			$coursecode = $course['Course']['coursecode'];
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($course['Course']['uid'])) {
				if(isset($_FILES['csv']) && $_FILES['csv']['error'] == 0) {
					$ext = pathinfo($_FILES['csv']['name'], PATHINFO_EXTENSION);
					if($ext != 'csv') {
						$this->flashMessage('Please provide a CSV file','/course/managestaff/'.$course['Course']['uid']);
						die();
					}
					//file should be all good
					if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== FALSE) {
						$csvdata = file_get_contents($_FILES['csv']['tmp_name']);
						$csvdata = preg_split("/\\r\\n|\\r|\\n/", $csvdata);
						if(sizeOf($csvdata) < 2) {
							$csvdata = explode("\n",$csvdata);
						}
						$i = 0;
						$staffids = array();
						foreach($csvdata as $csvline) {
							$i++;
							//ignore the headers
							if($i<2) {
								continue;
							}
							$csvcolumns = explode(",",$csvline);
							if($csvcolumns[0] != "") {
								$staffids[$csvcolumns[0]] = $csvcolumns[1];
							}
						}
						fclose($handle);
						$staffadded = 0;
						$badstaff = array();
						foreach($staffids as $staffid=>$role) {
							$role_id = 0;
							if($role == 'Tutor') {
								$role_id = 2;
							} else if($role == 'Course Coordinator') {
								$role_id = 3;
							}
							$user_id = $this->Ldap->getUserIDForUQLogin($staffid);
							if($user_id > 0 && $role_id > 0) {
								//save the relationship
								$this->CourseRoleUser->create();
								if($this->CourseRoleUser->save(array('course_id'=>$course_id,'role_id'=>$role_id,'user_id'=>$user_id))) {
									$staffadded++;
								} else {
									$badstaff[] = $staffid;
								}
							} else {
								$badstaff[] = $staffid;
							}
						}
						$badstaffstring = '';
						if(sizeOf($badstaff) > 0) {
							$badstaffstring = ' Could not add ';
							foreach($badstaff as $badstaffmember) {
								$badstaffstring .= $badstaffmember.', ';
							}
						}
						$this->refreshWebdavPermissions($course_id);
						$this->flashMessage($staffadded.' staff members added.'.$badstaffstring,'/course/managestaff/'.$course['Course']['uid'],true);
					}
				}
			}  else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
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
	        		$assignedusers++;
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
		        $projects = array_keys($this->Project->find('list',array('conditions'=>array('course_id'=>$course_id))));
		        $potentialsubmissions = $this->Activity->find('all',array('conditions'=>array('meta'=>$studentid,'state_id'=>1,'Submission.project_id'=>$projects)));
		        foreach($potentialsubmissions as $potentialsubmission) {
		            $states = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$potentialsubmission['Submission']['id'])));
		            $maxstate = 0;
		            foreach($states as $state) {
		                if($state['Activity']['state_id'] > $maxstate) {
		                	$maxstate = $state['Activity']['state_id'];
		                }
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
	        }
	    }
	    return $assignedusers;
	}
	
	public function display() {
		
	}
}
