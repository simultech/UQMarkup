<?php

App::uses('AppController', 'Controller');

class CronController extends AppController {

	public $name = 'Cron';

	public $uses = array('Course','CourseRoleUser','Project','Submission','Attachment');
	
	public $components = array('Ldap');
	
	public $courseadmin = false;

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function refreshClassLists() {
		$courses = $this->Course->find('all');
		foreach($courses as $course) {
			$this->refreshClassList($course);
		}
		echo 'done';
		die();
	}
	
	public function loadUploadedFiles() {
		$projects = $this->Project->find('all');
		foreach($projects as $project) {
				loadProjectSubmissions($project);
			}
			die();
		}
		print_r($projects);
	}
	
}