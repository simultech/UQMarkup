<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class PagesController extends AppController {

	public $name = 'Pages';

	public $uses = array('Course','CourseRoleUser','Activity','Course','Project');
	
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
	
	public function links() {
		$this->breadcrumbs = array('/pages/links/'=>'My Links');
		$userid = $this->Ldap->getUserID();
		$submissions = $this->Activity->find('all',array('conditions'=>array('state_id'=>$this->getStateId('Submitted'),'meta'=>$this->Ldap->getUQID())));
		foreach($submissions as &$submission) {
			$project = $this->Project->find('first',array('conditions'=>array('id'=>$submission['Submission']['project_id']),'recursive'=>-1));
			$submission['Project'] = $project['Project'];
			$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
			$submission['Course'] = $course['Course'];
			$published = $this->Activity->find('first',array('conditions'=>array('submission_id'=>$submission['Submission']['id'],'state_id'=>$this->getStateId('Published')),'recursive'=>-1));
			if(!empty($published)) {
				$submission['published'] = true;
			}
			$submission['encodedid'] = $this->encodeSubmissionID($submission['Submission']['id']);
		}
		uasort($submissions,array($this,'sort_by_course'));
		$this->set('submissions',$submissions);
	}

	public function home() {
		$debugshowall = false;
		$userid = $this->Ldap->getUserID();
		if($this->courseadmin) {
			//find existing courses
			$ccrole = $this->getRoleID('Course Coordinator');
			$course_teaching_ids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id','course_id'),'conditions'=>array('user_id'=>$userid,'role_id'=>$ccrole)));
			$courses_teaching = $this->Course->find('all',array('conditions'=>array('id'=>$course_teaching_ids),'order'=>array(array('year'=>'desc'),array('semester'=>'desc'))));
			$this->set('courses_teaching',$courses_teaching);
		}
		$tutorrole = $this->getRoleID('Tutor');
		$course_tutoring_ids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id','course_id'),'conditions'=>array('user_id'=>$userid,'role_id >='=>$tutorrole)));
		$courses_tutoring = $this->Course->find('all',array('conditions'=>array('id'=>$course_tutoring_ids),'order'=>array(array('year'=>'desc'),array('semester'=>'desc'))));
		$this->set('courses_tutoring',$courses_tutoring);
		$submissions = $this->Activity->find('all',array('conditions'=>array('state_id'=>$this->getStateId('Submitted'),'meta'=>$this->Ldap->getUQID())));
		if($debugshowall) {
			$submissions = $this->Activity->find('all',array('conditions'=>array('meta'=>$this->Ldap->getUQID())));
		}
		foreach($submissions as &$submission) {
			$project = $this->Project->find('first',array('conditions'=>array('id'=>$submission['Submission']['project_id']),'recursive'=>-1));
			$submission['Project'] = $project['Project'];
			$course = $this->Course->find('first',array('conditions'=>array('id'=>$submission['Project']['course_id']),'recursive'=>-1));
			$submission['Course'] = $course['Course'];
			$published = $this->Activity->find('first',array('conditions'=>array('submission_id'=>$submission['Submission']['id'],'state_id'=>$this->getStateId('Published')),'recursive'=>-1));
			if(!empty($published) || $debugshowall) {
				$submission['published'] = true;
			}
			$submission['encodedid'] = $this->encodeSubmissionID($submission['Submission']['id']);
		}
		uasort($submissions,array($this,'sort_by_course'));
		$this->set('submissions',$submissions);
	}
	
	function sort_by_course($a,$b) {
		if ($a == $b) {
        	return 0;
        }
        if ($a['Course']['year'] == $b['Course']['year']) {
        	if($a['Course']['semester'] == $b['Course']['semester']) {
        		return ($a['Course']['id'] > $b['Course']['id']) ? -1 : 1;
        	} else {
        		return ($a['Course']['semester'] > $b['Course']['semester']) ? -1 : 1;
        	}
        }
	    return ($a['Course']['year'] > $b['Course']['year']) ? -1 : 1;
	}
	
	public function contactus() {
		$this->breadcrumbs = array('/pages/contactus/'=>'Contact Us');
		if(!empty($this->data)) {
			$emaildata = array();
			$emailcontent = 'UQ ID: '.$this->Ldap->getUQID()."\n";
			$emailcontent .= 'Name: '.$this->data['name']."\n";
			$emailcontent .= 'Email: '.$this->data['email']."\n";
			$emailcontent .= 'Feedback Type: '.$this->data['feedbacktype']."\n";
			$emailcontent .= 'Feedback: '.$this->data['comments']."\n";
			$emaildata['subject'] = 'UQMarkup Feedback from '.$this->Ldap->getUQID().' on - '.$this->data['feedbacktype'];
			$emaildata['subject_short'] = 'UQMarkup Feedback';
			$emaildata['to'] = $this->Ldap->adminemails;
			$emaildata['replyto'] = $this->data['email'];
			$emaildata['content'] = $emailcontent;
			if($this->email($emaildata)) {
				$this->flash('Thank you for your feedback.','/',true);
			} else {
				$this->flash('Could not send email',false);
			}
		}
		$this->set('userdetails',$this->Ldap->userdetails);
	}
	
	public function ethicalclearance($confirm='') {
		$student = true;
		$adminroles = $this->CourseRoleUser->find('list',array('conditions'=>array('user_id'=>$this->Ldap->getUserID(),'role_id > 1')));
		if(sizeOf($adminroles) > 0) {
			$student = false;
		}
	
		$this->breadcrumbs = array('/pages/contactus/'=>'Ethical Clearance');
		if($confirm == 'yes') {
			$this->User->id = $this->Ldap->getUserID();
			if(isset($_POST['pastfeedback'])) {
				$this->User->saveField('pastfeedback',$_POST['pastfeedback']);
			}
			if(isset($_POST['futurefeedback'])) {
				$this->User->saveField('futurefeedback',$_POST['futurefeedback']);
			}
			$this->User->saveField('termsagreed','1');
			$this->redirect($this->Ldap->referer());
		} else if ($confirm == 'no') {
			$this->User->id = $this->Ldap->getUserID();
			$this->User->saveField('termsagreed','2');
			$this->redirect($this->Ldap->referer());
		} else {
			if($student) {
				$content = file_get_contents("/var/www/html/studenttermsofuse.html");
			} else {
				$content = file_get_contents("/var/www/html/ipadtermsofuse.html");
			}
			$this->set('content',$content);
		}
	}
}