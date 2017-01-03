<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class ApiController extends AppController {

	public $name = 'Api';

	public $uses = array('Course','CourseRoleUser','Project','Submission','Attachment','Version','Activity','User');
	
	public $components = array('Ldap');
	
	var $showhtml = false;
	
	var $secretkey = '';
	
	var $response = array();
	var $error = array();
	var $httpcode;
	var $httpcodes = array(
		'200'=>'HTTP/1.1 200 OK',
		'400'=>'HTTP/1.1 400 Bad Request',
		'401'=>'HTTP/1.1 401 Unauthorized',
		'403'=>'HTTP/1.1 403 Forbidden',
		'404'=>'HTTP/1.1 404 Not Found',
		'405'=>'HTTP/1.1 405 Method Not Allowed',
		'500'=>'HTTP/1.1 500 Internal Server Error',
	);
	
	function beforeFilter() {
		if($this->secretkey == '') {
			echo 'SECRET KEY MISSING FROM ApiController.php';
			die();
		}
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		$this->httpcode = '200';
		$apiauthed = false;
		if(isset($_GET['secret'])) {
			if($_GET['secret'] == $this->secretkey) {
				$apiauthed = true;
			}
		}
		if(isset($_POST['secret'])) {
			if($_POST['secret'] == $this->secretkey) {
				$apiauthed = true;
			}	
		}
		
		if(!$apiauthed && $this->request->params['action'] != 'help') {
			$this->httpcode = '403';
			$this->error = array('Unauthorized API access');
			$this->beforeRender();
		}
		parent::beforeFilter();
	}
	
	function checkingCLI() {
		echo 'perfect';
		die();
	}
	
	var $endpoints = array(
		'submissionFile' => array(
			'method'=>'GET',
			'auth' => true,
			'url' => 'submissionFile/{submission_id}/{file_id=false}',
			'errors' => array(
				'500','404'
			),
			'response' => 'FILE DATA',
		),
		'submissionDetails' => array(
			'method'=>'GET',
			'auth' => true,
			'url' => 'submissionDetails/{submission_id}/{file_id=false}',
			'errors' => array(
				'404'
			),
			'response' => '',
		),
		'login' => array(
			'method'=>'POST',		
			'auth' => false,
			'fields'=>array(
				'username','password'
			),
			'url' => 'login',
			'errors' => array(
				'401'
			),
			'response' => '',
		),
		'projectlist' => array(
			'method'=>'GET',		
			'auth' => true,
			'url' => 'projectlist',
			'errors' => array(
			),
			'response' => '',
		),
		'project' => array(
			'method'=>'GET',		
			'auth' => true,
			'url' => 'project/{project_id}',
			'errors' => array(
			),
			'response' => '',
		),
		'userdetails' => array(
			'method'=>'GET',		
			'auth' => true,
			'url' => 'userdetails',
			'errors' => array(
			),
			'response' => '',
		),
		'logout' => array(
			'method'=>'GET',		
			'auth' => false,
			'url' => 'logout',
			'errors' => array(
			),
			'response' => '',
		),
		'isloggedin' => array(
			'method'=>'GET',		
			'auth' => false,
			'url' => 'isloggedin',
			'errors' => array(
				'403'
			),
			'response' => '',
		),
		'uploadSubmission' => array(
			'method'=>'POST',
			'auth'=>true,
			'fields'=>array(
				'submissiondata'
			),
			'url'=>'uploadSubmission/{submission_id}',
			'errors'=>array(
				'400'
			),
			'response'=>'true'
		),
	);
	
	public function submissionFile($submission_id,$file_id=false) {
		$this->requireAuth();
		
		if(in_array($submission_id, $this->fakeSubmissionIDs())) {
		//if($submission_id == "999999" || $submission_id == "999998" || $submission_id == "999997") {
			$path = '/var/www/webdav/fake/'.$submission_id.'.pdf';
			if (file_exists($path) && is_readable ($path)) {
				ini_set('memory_limit','256M');				
				header("Content-length: ".filesize($path));
				header('Content-type: '.mime_content_type($path));
				header('Content-MD5: '.md5_file($path));
				ob_end_flush();
				readfile($path);
			} else {
				$this->error = 'Could not load file';
				$this->httpcode = '500';	
			}
		}
		
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		if(!empty($submission)) {
			$this->requireProjectAccess($submission['Project']['id']);
			if($file_id) {
				$file = $this->Attachment->find('first',array('conditions'=>array('Attachment.id'=>$file_id),'recursive'=>-1));
			} else {
				$file = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1));
			}
			if($file) {
				$path = $this->binarydir.$file['Attachment']['path'];
				if (file_exists($path) && is_readable ($path)) {
					ini_set('memory_limit','256M');				
					header("Content-length: ".filesize($path));
					header('Content-type: '.mime_content_type($path));
					header('Content-MD5: '.md5_file($path));
					ob_end_flush();
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
	
	public function moderationFile($submission_id,$file_id=false) {
		$this->requireAuth();
		
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		if(!empty($submission)) {
			$this->requireProjectAccess($submission['Project']['id']);
			$file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
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
				    	ini_set('memory_limit','256M');				
				    	header("Content-length: ".filesize($file));
				    	header('Content-type: '.mime_content_type($file));
				    	ob_end_flush();
				    	readfile($file);
				    } else {
				    	if(!file_exists($file)) {
					    	$this->error = 'Could not load file '.$file;
					    }
					    if(!is_readable($file)) {
					    	$this->error = 'Could not load file 2 *'.$file.'*';
					    }
					    if (file_exists($file) && is_readable ($file)) {
					    	ini_set('memory_limit','256M');				
					    	header("Content-length: ".filesize($file));
					    	header('Content-type: '.mime_content_type($file));
					    	ob_end_flush();
				    		readfile($file);
				    	}
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
	}
	
	public function submissionDetails($submission_id,$file_id=false) {
		$this->requireAuth();
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		if(!empty($submission)) {
			$this->requireProjectAccess($submission['Project']['id']);
			if($file_id) {
				$file = $this->Attachment->find('first',array('conditions'=>array('Attachment.id'=>$file_id)));
			} else {
				$file = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission_id)));	
			}
			if($file) {
				$this->response = $file;
			} else {
				$this->error = 'File not found';
				$this->httpcode = '404';
			}
		} else {
			$this->error = 'Submission not found';
			$this->httpcode = '404';
		}
	}
	
	public function login() {
		$data = $this->validateValues('POST',array('username','password'));
		$result = $this->Ldap->login($data['username'],$data['password']);
		if(isset($result['error'])) {
			$this->error = $result['error'];
			$this->httpcode = '401';
		} else {
			$this->response = 'true';
		}
	}
	
	public function isloggedin() {
		$this->requireAuth();
		$this->response = 'true';
	}
	
	private function getcurrenttime() {
		$mtime = explode(" ",microtime()); 
		$mtime = $mtime[1] + $mtime[0]; 
		return $mtime;
	}
	
	public function projectlist() {
		$this->requireAuth();
		$user_id = $this->Ldap->getUserID();
		
		$semester = '1';
		if(intval(date('m')) > 7) {
			$semester = '2';
		}
		$courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 1','user_id'=>$user_id)));
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids,'Course.year'=>date('Y'),'Course.semester'=>$semester),'order'=>array('Course.year'=>'desc','Course.uid'=>'asc','Project.name'=>'asc')));

		$projectsdata = $this->detailProjects2($projects);
		if(sizeOf($projectsdata) == 0) {
			$projectsdata = $this->filteredFakeProjects($courseids);
		} else {
			$projectsdata = array_merge($projectsdata,$this->filteredFakeProjects($courseids));
		}
		$this->response = $projectsdata;
	}
	
	public function projectlist2() {
		
		$this->requireAuth();
		$user_id = $this->Ldap->getUserID();

		$courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 1','user_id'=>$user_id)));
		
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids,'Course.year'=>date('Y')),'order'=>array('Course.year'=>'desc','Course.uid'=>'asc','Project.name'=>'asc')));

		$projectsdata = $this->detailProjects2($projects);
		
		if(sizeOf($projectsdata) == 0) {
			$projectsdata = $this->filteredFakeProjects($courseids);
		} else {
			$projectsdata = array_merge($projectsdata,$this->filteredFakeProjects($courseids));
		}
		$this->response = $projectsdata;
	}
	
	public function moderationlist() {
		$this->requireAuth();
		
        $user_id = $this->Ldap->getUserID();

        $courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 2','user_id'=>$user_id)));
        $projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids,'Course.year'=>date('Y')),'order'=>array('Course.year'=>'desc','Course.uid'=>'asc','Project.name'=>'asc')));

        $projectsdata = $this->detailModerationProjects($projects);
		$this->response = $projectsdata;
	}
	
	public function projectlisttest() {
		$this->requireAuth();
		$user_id = $this->Ldap->getUserID();
		$courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 1','user_id'=>$user_id)));
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids),'order'=>array('Course.uid','Project.name')));
		$projectsdata = $this->detailProjectsTwo($projects);
		if(sizeOf($projectsdata) == 0) {
			$projectsdata = $this->fakeProjects();
		} else {
			$projectsdata = array_merge($projectsdata,$this->fakeProjects());
		}
		$this->response = $projectsdata;
	}
	
	public function projectlistarray() {
		$this->requireAuth();
		$user_id = $this->Ldap->getUserID();
		$courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 1','user_id'=>$user_id)));
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids),'order'=>array('Course.uid','Project.name')));
		$projectsdata = $this->detailProjects($projects);
		if(sizeOf($projectsdata) == 0) {
			$projectsdata = $this->fakeProjects();
		}
		print_r($projectsdata);
		die();
		$this->response = $projectsdata;
	}
	
	function fakeSubmissionIDs() {
		$projects = $this->fakeProjects();
		$fakesubmissions = array();
		foreach($projects[0]['Submission'] as $submission) {
			$fakesubmissions[] = $submission['id'];
		}
		return $fakesubmissions;
	}
	
	function filteredFakeProjects($courseids=array()) {
		$fakeprojects = $this->fakeProjects();
		for($i=sizeOf($fakeprojects[0]['Submission'])-1; $i>=0; $i--) {
			if(!in_array($fakeprojects[0]['Submission'][$i]['req'], $courseids) && $fakeprojects[0]['Submission'][$i]['req'] != '*') {
				unset($fakeprojects[0]['Submission'][$i]);
			}
		}
		foreach($fakeprojects as &$fakeproject) {
			$fakeproject['Submission'] = array_values($fakeproject['Submission']);
		}
		return $fakeprojects;
	}
	
	function fakeProjects() {
		$data = array();
		$course = array();
		$course['Project'] = array(
			"id" => "999999",
            "course_id" => "999999",
            "name" => "Quickstart",
            "start_date" => "2012-08-21",
            "end_date" => "2012-08-23",
            "submission_date" => "2012-08-22",
            "description" => "This is the first assignment",
            "created" => "2012-08-20 21:53:18",
		);
		$course['Course'] = array(
			"id" => "999999",
            "coursecode" => "MARK1000",
            "uid" => "2000_1_MARK100",
            "name" => "Introduction to Markup",
            "shadowcode" => "",
            "semester" => "1",
            "year" => "2000",
            "created" => "2012-08-03 22:17:23",
            "updated" => "2012-08-04 20:17:13",
		);
		/*$course['Rubric'] = array(
			array(
				"id" => "99912",
                "project_id" => "999999",
                "name" => "Argument for hypothesis - 10%",
                "type" => "table",
                "section" => "1. Introduction",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "10pts\nInformation is used to make an insightful and convincing argument for the hypothesis.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "8pts\nInformation is used effectively to make a convincing argument for the hypothesis.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "6.5pts\nMost of the information is used effectively to make a convincing argument for the hypothesis.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "5pts\nSome of the information is too general, and/or not directly relevant or well linked to the hypothesis.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "3.5pts\nLarge amounts of the information are too general, and/or not directly relevant or well linked to the hypothesis.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "2pts\nInformation is not relevant and is used so poorly that link to hypothesis is completely lost.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "Missing",
                	),
                )
			),
			array(
				"id" => "99913",
                "project_id" => "999999",
                "name" => "Specific and testable - 5%",
                "type" => "table",
                "section" => "2. Hypothesis",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Detailed and complete for specific treatment, measurable outcome, and context.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Detailed but missing minor specific info for treatment, measurable outcome, AND/OR context.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Testable but missing some key details for treatment, measurable outcome, AND/OR context.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Testable but missing many specific details for treatment, measurable outcome, AND/OR context.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Misses some key information needed to form a testable hypothesis.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Hypothesis is not testable.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99914",
                "project_id" => "999999",
                "name" => "Reproducibility and design - 10%",
                "type" => "table",
                "section" => "3. Methods",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "10pts \n Includes all necessary details for subjects, procedure, treatments and data collection, and is exceptionally well designed and controlled.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "8pts \n Includes all necessary and some trivial details for subjects, procedure, treatments and data collection, and is solidly designed and controlled.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "6.5pts \n Includes all necessary and some trivial details for subjects, procedure, treatments and data collection, but needs minor improvements in design or use of controls.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "5pts \n Includes most of the necessary details for subjects, procedure, treatments and data collection, but with frequent trivial detail, and may need major improvements in design and/or use of controls.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "3.5pts \n Misses large amounts of the key information needed to conduct the experiment, AND/OR is so poorly designed it will not test hypothesis.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "2pts \n Subjects, procedure, treatments and data collection are so poorly stated that the reader cannot determine the experiment.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99915",
                "project_id" => "999999",
                "name" => "Text - 5%",
                "type" => "table",
                "section" => "4. Results",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Text accurately summarises the major findings of the experiment.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Text accurately summarises most of the major findings of the experiment, or has minor problems with clarity.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Text accurately summarises the data but hides the “findings” (too much or too little detail).",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Text refers to the data obtained, but misses some important points.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Some results were referred to incorrectly, or many key details missing which hindered interpretation.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Most results were not referred to in text, or referred to incorrectly.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99916",
                "project_id" => "999999",
                "name" => "Figure(s)/Table(s) - 5%",
                "type" => "table",
                "section" => "4. Results",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Figures/tables were complete and professionally presented.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Figures/tables were professionally presented and mostly complete, but slipped on minor details.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Figures/tables were missing minor details AND there were problems with quality of presentation.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Figures/tables lacked some important details, and/or presentation hindered interpretation.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Figures/tables lacked many important details that hindered interpretation, AND/OR figures/tables repeated results.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Figures/tables provided an inaccurate or indecipherable presentation of data.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99917",
                "project_id" => "999999",
                "name" => "Figure legend(s)/ Table title(s) - 5%",
                "type" => "table",
                "section" => "4. Results",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Figure legends/table titles are accurate, clear and complete.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Figure legends/table titles are accurate, and clear but missing minor information.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Figure legends/table titles are accurate, with minor issues in clarity or information that does not interfere with interpretation.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Figure legends/table titles contain some inaccuracies, but are otherwise clear and contain all of the details necessary for interpretation.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Figure legends/table titles are so poorly written that interpretation becomes a guessing game.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Figure legends/table titles are inaccurate descriptions of most of the figure attributes.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99918",
                "project_id" => "999999",
                "name" => "Interpretation of findings - 10%",
                "type" => "table",
                "section" => "5. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "10pts \n All major findings were correctly and insightfully interpreted in terms of underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "8pts \n Most findings were correctly and insightfully interpreted in terms of underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "6.5pts \n Most findings were correctly but superficially interpreted with some links to underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "5pts \n Most findings were superficially interpreted AND/OR contained small errors in links to underlying physiological mechanisms",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "3.5pts \n Some key findings were incorrectly interpreted in terms of underlying physiological mechanisms, AND/OR were simple repeats of resutls.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "2pts \n Findings and underlying physiological mechanisms were incorrectly interpreted.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Findings were not mentioned.",
                	),
                )
			),
			array(
				"id" => "99919",
                "project_id" => "999999",
                "name" => "Integration of literature - 10%",
                "type" => "table",
                "section" => "5. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "10pts \n Experimental evidence was thoroughly and critically discussed in relation to scientific literature.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "8pts \n Most of the experimental evidence was discussed critically and accurately in relation to scientific literature.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "6.5pts \n Some of the experimental evidence was discussed critically and accurately in relation to scientific literature.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "5pts \n Most of the scientific literature was discussed superficially in relation to experimental evidence.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "3.5pts \n Most of the discussion of the scientific literature was irrelevant or segregated so links to experimental evidence was absent or inappropriate.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "2pts \n No link was made between experimental evidence and the scientific literature apart from a token reference.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Literature was not mentioned",
                	),
                )
			),
			array(
				"id" => "99920",
                "project_id" => "999999",
                "name" => "In text citations and references list - 5%",
                "type" => "table",
                "section" => "6. Referencing",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n In text citations AND references are complete, accurate and consistent in style throughout, all refs required to support claims in text are present and of appropriate quality.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n In text citations AND references are complete and accurate throughout but some inconsistencies in style OR minor claims in text missing supporting refs.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n In text citations OR references have some minor errors but are mostly complete and accurate and of appropriate quality, AND/OR several minor claims in text missing refs.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n In text citations AND references have some minor errors but are mostly complete and accurate and of appropriate quality, AND/OR key claims in text missing supporting refs.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Some in text citations AND/OR references are incomplete or inaccurate but can be found, OR are of inappropriate quality.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n In text citations AND references are incomplete and Inaccurate and cannot be found, OR the majority of refs are of inappropriate quality.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts Missing",
                	),
                )
			),
			array(
				"id" => "99921",
                "project_id" => "999999",
                "name" => "Physiological mechanisms - 15%",
                "type" => "table",
                "section" => "7. Knowledge",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "15pts \n Writing consistently shows an insightful understanding of relevant underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "12pts \n Writing consistently shows a deep understanding of relevant underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "9.75pts \n Writing demonstrates deep and accurate, but occasionally shows superficial (OR a small flaw in) understanding of the key underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "7.5pts \n Writing shows generally superficial but accurate, OR, generally deep but slightly inaccurate understanding of the underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "5.25pts \n Writing consistently shows superficial understanding AND/OR contains several small errors in understanding of underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "3pts \n Writing shows several important errors in understanding of relevant underlying physiological mechanisms.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Writing shows a completely inaccurate understanding of relevant underlying physiological mechanisms.",
                	),
                )
			),
			array(
				"id" => "99922",
                "project_id" => "999999",
                "name" => "Experimental approach - 5%",
                "type" => "table",
                "section" => "7. Knowledge",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Writing consistently shows an insightful understanding of information relevant to the experimental approach.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Writing consistently shows a deep understanding of information relevant to the experimental approach.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Writing is generally deep and accurate, but occasionally shows superficial (OR a small flaw in) understanding of the experimental approach.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Writing shows generally superficial but accurate, OR, generally deep but slightly inaccurate understanding of the experimental approach.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Writing consistently shows superficial understanding AND/OR contains several small errors in understanding of the experimental approach.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Writing shows several important errors in understanding of the experimental approach.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Writing shows an inaccurate understanding of the experimental approach.",
                	),
                )
			),
			array(
				"id" => "99923",
                "project_id" => "999999",
                "name" => "Structure - 5%",
                "type" => "table",
                "section" => "8. Writing",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Placement of information creates effective arguments that are clearly and cohesively structured throughout and follows the conventions of the scientific genre.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Placement of information follows creates an effective argument with overall cohesiveness and follows the conventions of the scientific genre throughout.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Placement of information is used effectively and follows the scientific genre, but has some minor inconsistencies that disrupt some aspects of the overall structure.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Inconsistencies in flow, between OR within paragraphs, disrupt important aspects of the structure, AND/OR often deviate from the scientific genre.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Placement of information has some inconsistencies that detract from the meaning AND/OR often deviates from the scientific genre.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Major inconsistencies in information placement are notably distracting.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Unintelligible",
                	),
                )
			),
			array(
				"id" => "99924",
                "project_id" => "999999",
                "name" => "Language and jargon - 5%",
                "type" => "table",
                "section" => "8. Writing",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Language and jargon was accurate, clear, and concise throughout.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Language and jargon was accurate and clear throughout, but small sections could have been more concise.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Language and jargon generally accurate, but lacks some clarity AND/OR conciseness.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Most language and jargon used appropriately, but some problems with accuracy, clarity AND/OR conciseness.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Language and jargon often used inaccurately OR lacked clarity and conciseness.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Language and jargon often used inaccurately AND lacks clarity and conciseness.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Unintelligible",
                	),
                )
			),
			array(
				"id" => "99925",
                "project_id" => "999999",
                "name" => "Grammar and spelling - 5%",
                "type" => "table",
                "section" => "8. Writing",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "Exep 100%",
                		"description" => "5pts \n Grammar, punctuation and spelling were of a very high standard with no errors.",
                	),
                	array(
                		"name" => "Excel 80%",                	
                		"description" => "4pts \n Grammar, punctuation and spelling were of a very high standard with few minor errors.",
                	),
                	array(
                		"name" => "Good 65%",
                		"description" => "3.25pts \n Grammar, punctuation and spelling were generally of a high standard but had several minor errors.",
                	),
                	array(
                		"name" => "Satisf 50%",                	
                		"description" => "2.5pts \n Notable errors in grammar, punctuation and spelling were present.",
                	),
                	array(
                		"name" => "Weak 35%",                	
                		"description" => "1.75pts \n Frequent errors in grammar, punctuation and spelling were distracting.",
                	),
                	array(
                		"name" => "Poor 20%",                	
                		"description" => "1pt \n Errors in grammar, punctuation and spelling made writing difficult to understand at times.",
                	),
                	array(
                		"name" => "Incomplete 0%",                	
                		"description" => "0pts \n Unintelligible",
                	),
                )
			),
		);
		*/
		
		
		
		
		
		
		$course['Rubric'] = array(
			array(
				"id" => "99901",
                "project_id" => "999999",
                "name" => "Statement of hypothesis",
                "type" => "table",
                "section" => "1. Statement",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Hypothesis clearly  & completely stated",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Clear & reasonably complete hypothesis",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Satisfactory statement of hypothesis",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Unclear or significantly incomplete hypothesis",
                	),
                )
			),
			array(
				"id" => "99902",
                "project_id" => "999999",
                "name" => "Results",
                "type" => "table",
                "section" => "2. Results",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Very clear & logical presentation and description of all major results",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Reasonably clear presentation & mostly logical description of most major results",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Satisfactory presentation of major results with some omissions & logical errors in description",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Unclear or significantly incomplete presentation of results",
                	),
                )
			),
			array(
				"id" => "99903",
                "project_id" => "999999",
                "name" => "Claim",
                "type" => "table",
                "section" => "3. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Claim is detailed and includes all major conclusions",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Detailed answer to the question is provided but some conclusion are omitted",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Brief conclusion is provided that answers the question but lacks detail",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Not Included OR incorrect",
                	),
                )
			),
			array(
				"id" => "99904",
                "project_id" => "999999",
                "name" => "Evidence",
                "type" => "table",
                "section" => "3. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "The author uses data to show a trend over time, a difference between groups, or a relationship between variables AND included correct units (where appropriate)",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "The author uses data to show a trend over time OR a difference between groups OR a relationship between variables",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "The author did not use data to show trend over time OR difference between groups OR a relationship between variables",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Incorrect use of evidence",
                	),
                )
			),
			array(
				"id" => "99905",
                "project_id" => "999999",
                "name" => "Reasoning",
                "type" => "table",
                "section" => "3. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "The author provides support for all of his\/her ideas using valid and reliable data AND correctly justifies outliers if present.\r\nThe provided rationale is sound and complete.",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "The author provides support for most of his\/her ideas using valid and reliable data AND if outliers are presents incorrectly justifies outliers OR does not discuss outliers if present.\r\nThe provided rationale is sound and complete.",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "The author provides support for most his\/her ideas using valid and reliable data AND if outliers are present incorrectly justifies outliers OR does not discuss outliers if present.\r\nThe provided rationale is sound but incomplete. \r\n(Part of the conclusion follows from assumptions with the rest of the conclusion not being warranted)",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "The author DID NOT support all of his\/her ideas OR used evidence based on unreliable or invalid data OR conclusions are incorrect based upon assumptions.",
                	),
                )
			),
			array(
				"id" => "99906",
                "project_id" => "999999",
                "name" => "Significance of research",
                "type" => "table",
                "section" => "3. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Extensive & relevant professional insights",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Some relevant professional insights",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Limited relevant professional insights",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "No relevant professional insights",
                	),
                )
			),
			array(
				"id" => "99907",
                "project_id" => "999999",
                "name" => "Limitations and future directions",
                "type" => "table",
                "section" => "3. Discussion",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Extensive understanding of limitations of conclusions drawn from study.  Avenues for future research identified and discussed in detail",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Good understanding of limitations of conclusions drawn from study.  Avenues for future research identified but not discussed in detail",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Some understanding of limitations of conclusions drawn from study.   Some avenues for future research identified but not discussed",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "No understanding of limitations of conclusions drawn from study.  Avenues for future research not identified or discussed",
                	),
                )
			),
			array(
				"id" => "99908",
                "project_id" => "999999",
                "name" => "Quality of oral communication",
                "type" => "table",
                "section" => "4. Presentation",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Highly effective use of professional vocabulary & engaging & persuasive language",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Effective use of professional vocabulary & engaging & persuasive language",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Use of professional vocabulary & engaging & persuasive language is inconsistent",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Limited use of professional vocabulary & engaging & persuasive language",
                	),
                )
			),
			array(
				"id" => "99909",
                "project_id" => "999999",
                "name" => "Use of presentation aids",
                "type" => "table",
                "section" => "5. Presentation Aids",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Highly effective use of clear & legible text  & graphic presentation materials",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Effective use of clear & legible text & graphic presentation materials",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Inconsistent use of clear & legible text & graphic presentation materials",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Limited use of clear & legible text & graphic presentation materials",
                	),
                )
			),
			array(
				"id" => "99910",
                "project_id" => "999999",
                "name" => "Handling questions",
                "type" => "table",
                "section" => "6. Questions",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	array(
                		"name" => "100-80% (A+/A)",
                		"description" => "Positive response to all questions Very clear & relevant answers to all questions",
                	),
                	array(
                		"name" => "79-65% (B+/B)",                	
                		"description" => "Positive response to most questions Clear & relevant answers to most questions",
                	),
                	array(
                		"name" => "65-50% (C+/C)",
                		"description" => "Adequate response to some questions  Clear & relevant answers to some questions",
                	),
                	array(
                		"name" => "<50% (Fail)",                	
                		"description" => "Inadequate responses to most questions Answers to most questions unclear or irrelevant",
                	),
                )
			),
			array(
				"id" => "99911",
                "project_id" => "999999",
                "name" => "Comments",
                "type" => "text",
                "section" => "7. Comments",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	"description" => "Additional comments by examiner",
                )
			),
			array(
				"id" => "99912",
                "project_id" => "999999",
                "name" => "Participation",
                "type" => "text",
                "section" => "8. Participation",
                "created" => "2012-09-02 22:07:34",
                "updated" => "2012-09-02 22:07:34",
                "meta" => array(
                	"description" => "Participation by other students",
                )
			),
);
			
		
		
		
		
		
		
		
		
		$course['Submission'] = array(
			array(
				"id" => "999988",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Action Potential Report Exemplar 1.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999989",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Action Potential Report Exemplar 2.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999990",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Toad Heart Report Exemplar 1.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999991",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Toad Heart Report Exemplar 2.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999992",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Toad Heart Report Exemplar 3.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999993",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Biol1040_Demo_1.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'22',		
			),
			array(
				"id" => "999994",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Biol1040_Demo_2.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'22',		
			),
			array(
				"id" => "999995",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Biom2013_Demo_1.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'21',		
			),
			array(
				"id" => "999996",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Biom2013_Demo_2.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'21',		
			),
			array(
				"id" => "999997",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Biom2011_Demo.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'20',		
			),
			array(
				"id" => "999998",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Quickstart Guide.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'*',
			),
			array(
				"id" => "999999",
				"project_id" => "999999",
				"created" => "2012-08-20 21:55:57",
				"updated" => "2012-08-20 21:55:57",
				"title" => "Sketchpad.pdf",
				"uqid" => "MarkupDemo",
				'req'=>'*',
			),
		);
		$course['Tag'] = array(
			array(
				"id" => "14",
                "project_id" => "999999",
                "name" => "Feedback",
                "color" => "0031ff",
                "created" => "2012-08-20 21:54:55",
                "updated" => "2012-08-20 21:54:55"			
			),
			array(
				"id" => "13",
                "project_id" => "999999",
                "name" => "Admin",
                "color" => "ff0000",
                "created" => "2012-08-20 21:54:55",
                "updated" => "2012-08-20 21:54:55"
			)
		);
		
		$data[] = $course;
		return $data;
	}
	
	public function project($id) {
		$this->requireAuth();
		$user_id = $this->Ldap->getUserID();
		$courseids = $this->CourseRoleUser->find('list',array('fields'=>array('course_id'),'conditions'=>array('role_id > 1','user_id'=>$user_id)));
		$projects = $this->Project->find('all',array('conditions'=>array('course_id'=>$courseids,'Project.id'=>$id),'order'=>array('Course.coursecode','Project.name')));
		$this->response = $this->detailProjects($projects);
	}
	
	public function isLatestVersion() {
		$currentversion = '2.23';
		$releasenotes = '
RELEASE NOTES 
====
2.23 - 11-02-2013
---
- Changed the front page headers
2.22 - 07-02-2013
---
Initial release for 2013
- Numerous bug fixes
- Update notifications
- Button to download all
- Ability to delete/remove assessment
- Much faster drawing
- More responsive toolbar
- Instrumentation / logging of the marker
- Better offline support
		';
		$response = array('latest'=>'true');
		$response['releasenotes'] = $releasenotes;
		$response['version'] = $currentversion;
		if(floatval($_POST['version']) < floatval($currentversion)) {
			$response['latest'] = 'false';
		}
		
		$this->response = $response;
	}
	
	public function isLatestModerationVersion() {
		$this->isLatestVersion();
	}
	
	function detailProjects($projectsarray) {
		
		foreach($projectsarray as $project_key=>&$project) {
			foreach($project['Rubric'] as &$rubric) {
				$rubric['meta'] = json_decode($rubric['meta']);
			}
			foreach($project['Submission'] as $submission_key=>&$submission) {
				$filedetails = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>0));
				$submission['title'] = $filedetails['Attachment']['title'];
				$activities = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>-1));
				$identified = array();
				$assigned = array();
				foreach($activities as $activity) {
					if($activity['Activity']['state_id'] == '1') {
						$identified = $activity;
					} else if($activity['Activity']['state_id'] == '2') {
						$assigned[] = $activity;
					}
				}
				//$identified = $this->Activity->find('first',array('conditions'=>array('state_id'=>1,'submission_id'=>$submission['id']),'recursive'=>-1));
				//$assigned = $this->Activity->find('first',array('conditions'=>array('state_id'=>2,'submission_id'=>$submission['id']),'recursive'=>-1));
				if(!empty($identified)) {
					$theuser = $this->User->find('first',array('conditions'=>array('uqid'=>$identified['Activity']['meta']),'recursive'=>-1));
					$usernameformatted = $identified['Activity']['meta'];
					if(!empty($theuser)) {
						$theusernames = explode(" ", $theuser['User']['name']);
						if(sizeOf($theusernames) > 1) {
							$usernameformatted = $usernameformatted.' ('.$theusernames[sizeOf($theusernames)-1].')';
						}
					}
					$submission['uqid'] = $usernameformatted;
				} else {
					$submission['uqid'] = 'Unknown';
				}
				if(empty($identified)) {
					unset($projectsarray[$project_key]['Submission'][$submission_key]);
				} else {
					$isvalidmarker = false;
					//if(isset($assignedtutor['Activity'])) {
						foreach($assigned as $assignedtutor) {
							if(isset($assignedtutor['Activity'])) {
								if($assignedtutor['Activity']['meta'] == $this->Ldap->getUQID()) {
									$isvalidmarker = true;
								}
							}
						}
						//if(!isset($assignedtutor['Activity']) || $assigned['Activity']['meta'] != $this->Ldap->getUQID()) {
						//	unset($projectsarray[$project_key]['Submission'][$submission_key]);
						//unset($submission);
						//}
					//}
					if(!$isvalidmarker) {
						unset($projectsarray[$project_key]['Submission'][$submission_key]);
					}
				} 
			}
			usort($projectsarray[$project_key]['Submission'], array($this, "usort_name"));
			$projectsarray[$project_key]['Submission'] = array_values($projectsarray[$project_key]['Submission']);
		}
		return $projectsarray;
	}
	
	function rub_cmp($a, $b) {
		return strcmp($a['id'], $b['id']);
	}
	
	function detailProjects2($projectsarray) {
		
		foreach($projectsarray as $project_key=>&$project) {
			usort($project['Rubric'], array($this, "rub_cmp"));
			foreach($project['Rubric'] as &$rubric) {
				$rubric['meta'] = json_decode($rubric['meta']);
			}
			$submissionids = array();
			foreach($project['Submission'] as $submission_key=>&$submission) {
				$submissionids[] = $submission['id'];
			}
			$attachmentlist = $this->Attachment->find('all',array('conditions'=>array('submission_id'=>$submissionids),'recursive'=>0));
			$filedetails = array();
			foreach($attachmentlist as $attachmentitem) {
				$filedetails[$attachmentitem['Attachment']['submission_id']] = $attachmentitem;
			}
			foreach($project['Submission'] as $submission_key=>&$submission) {
				//$filedetails = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>0));
				$submission['title'] = $filedetails[$submission['id']]['Attachment']['title'];
				$activities = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>-1));
				$identified = array();
				$assigned = array();
				foreach($activities as $activity) {
					if($activity['Activity']['state_id'] == '1') {
						$identified = $activity;
					} else if($activity['Activity']['state_id'] == '2') {
						$assigned[] = $activity;
					}
				}
				if(!empty($identified)) {
					$theuser = $this->User->find('first',array('conditions'=>array('uqid'=>$identified['Activity']['meta']),'recursive'=>-1));
					$usernameformatted = $identified['Activity']['meta'];
					if(!empty($theuser)) {
						$theusernames = explode(" ", $theuser['User']['name']);
						if(sizeOf($theusernames) > 1) {
							$usernameformatted = $usernameformatted.' ('.$theusernames[sizeOf($theusernames)-1].')';
						}
					}
					$submission['uqid'] = $usernameformatted;
				} else {
					$submission['uqid'] = 'Unknown';
				}
				if(empty($identified)) {
					unset($projectsarray[$project_key]['Submission'][$submission_key]);
				} else {
					$isvalidmarker = false;
					foreach($assigned as $assignedtutor) {
					    if(isset($assignedtutor['Activity'])) {
					    	if($assignedtutor['Activity']['meta'] == $this->Ldap->getUQID()) {
					    		$isvalidmarker = true;
					    	}
					    }
					}
					if(!$isvalidmarker) {
						unset($projectsarray[$project_key]['Submission'][$submission_key]);
					}
				}
			}
			usort($projectsarray[$project_key]['Submission'], array($this, "usort_name"));
			$projectsarray[$project_key]['Submission'] = array_values($projectsarray[$project_key]['Submission']);
		}
		return $projectsarray;
	}
	
	function detailModerationProjects($projectsarray) {
		
		foreach($projectsarray as $project_key=>&$project) {
			foreach($project['Rubric'] as &$rubric) {
				$rubric['meta'] = json_decode($rubric['meta']);
			}
			foreach($project['Submission'] as $submission_key=>&$submission) {
				$filedetails = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>0));
				$submission['title'] = $filedetails['Attachment']['title'];
				$activities = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>-1));
				$identified = array();
				$assigned = array();
				$beenmarked = false;
				foreach($activities as $activity) {
					if($activity['Activity']['state_id'] == '1') {
						$identified = $activity;
					} else if($activity['Activity']['state_id'] == '2') {
						$assigned[] = $activity;
					} else if($activity['Activity']['state_id'] == '4') {
						$beenmarked = true;
					} else if($activity['Activity']['state_id'] == '6') {
						$beenmarked = false;
					}
				}
				//$identified = $this->Activity->find('first',array('conditions'=>array('state_id'=>1,'submission_id'=>$submission['id']),'recursive'=>-1));
				//$assigned = $this->Activity->find('first',array('conditions'=>array('state_id'=>2,'submission_id'=>$submission['id']),'recursive'=>-1));
				if(!empty($identified)) {
					$theuser = $this->User->find('first',array('conditions'=>array('uqid'=>$identified['Activity']['meta']),'recursive'=>-1));
					$usernameformatted = $identified['Activity']['meta'];
					if(!empty($theuser)) {
						$theusernames = explode(" ", $theuser['User']['name']);
						if(sizeOf($theusernames) > 1) {
							$usernameformatted = $usernameformatted.' -> ';
						}
					}
					$submission['uqid'] = $usernameformatted;
				} else {
					$submission['uqid'] = 'Unknown';
				}
				if(empty($identified)) {
					unset($projectsarray[$project_key]['Submission'][$submission_key]);
				} else {
					$isvalidmarker = false;
					//if(isset($assignedtutor['Activity'])) {
						foreach($assigned as $assignedtutor) {
							if(isset($assignedtutor['Activity'])) {
								$submission['uqid'] .= $assignedtutor['Activity']['meta'];
							}
						}
						//if(!isset($assignedtutor['Activity']) || $assigned['Activity']['meta'] != $this->Ldap->getUQID()) {
						//	unset($projectsarray[$project_key]['Submission'][$submission_key]);
						//unset($submission);
						//}
					//}
					if(!$beenmarked) {
						unset($projectsarray[$project_key]['Submission'][$submission_key]);
					}
					
					/* DO MARKS AND AUDIO */
					$versionfile = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
					$marks = array();
					$audioannotations = array();
					
					if($versionfile) {
					    $path = $this->versionsdir.$submission['id'].'/'.$versionfile['Version']['path'];
					    $file = $path.'/marks.json';
					    if($file) {
					    	if (file_exists($file) && is_readable ($file)) {
					    		$marks = json_decode(file_get_contents($file));
					    		if(is_object($marks)) {
						    		$marks = $marks->marks;
						    	}
					    	}
					    }
					    
		                $file = $path.'/annots/annots.json';
		                if($file) {
		                    if (file_exists($file) && is_readable ($file)) {
		                    	$annotationdata = json_decode(file_get_contents($file));
		                    	foreach($annotationdata as &$annotationdatafile) {
		                        	if($annotationdatafile->type == 'Recording') {
		                        		if(isset($annotationdatafile->filename)) {
			                        		$annotationdatafile->filename = $submission['id'].'/'.str_replace('.m4a','.mp3',$annotationdatafile->filename);
			                        		$audioannotations[] = $annotationdatafile;
			                        	}
		                       		}
		                    	}
		                    }
		                }
					}
					$submission['marks'] = json_encode($marks);
					$submission['audioannotations'] = json_encode($audioannotations);
				} 
			}
			usort($projectsarray[$project_key]['Submission'], array($this, "usort_name"));
			$projectsarray[$project_key]['Submission'] = array_values($projectsarray[$project_key]['Submission']);
		}
		return $projectsarray;
	}
	
	function usort_name($a,$b) {
		$al = strtolower($a['title']);
        $bl = strtolower($b['title']);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
	}
	
	function detailProjectsTwo($projectsarray) {
		$starttime = microtime(true);
		
		$attachmentfiles = $this->Attachment->find('list',array('fields'=>array('submission_id','title')));
		
		foreach($projectsarray as $project_key=>&$project) {
			foreach($project['Rubric'] as &$rubric) {
				$rubric['meta'] = json_decode($rubric['meta']);
			}
			foreach($project['Submission'] as $submission_key=>&$submission) {
				$submission['title'] = $attachmentfiles[$submission['id']];
			}
			$projectsarray[$project_key]['Submission'] = array_values($projectsarray[$project_key]['Submission']);
		}
		$end = microtime(true);
		$endtime = $end - $starttime.'ms';
		echo $endtime;
		return $projectsarray;
	}
	
	function detailProjectsThree($projectsarray) {
		foreach($projectsarray as $project_key=>&$project) {
			foreach($project['Rubric'] as &$rubric) {
				$rubric['meta'] = json_decode($rubric['meta']);
			}
			foreach($project['Submission'] as $submission_key=>&$submission) {
				$starttime = $this->getcurrenttime(); 
				$filedetails = $this->Attachment->find('first',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>0));
				$submission['title'] = $filedetails['Attachment']['title'];
				$activities = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submission['id']),'recursive'=>-1));
				$identified = array();
				$assigned = array();
				foreach($activities as $activity) {
					if($activity['Activity']['state_id'] == '1') {
						$identified = $activity;
					} else if($activity['Activity']['state_id'] == '2') {
						$assigned = $activity;
					}
				}
				//$identified = $this->Activity->find('first',array('conditions'=>array('state_id'=>1,'submission_id'=>$submission['id']),'recursive'=>-1));
				//$assigned = $this->Activity->find('first',array('conditions'=>array('state_id'=>2,'submission_id'=>$submission['id']),'recursive'=>-1));
				if(!empty($identified)) {
					$theuser = $this->User->find('first',array('conditions'=>array('uqid'=>$identified['Activity']['meta']),'recursive'=>-1));
					$usernameformatted = $identified['Activity']['meta'];
					if(!empty($theuser)) {
						$theusernames = explode(" ", $theuser['User']['name']);
						if(sizeOf($theusernames) > 1) {
							$usernameformatted = $usernameformatted.' ('.$theusernames[sizeOf($theusernames)-1].')';
						}
					}
					$submission['uqid'] = $usernameformatted;
				} else {
					$submission['uqid'] = 'Unknown';
				}
				if(empty($identified)) {
					unset($projectsarray[$project_key]['Submission'][$submission_key]);
				} else {
					if($assigned['Activity']['meta'] != $this->Ldap->getUQID()) {
						unset($projectsarray[$project_key]['Submission'][$submission_key]);
						//unset($submission);
					}
				} 
				echo 'Finished a submission: '.($this->getcurrenttime()-$starttime.' seconds'."\n");
				echo "\n";
			}
			$projectsarray[$project_key]['Submission'] = array_values($projectsarray[$project_key]['Submission']);
		}
		return $projectsarray;
	}
	
	public function userdetails() {
		$this->requireAuth();
		$this->response = $this->Ldap->userdetails;
	}
	
	public function logout() {
		$this->Ldap->logout();
		$this->response = 'true';
	}
	
	public function uploadSubmission($submission_id) {
		 ini_set('memory_limit','512M');
		 set_time_limit(500);
	
		
	
		//if($submission_id == "999998" || $submission_id == "999999" || $submission_id == "999997") {
		if(in_array($submission_id, $this->fakeSubmissionIDs())) {
			$this->response = 'true';
			die();
		}
		$submission = $this->Submission->findById($submission_id);
		if(empty($submission)) {
			$this->error = 'Error - Not a valid submission - unknown submission';
			$this->httpcode = '400';
			$this->beforeRender();
		}
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$submission['Project']['course_id'],'role_id > 1','user_id'=>$this->Ldap->getUserID())));
		if(empty($courseroleuser)) {
			$this->error = 'Error - You are not a tutor or course coordinator for this course';
			$this->httpcode = '400';
			$this->beforeRender();
		}
		if(sizeOf($_FILES) != 1) {
		    $this->error = 'Error - Invalid number of files, files sent - '.sizeOf($_FILES);
		    $this->httpcode = '400';
	    	$this->beforeRender();
		}
		if(!isset($_FILES['submissiondata'])) {
		   $this->error = 'Error - Require file name to be submissiondata';
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		if($_FILES['submissiondata']['error'] != 0) {
		   $this->error = 'Error - File upload error - Error '.$_FILES['submissiondata']['error'];
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		if($_FILES['submissiondata']['type'] != 'application/zip') {
		   $this->error = 'Error - File upload error - Not a zip file';
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		//possibly all good, lets try upload
		$data = array();
		$data['submission_id'] = $submission_id;
		$data['user_id'] = $this->Ldap->getUserID();
		$data['filename'] = $_FILES['submissiondata']['name'];
		$zipfile = $this->tmpdir.$submission_id.'.zip';
		if(move_uploaded_file($_FILES['submissiondata']['tmp_name'],$zipfile)) {
			$output = array();
			$unzippeddir = $this->tmpdir.'/'.$submission_id;
			$unzipcmd = "unzip ".$zipfile." -d ".$unzippeddir;
			$audiocmd = "/var/www/webdav/convert.sh ".$unzippeddir."/annots";
			shell_exec($unzipcmd);
			if(file_exists($this->tmpdir.'/'.$submission_id)) {
				shell_exec($audiocmd);
				//$this->response = 'true';
				//die();
				unlink($zipfile);
				$this->versionDirectory($submission_id,$unzippeddir);
				//assume it works, cause assuming is awesome
				//autopublish
				if($submission['Project']['option_autopublish'] == 1) {
					$this->publishSubmission($submission_id);
				}
			}
			/*
			//unzip it
			//unzip /var/www/webdav/marked/16.zip -d /var/www/webdav/marked/
		//convert audio
			//$output = shell_exec('/var/www/webdav/marked/annots/convert.sh /var/www/webdav/marked/annots');	
		//move it into the right place
		
		
		
		$output = shell_exec("php /var/www/jojo/test.php ");
			*/
			
			
			//mkdir($this->$versionsdir.$submission_id);
			//$command = 'unzip '.$zipfile.' -d '.$submission_id;
			//exec($command,$output,$return);
			//echo '*'.$return.'*';
			//print_r($command);
			//print_r($data);
			$this->response = 'true';
			die();
			
		} else {
			$this->error = 'Error - Could not move file';
		    $this->httpcode = '400';
		    $this->beforeRender();
		}
	}
	
	
	
	
	
	
	
	
	public function uploadModeration($submission_id) {
		 ini_set('memory_limit','512M');
		 set_time_limit(500);
	
		
	
		//if($submission_id == "999998" || $submission_id == "999999" || $submission_id == "999997") {
		if(in_array($submission_id, $this->fakeSubmissionIDs())) {
			$this->response = 'true';
			die();
		}
		$submission = $this->Submission->findById($submission_id);
		if(empty($submission)) {
			$this->error = 'Error - Not a valid submission - unknown submission';
			$this->httpcode = '400';
			$this->beforeRender();
		}
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$submission['Project']['course_id'],'role_id > 2','user_id'=>$this->Ldap->getUserID())));
		if(empty($courseroleuser)) {
			$this->error = 'Error - You are not a course coordinator for this course';
			$this->httpcode = '400';
			$this->beforeRender();
		}
		if(sizeOf($_FILES) != 1) {
		    $this->error = 'Error - Invalid number of files, files sent - '.sizeOf($_FILES);
		    $this->httpcode = '400';
	    	$this->beforeRender();
		}
		if(!isset($_FILES['submissiondata'])) {
		   $this->error = 'Error - Require file name to be submissiondata';
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		if($_FILES['submissiondata']['error'] != 0) {
		   $this->error = 'Error - File upload error - Error '.$_FILES['submissiondata']['error'];
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		if($_FILES['submissiondata']['type'] != 'application/zip') {
		   $this->error = 'Error - File upload error - Not a zip file';
		   $this->httpcode = '400';
		   $this->beforeRender();
		}
		//possibly all good, lets try upload
		$data = array();
		$data['submission_id'] = $submission_id;
		$data['user_id'] = $this->Ldap->getUserID();
		$data['filename'] = $_FILES['submissiondata']['name'];
		$zipfile = $this->tmpdir.'moderation_'.$submission_id.'.zip';
		if(move_uploaded_file($_FILES['submissiondata']['tmp_name'],$zipfile)) {
			$output = array();
			$unzippeddir = $this->tmpdir.'/'.'moderation_'.$submission_id;
			$unzipcmd = "unzip ".$zipfile." -d ".$unzippeddir;
			$audiocmd = "/var/www/webdav/convert.sh ".$unzippeddir."/annots";
			shell_exec($unzipcmd);
			if(file_exists($this->tmpdir.'/'.'moderation_'.$submission_id)) {
				shell_exec($audiocmd);
				unlink($zipfile);
				$this->moderateVersionDirectory($submission_id,$unzippeddir);
				//assume it works, cause assuming is awesome
			}
			$this->response = 'true';
			die();
			
		} else {
			$this->error = 'Error - Could not move file';
		    $this->httpcode = '400';
		    $this->beforeRender();
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	function publishSubmission($submission_id) {
		$studentids = array();
		$readytopublish = true;
		$submission = $this->Submission->findById($submission_id);
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$studentids[] = $activity['meta'];
			} else if($activity['state_id'] == $this->getStateID('Published')) {
				//$readytopublish = false;
			}
		}
		//if not already published
		if($readytopublish) {
			$activitydata = array();
			$courseid = $submission['Project']['course_id'];
			$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$courseid,'role_id > 1','user_id'=>$this->Ldap->getUserID()),'recursive'=>-1));
			$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
			$activitydata['state_id'] = $this->getStateID('Published');
			$activitydata['submission_id'] = $submission_id;
			$activitydata['meta'] = $this->Ldap->getUQID();
			$course = $this->Course->find('first',array('conditions'=>array('Course.id'=>$submission['Project']['course_id']),'recursive'=>-1));
			
			sleep(1); //just wait until marked is complete
			$this->Activity->create();
			if($this->Activity->save($activitydata)) {
				$studentemails = array();
				foreach($studentids as $studentid) {
					$thestudent = $this->User->find('first',array('conditions'=>array('uqid'=>$studentid)));
					$studentemails[$thestudent['User']['email']] = $thestudent['User']['name'];
				}
				foreach($studentemails as $studentemail=>$studentname) {
					$this->emailToStudent($studentname,$course,$submission,$submission,$studentemail);
				}
			}
		}
	}
	
	function versionDirectory($submission_id,$unzippeddir) {
		$versiondata = array();
		$versiondata['submission_id'] = $submission_id;
		$versiondata['meta'] = '{"submitted_by":"'.$this->Ldap->getUQID().'"}';
		$this->Version->save($versiondata);
		$versionid = $this->Version->id;
		$path = sha1($submission_id.'/'.$versionid);
		$this->Version->saveField('path',$path);
		if(!file_exists($this->versionsdir.$submission_id)) {
			mkdir($this->versionsdir.$submission_id);
		}
		rename($unzippeddir, $this->versionsdir.$submission_id.'/'.$path.'/');
		$version = $this->Version->find('first',array('conditions'=>array('id'=>$versionid),'recursive'=>-1));
		//save submission updated
		$this->Submission->id = $submission_id;
		$this->Submission->saveField('updated',$version['Version']['updated']);
		//save activity
		$activitydata = array();
		$submission = $this->Submission->findById($submission_id);
		$courseid = $submission['Project']['course_id'];
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$courseid,'role_id > 1','user_id'=>$this->Ldap->getUserID()),'recursive'=>-1));
		$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
		$activitydata['state_id'] = $this->getStateID('Marked');
		$activitydata['submission_id'] = $submission_id;
		$activitydata['meta'] = $this->Ldap->getUQID();
		$this->Activity->create();
		$this->Activity->save($activitydata);
	}
	
	function moderateVersionDirectory($submission_id,$unzippeddir) {
		if(file_exists($this->moderationdir.$submission_id)) {
			rename($this->moderationdir.$submission_id, $this->moderationdir.'trash_'.$submission_id.'_'.date('Y_m_d_H_i_s'));
		}
		rename($unzippeddir, $this->moderationdir.$submission_id.'/');
		//save activity
		$activitydata = array();
		$submission = $this->Submission->findById($submission_id);
		$courseid = $submission['Project']['course_id'];
		$course = $this->Course->find('first',array('conditions'=>array('Course.id'=>$courseid)));
		$courseroleuser = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$courseid,'role_id > 1','user_id'=>$this->Ldap->getUserID()),'recursive'=>-1));
		$activitydata['course_role_users_id'] = $courseroleuser['CourseRoleUser']['id'];
		$activitydata['state_id'] = $this->getStateID('Moderated');
		$activitydata['submission_id'] = $submission_id;
		$activitydata['meta'] = $this->Ldap->getUQID();
		$this->Activity->save($activitydata);
		//Email as well
		$emailcontent = $this->Ldap->getUQID()." has provided feedback on a submission you have graded.\n\n";
		$emailcontent .= "Please note that this feedback is only available to you, and will not be sent through to the student.\n";
		$emailcontent .= "Any changes that result from this feedback should be performed on your own device with your existing markup.\n";
		$emailcontent .= "To view the feedback, please click on this link:\n";
		$emailcontent .= Configure::read('url_base')."/_dev/assessment/view_moderation/".$this->encodeSubmissionID($submission_id)."\n\n";
		$emailcontent .= "(best viewed on either Google Chrome or Firefox)";
		
		$markeractivities = $this->Activity->find('all',array('conditions'=>array('state_id'=>'4','submission_id'=>$submission_id),'recursive'=>-1));
		
		foreach($markeractivities as $markeractivity) {
			$themarker = $this->User->find('first',array('conditions'=>array('uqid'=>$markeractivity['Activity']['meta']),'recursive'=>-1));
			$emailname = $themarker['User']['name'];
			$emailaddress = $themarker['User']['email'];
			
			/* START DEBUG */			
			//$emailcontent = "DEBUG MODE DELETE THIS LINE ".$emailcontent;
			//$emailcontent .= "\n\n\nThe email is meant to go to ".$emailname." at ".$emailaddress."\n\n\n";
			//$emailaddress = 'uqadekke@uq.edu.au';
			/* END DEBUG */
			
			$this->moderationAutomatedEmail($emailname,$course,$emailcontent,$submission,$emailaddress);
		}
	}
	
	/*
	function transcodeaudiofile($path) {
		$wavfile = $this->Configuration->localpath.'/app/webroot/files/tmp.wav';
		$mp3file = $this->Configuration->localpath.'/app/webroot/files/tmp.mp3';
		$this->Dropbox->downloadfile($path,$this->Configuration->localpath.'/app/webroot/files/tmp.wav');
		exec("lame ".$wavfile." ".$mp3file);
		$this->Dropbox->uploadfile(str_replace('.wav','.mp3',$path),$mp3file);
		$this->Dropbox->deletefile($path);
	}
	*/
	
	public function hasAgreedToTOS() {
		$userdata = $this->User->find('first',array('conditions'=>array('User.id'=>$this->Ldap->getUserID()),'recursive'=>-1));
		if($userdata['User']['termsagreed'] < 1) {
			$this->response = 'false';
		} else {
			$this->response = 'true';
		}
	}
	
	public function setAgreedToTOS() {
		$this->User->id = $this->Ldap->getUserID();
		$this->User->saveField('termsagreed','1');
		$this->response = 'true';
	}
	
	public function testUpload() {
		header('Content-type: text/html');
		?>
		<form method='post' enctype="multipart/form-data" action="/_dev/api/uploadSubmission/1?secret=<?php echo $this->secretkey; ?>">
			<input type='file' name='submissiondata' />
			<input type='submit' />
		</form>
		<?php
		die();
	}
	
	
	
	/* OTHER STUFF */
	function requireAuth() {
		if(!$this->Ldap->loggedin()) {
			$this->error = 'Not logged in';
			$this->httpcode = '403';
			$this->beforeRender();
		}
	}
	
	function requireProjectAccess($project_id) {
		$project = $this->Project->findById($project_id);
		if(!empty($project)) {
			$association = $this->CourseRoleUser->find('first',array('conditions'=>array('course_id'=>$project['Course']['id'],'user_id'=>$this->Ldap->getUserID(),'role_id > 1')));
			if(empty($association)) {
				$this->error = 'Access denied to this project';
				$this->httpcode = '403';
				$this->beforeRender();
			}
		}
	}
	
	function beforeRender() {
		if(!$this->showhtml) {
			$data = array('response'=>$this->response);
			if(sizeOf($this->error) > 0) {
				$data = array('error'=>$this->error);
			}
			header($this->httpcodes[$this->httpcode]);
			echo json_encode($data);
			die();
		} else {
			parent::beforeRender();
		}
	}
	
	function validateValues($type,$fields) {
		$validdata = array();
		if($type == "GET") {
			foreach($fields as $field) {
				if(isset($_GET[$field])) {
					$validdata[$field] = $_GET[$field];
				} else {
					$this->error = 'Missing '.$type.' data for '.$field;
					$this->httpcode = '400';
					$this->beforeRender();
				}
			}
		} else if ($type == "POST") {
			foreach($fields as $field) {
				if(isset($_POST[$field])) {
					$validdata[$field] = $_POST[$field];
				} else {
					$this->error = 'Missing '.$type.' data for '.$field;
					$this->httpcode = '400';
					$this->beforeRender();
				}
			}
		} else {
			$this->error = 'Invalid validation message type';
			$this->httpcode = '405';
			$this->beforeRender();
		}
		return $validdata;
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
				if($this->Ldap->isCourseCoordinator($course['Course']['uid']) || $course['Course']['uid'] == 'uqjkibed') {
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
	
	public function help() {
		if(!$this->Ldap->loggedIn()) {
			$this->redirect(array('controller'=>'users','action'=>'login'));
			die();
		}
		$developers = array('uqjmarri','uqadekke');
		$uqid = $this->Ldap->getUQID();
		if(!in_array($uqid, $developers)) {
			echo 'NOT A DEVELOPER';
			die();			
		}
		$this->set('secret',$this->secretkey);
		$this->requireAuth();
		header('Content-type: text/html');
		$this->showhtml = true;
		$this->set('endpoints',$this->endpoints);
	}
}
