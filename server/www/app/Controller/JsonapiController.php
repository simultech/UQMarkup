<?php

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class JsonapiController extends AppController {

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
			echo 'SECRET KEY MISSING FROM JsonapiController.php';
			die();
		}
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		$this->httpcode = '200';
		$apiauthed = true;
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
	
	function userlookup($course_id,$type='markers',$search='') {
		$data = array();
		if($this->Ldap->isAdmin()) {
			if(strlen($search) > 3) {
				if($type == 'markers') {
					$crus = array_values($this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('role_id >'=>'1','course_id'=>$course_id))));
				} else {
					$crus = array_values($this->CourseRoleUser->find('list',array('fields'=>array('user_id'),'conditions'=>array('course_id'=>$course_id))));
				}
				$data = $this->User->find('list',array('fields'=>array('uqid','name'),'conditions'=>array('id'=>$crus,'OR'=>array('uqid LIKE'=>'%'.$search.'%','name LIKE'=>'%'.$search.'%'))));
			}
		}
		echo json_encode($data);
		die();
	}
	
}

?>