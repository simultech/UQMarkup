<?php

App::uses('AppController', 'Controller');

class SurveysController extends AppController {

	public $name = 'Surveys';

	public $uses = array('Course','CourseRoleUser','Logtype','Submission','Attachment','Version','Rubric','Surveyresult','Project','Activity');
	
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
	
	public function results($project_id) {
		$this->helpers[] = 'Extra';
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$project_name = $project['Course']['name'].' - '.$project['Project']['name'].' ('.$project['Course']['year'].'/'.$project['Course']['semester'].')';
				$this->set('project_name',$project_name);
				$this->set('title_for_layout', 'Survey_'.$project_name.'_'.date('Y-m-d'));
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Survey Responses');
				ini_set("auto_detect_line_endings", 1);
				$responses = array();
				$responsedata = $this->Surveyresult->find('all',array('conditions'=>array('project_id'=>$project_id,'user_id > 1'),'recursive'=>-1));
				foreach($responsedata as $response) {
					$responses[$response['Surveyresult']['survey_id']][$response['Surveyresult']['question_id']][] = $response['Surveyresult']['answer'];
				}
				$this->set('responses',$responses);
				$surveys = array();
				for($i=1; $i<5; $i++) {
					$surveypath = $this->surveydir.$project_id.'_'.$i.'.csv';
					if(file_exists($surveypath)) {
						$surveys[$i]['questions'] = $this->get2DArrayFromCsv($surveypath,",");
					}
				}
				$this->set('surveys',$surveys);
				
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function resultsraw($project_id) {
		$this->layout = false;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=surveyresults_".$project_id.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->helpers[] = 'Extra';
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$project_name = $project['Course']['name'].' - '.$project['Project']['name'].' ('.$project['Course']['year'].'/'.$project['Course']['semester'].')';
				$this->set('project_name',$project_name);
				$this->set('title_for_layout', 'Survey_'.$project_name.'_'.date('Y-m-d'));
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Survey Responses');
				ini_set("auto_detect_line_endings", 1);
				$responses = array();
				$responsedata = $this->Surveyresult->find('all',array('conditions'=>array('project_id'=>$project_id,'user_id > 1'),'recursive'=>-1));
				foreach($responsedata as $response) {
					$responses[$response['Surveyresult']['survey_id']][$response['Surveyresult']['question_id']][] = $response['Surveyresult']['answer'];
				}
				$this->set('responses',$responses);
				$surveys = array();
				for($i=1; $i<5; $i++) {
					$surveypath = $this->surveydir.$project_id.'_'.$i.'.csv';
					if(file_exists($surveypath)) {
						$surveys[$i]['questions'] = $this->get2DArrayFromCsv($surveypath,",");
					}
				}
				$this->set('surveys',$surveys);
				
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function resultsbystudent($project_id) {
		$this->layout = false;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=resultsbystudent_".$project_id.".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->helpers[] = 'Extra';
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$project_name = $project['Course']['name'].' - '.$project['Project']['name'].' ('.$project['Course']['year'].'/'.$project['Course']['semester'].')';
				$this->set('project_name',$project_name);
				$this->set('title_for_layout', 'Survey_'.$project_name.'_'.date('Y-m-d'));
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Survey Responses');
				ini_set("auto_detect_line_endings", 1);
				$responses = array();
				$responsedata = $this->Surveyresult->find('all',array('conditions'=>array('project_id'=>$project_id,'user_id > 1'),'recursive'=>-1));
				$questions = array();
				/*foreach($responsedata as $response) {
					$responses[$response['Surveyresult']['survey_id']][$response['Surveyresult']['question_id']][] = $response['Surveyresult']['answer'];
				}*/
				$submissionids = array();
				$users = array();
				$markers = array();
				foreach($responsedata as $response) {
					if(!isset($questions[$response['Surveyresult']['survey_id']]) || !in_array($response['Surveyresult']['question_id'], $questions[$response['Surveyresult']['survey_id']])) {
						$questions[$response['Surveyresult']['survey_id']][] = $response['Surveyresult']['question_id'];
					}
					if(!in_array($response['Surveyresult']['user_id'], $users)) {
						$users[] = $response['Surveyresult']['user_id'];
					}
					$responses[$response['Surveyresult']['survey_id']][$response['Surveyresult']['user_id']][$response['Surveyresult']['question_id']] = $response['Surveyresult']['answer'];
				}
				foreach($questions as &$questionset) {
					usort($questionset,array($this,'questionSort'));
				}
				$users = $this->User->find('list',array('conditions'=>array('id'=>$users),'fields'=>array('id','uqid'),'recursive'=>-1));
				foreach($users as $userid=>$username) {
					$markers[$userid] = 'Unknown';
					$submissionids[$userid] = 'Unknown';
					$potentialsubmissions = $this->Activity->find('all',array('conditions'=>array('state_id'=>1,'meta'=>$username)));
					$submission = array();
					foreach($potentialsubmissions as $potentialsubmission) {
						if($potentialsubmission['Submission']['project_id'] == $project_id) {
							$submission = $potentialsubmission;
						}
					}
					if(!empty($submission)) {
						$submissionids[$userid] = $submission['Submission']['id'];
						$submission = $this->Submission->findById($submission['Submission']['id']);
						foreach($submission['Activity'] as $activity) {
							if($activity['state_id'] == '2') {
								$markers[$userid] = $activity['meta'];
							}
						}
					}
				}
				$this->set('submissionids',$submissionids);
				$this->set('users',$users);
				$this->set('markers',$markers);
				$this->set('responses',$responses);
				$this->set('questions',$questions);
				$surveys = array();
				for($i=1; $i<5; $i++) {
					$surveypath = $this->surveydir.$project_id.'_'.$i.'.csv';
					if(file_exists($surveypath)) {
						$surveys[$i]['questions'] = $this->get2DArrayFromCsv($surveypath,",");
					}
				}
				$this->set('surveys',$surveys);
				
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function questionSort($item1,$item2) {
		if(intval($item1) < 10) {
			$item1 = '0'.$item1;
		}
		if(intval($item2) < 10) {
			$item2 = '0'.$item2;
		}
		if ($item1 == $item2) return 0;
		return ($item1 > $item2) ? 1 : -1;
	}
	
	public function savesurvey($survey_name=false,$project_id=false) {
		if(!$project_id || !$survey_name) {
			$this->permissionDenied('Invalid survey');
		} 
		//validate whether its available
		$valid = true;
		$survey_id = 0;
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
		if($valid) {
			foreach($_POST['data']['response'] as $questionid=>$questionresponse) {
				$existing = $this->Surveyresult->find('first',array('conditions'=>array(
					'project_id'=>$project_id,
					'user_id'=>$this->Ldap->getUserID(),
					'question_id'=>$questionid,
					'survey_id'=>$survey_id,
				),'order'=>array('id'=>'desc')));
				$valid = true;
				if(!empty($existing)) {
					if($existing['Surveyresult']['answer'] == $questionresponse) {
						$value = false;
					}
				}
				if($valid) {
					$this->Surveyresult->create();
					$surveydata = array(
						'project_id'=>$project_id,
						'user_id'=>$this->Ldap->getUserID(),
						'question_id'=>$questionid,
						'survey_id'=>$survey_id,
						'answer'=>$questionresponse,
					);
					$this->Surveyresult->save($surveydata);
				}
			}
      echo "1";
      die();
		} else {
		  echo "0";
      die();
		}
	}
	
	public function survey($survey_name=false,$project_id=false,$alwaysshow=false) {
		if(!$project_id || !$survey_name) {
			$this->permissionDenied('Invalid survey');
		} 
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$this->breadcrumbs = array('/surveys/survey/'=>'Markup Survey');
		//validate whether its available
		$valid = false;
		$survey_id = 0;
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
		if($survey_id == 1) {
			$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'course_id'=>$project['Course']['id'],'role_id'=>1)));
			if($courseroleuser) {
				$valid = true;
			}
		} else {
			$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'course_id'=>$project['Course']['id'],'role_id > 1')));
			if($courseroleuser) {
				$valid = true;
			}
		}
		if($valid) {
			$responseData = $this->Surveyresult->find('all',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'project_id'=>$project_id,'survey_id'=>$survey_id)));
			$this->set('course',$project['Course']);
			
			$existingresponses = array();
			foreach ($responseData as $rdata) {
				$existingresponses[$rdata['Surveyresult']['question_id']] = $rdata['Surveyresult']['answer'];
			}
      
			$this->set('existingresponses',$existingresponses);
			//load the csv
			ini_set("auto_detect_line_endings", 1);
			$surveypath = $this->surveydir.$project_id.'_'.$survey_id.'.csv';
			if(file_exists($surveypath)) {
				$questions = $this->get2DArrayFromCsv($surveypath,",");
				$this->set('questions',$questions);
				$this->set('survey_name', $survey_name);
				$this->set('project_id', $project_id);
			} else {
				$this->permissionDenied('You cannot complete this survey, could not find survey');
			}
		} else {
			$this->permissionDenied('You cannot complete this survey');
		}
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
		$this->set('course',$project['Course']);
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