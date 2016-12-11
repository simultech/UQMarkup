<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

	var $breadcrumbs = array();
	
	var $uses = array('Role','State','Logtype','User','Project','Course');
	//var $components = array('Security');
	
	var $roles = array();
	var $states = array();
	var $logtypes = array();
	
	var $baseURL = '/_dev';
	
	var $webdavdir = '/var/www/webdav/uploads/';
	var $binarydir = '/var/www/webdav/binary/';
	var $versionsdir = '/var/www/webdav/versions/';
	var $moderationdir = '/var/www/webdav/moderation/';
	var $tmpdir = '/var/www/webdav/tmp/';
	var $surveydir = '/var/www/webdav/surveys/';
	var $webdavsuburl = 'https://uqmarkup.ceit.uq.edu.au/uploads/';
	
	var $debugmode = true;
	var $debug = false; //this will auto-get set to true if debugmode is true and its a developer
	
	function beforeFilter() {
		if($_SERVER['SERVER_PORT'] != '443' && Configure::read('debug') == 0) {
			$this->forceSSL();
		}
		parent::beforeFilter();
		$this->setupStaticTables();
	}
	
	function forceSSL() {
        $this->redirect('https://' . $_SERVER['SERVER_NAME'] . $this->here);
    }

	function beforeRender() {
		$this->set('projectname','UQMarkup');
		$this->set('baseURL',$this->baseURL);
		$this->setupBreadcrumb();
		$this->set('breadcrumbs',$this->breadcrumbs);
		$this->set('debug',$this->debug);
	}
	
	function setupBreadcrumb() {
		$this->array_unshift_assoc($this->breadcrumbs,'/','Home');
	}
	
	function permissionDenied($message) {
		$this->Session->setFlash('Permission Denied - '.$message);
		$this->redirect($this->referer());
		die();
	}
	
	function flashMessage($message,$redirect,$goodmessage=false) {
		if($goodmessage) {
			$this->Session->setFlash($message,'flash_success');
		} else {
			$this->Session->setFlash($message);
		}
		if($redirect != 'false') {
			$this->redirect($redirect);
		}
	}
	
	function array_unshift_assoc(&$arr, $key, $val) { 
	    $arr = array_reverse($arr, true); 
    	$arr[$key] = $val; 
    	$arr = array_reverse($arr, true); 
    	return count($arr); 
    }
    
    function emailToStudent($studentname,$course,$project,$submission,$studentemail) {
		$emailcontent = 'Dear '.$studentname.",\n\n";
		$emailcontent .= 'You now have assessment feedback available for '.$course['Course']['coursecode'].".\n";
		$emailcontent .= 'To view this feedback, please visit: https://uqmarkup.ceit.uq.edu.au/_dev/assessment/view/'.$this->encodeSubmissionID($submission['Submission']['id'])."\n";
		$emailcontent .= "Please use the 'contact us' form at the bottom of the page to let us know if you experience any issues.\n\nRegards,\nUQMarkup.";  
		$emaildata = array();
		$emaildata['subject'] = 'UQMarkup Feedback ready for '.$course['Course']['coursecode'].' '.$project['Project']['name'];
		$emaildata['subject_short'] = 'UQMarkup Feedback for '.$course['Course']['coursecode'];
		$emaildata['to'] = $studentemail;
		$emaildata['replyto'] = 'noreply@uqmarkup.ceit.uq.edu.au';
		$emaildata['returnPath'] = array('uqadekke@uq.edu.au');
		$emaildata['content'] = $emailcontent;
		$this->email($emaildata);
	}
	
	function moderationEmail($tutorname,$course,$feedback,$submission,$tutoremail) {
		$emailcontent = 'Dear '.$tutorname.",\n\n";
		$emailcontent .= 'You now have moderation feedback available for '.$course['Course']['coursecode'].".\n";
		$emailcontent .= "\nFeedback:\n======\n";
		$emailcontent .= $feedback."\n======\n\n";
		$emailcontent .= 'To view the submission that this feedback is related to, please visit: https://uqmarkup.ceit.uq.edu.au/_dev/assessment/view/'.$this->encodeSubmissionID($submission['Submission']['id'])."\n";
		$emailcontent .= "Please use the 'contact us' form at the bottom of the page to let us know if you experience any issues.\n\nRegards,\nUQMarkup.";  
		$emaildata = array();
		$emaildata['subject'] = 'UQMarkup moderation feedback for '.$course['Course']['coursecode'].' '.$submission['Project']['name'];
		$emaildata['subject_short'] = 'UQMarkup moderation for '.$course['Course']['coursecode'];
		$emaildata['to'] = $tutoremail;
		$emaildata['cc'] = array('j.kibedi@uq.edu.au');
		$emaildata['bcc'] = array('j.kibedi@uq.edu.au','uqadekke@uq.edu.au');
		$emaildata['returnPath'] = array('uqadekke@uq.edu.au');
		$emaildata['replyto'] = 'noreply@uqmarkup.ceit.uq.edu.au';
		$emaildata['content'] = $emailcontent;
		$this->email($emaildata);
	}
	
	function moderationAutomatedEmail($tutorname,$course,$feedback,$submission,$tutoremail) {
		$emailcontent = 'Dear '.$tutorname.",\n\n";
		$emailcontent .= 'You now have moderation feedback available for '.$course['Course']['coursecode'].".\n";
		$emailcontent .= $feedback;
		$emailcontent .= "Please use the 'contact us' form at the bottom of the page to let us know if you experience any issues.\n\nRegards,\nUQMarkup.";  
		$emaildata = array();
		$emaildata['subject'] = 'UQMarkup moderation feedback for '.$course['Course']['coursecode'].' '.$submission['Project']['name'];
		$emaildata['subject_short'] = 'UQMarkup moderation for '.$course['Course']['coursecode'];
		$emaildata['to'] = $tutoremail;
		$emaildata['bcc'] = array('j.kibedi@uq.edu.au','uqadekke@uq.edu.au');
		$emaildata['returnPath'] = array('uqadekke@uq.edu.au');
		$emaildata['replyto'] = 'noreply@uqmarkup.ceit.uq.edu.au';
		$emaildata['content'] = $emailcontent;
		$this->email($emaildata);
	}
    
    /* Database stuff */
	
	function setupStaticTables() {
		$this->roles = $this->Role->find('list');
		$this->states = $this->State->find('list');
		$this->logtypes = $this->Logtype->find('list');
	}
	
	function getStateID($_statename) {
		foreach($this->states as $stateid=>$statename) {
			if($statename == $_statename) {
				return $stateid;
			}
		}
		echo "INVALID STATE";
		die();
	}
	
	function getRoleID($_rolename) {
		foreach($this->roles as $roleid=>$rolename) {
			if($rolename == $_rolename) {
				return $roleid;
			}
		}
		echo "INVALID ROLE";
		die();
	}
	
	function getLogTypeID($_logname) {
		foreach($this->logtypes as $logid=>$logname) {
			if($logname == $_logname) {
				return $logid;
			}
		}
		echo "INVALID LOG TYPE";
		die();
	}
	
	function getcourse_folderid($course) {
		return $course['year'].'_'.$course['semester'].'_'.strtolower($course['coursecode']);
	}
	
	function getuploadfolder($project_id) {
		$project = $this->Project->findById($project_id);
		return $this->webdavsuburl.$this->getcourse_folderid($project['Course']).'/projectid_'.($project_id);
	}
	
	function refreshWebdavPermissions($course_id) {
		$course = $this->Course->find('first',array('conditions'=>array('id'=>$course_id),'recursive'=>-1));
		$coursecodeid = $this->getcourse_folderid($course['Course']);
		$coordinators = $this->CourseRoleUser->find('all',array('conditions'=>array('role_id'=>$this->getRoleID('Course Coordinator'),'course_id'=>$course_id)));
		$projects = $this->Project->find('list',array('conditions'=>array('course_id'=>$course_id)));
		foreach($coordinators as $coordinator) {
			$userdirectory = $this->webdavdir.$coordinator['User']['uqid'];
			if(!file_exists($userdirectory)) {
				mkdir($userdirectory,0744);
			}
			$coursedirectory = $userdirectory.'/'.$coursecodeid;
			if(!file_exists($coursedirectory)) {
				mkdir($coursedirectory,0744);
			}
			foreach($projects as $projectid=>$project) {
				$projectdirectory = $coursedirectory.'/projectid_'.($projectid);
				if(!file_exists($projectdirectory)) {	
					mkdir($projectdirectory,0744);
				}
			}
		}
	}
	
	/*
function getAudioDuration($audiopath) {
		$datasize = filesize($audiopath);
		$bitrate = 32;
		$KBps = ($bitrate*1000)/8;
		$length = $datasize / $KBps;
		$length = $length - 4.8; //something padded 
		return round($length);
	}
*/

	function getAudioDuration($audiopath) {
		$datasize = filesize($audiopath);
		$bitrate = 192;
		$KBps = ($bitrate*1000)/8;
		$length = $datasize / $KBps;
		//$length = $length - 4.8; //something padded 
		return round($length);
	}
	
	function encodeSubmissionID($id) {
		$input = 'UQM'.$id.'arkup';
		return strtr(base64_encode($input), '+/=', '-_,');
	}
	
	function decodeSubmissionID($hash) {
		$decoded = base64_decode(strtr($hash, '-_,', '+/='));
		$decoded = str_replace("UQM","",$decoded);
		$decoded = str_replace("arkup","",$decoded);
		return $decoded;
	}
	
	function compareClassLists($course) {
		$role_id = $this->getRoleID('Student');
		$course_id = $course['Course']['id'];
		$users = $this->Ldap->getClassList($course['Course']['coursecode'],$course['Course']['year'],$course['Course']['semester']);	
		if($course['Course']['shadowcode'] != '') {
		    $masters = $this->Ldap->getClassList($course['Course']['shadowcode'],$course['Course']['year'],$course['Course']['semester']);	
		    $users = array_merge($users,$masters);
		}
		$userids = array();
		echo '<table>';
		foreach($users as $user) {
			echo '<tr><td>'.$user['uqid'].'</td><td>'.$user['name'].'</td>';
			$uqid = $this->Ldap->getUserIDForUQLogin($user['uqid']);
			echo '<td>'.$uqid.'</td>';
			$usr = $this->User->find('first',array('conditions'=>array('id'=>$uqid),'recursive'=>-1));
			echo '<td>'.$usr['User']['name'].'</td>';
			$dup = '';
			if($usr['User']['name'] != $user['name']) {
				$dup = 'OH MY GOD';
			}
			echo '<td>'.$dup.'</td>';
			//print_r($usr);
		    //$userids[] = $this->Ldap->getUserIDForUQLogin($user['uqid']);
		    echo '</tr>';
		}
		echo '<table>';
	}
	
	function refreshClassList($course) {
		$role_id = $this->getRoleID('Student');
		$course_id = $course['Course']['id'];
		$users = $this->Ldap->getClassList($course['Course']['coursecode'],$course['Course']['year'],$course['Course']['semester']);	
		if($course['Course']['shadowcode'] != '') {
		    $masters = $this->Ldap->getClassList($course['Course']['shadowcode'],$course['Course']['year'],$course['Course']['semester']);	
		    $users = array_merge($users,$masters);
		}
		$userids = array();
		foreach($users as $user) {
		    $userids[] = $this->Ldap->getUserIDForUQLogin($user['uqid']);
		}
		//find and delete old associations
		$oldassociations = $this->CourseRoleUser->find('list',array('fields'=>array('id','user_id'),'conditions'=>array('role_id'=>$role_id,'course_id'=>$course_id)));
		foreach($oldassociations as $oldassociationid=>$oldassociationuserid) {
		    if(!in_array($oldassociationuserid,$userids)) {
		    	$this->CourseRoleUser->delete($oldassociationid);
		    }
		}
		//add in new associations
		foreach($userids as $newuserid) {
		    if(!in_array($newuserid,$oldassociations)) {
		    	$this->CourseRoleUser->create();
		    	$this->CourseRoleUser->save(array('role_id'=>$role_id,'course_id'=>$course_id,'user_id'=>$newuserid));
		    }
		}
	}
	
	function email($data) {
		if(!isset($data['template'])) {
			$data['template'] = 'default';
		}
		$this->set('title_for_layout', $data['subject']);
		$email = new CakeEmail();
		$email->from(array('noreply@uqmarkup.ceit.uq.edu.au' => 'UQMarkup'));
		$email->to($data['to']);
		if(isset($data['cc'])) {
			$email->cc($data['cc']);
		}
		if(isset($data['bcc'])) {
			$email->bcc($data['bcc']);
		} else {
			$email->bcc('uqadekke@uq.edu.au');
		}
		$email->replyTo($data['replyto']);
		$email->returnPath('uqadekke@uq.edu.au');
		//$email->setHeaders(array('X-Return-Path: uqadekke@uq.edu.au'));
		$email->template('default', $data['template']);
		$email->viewVars(array('content' => $data['content'],'baseURL'=>$this->baseURL,'title_for_layout'=>$data['subject'],'short_title'=>$data['subject_short']));
		$email->emailFormat('both');
		$email->subject($data['subject']);
		return $email->send($data['content']);
	}
	
	function loadProjectSubmissions($project) {
		$numberofprojectsadded = 0;
		$allowedExtensions = array('pdf','ppt');
		$coordinator_role = $this->getRoleID('Course Coordinator');
		$projectfolder = $this->getcourse_folderid($project['Course']).'/projectid_'.$project['Project']['id'];
		$coordinators = $this->CourseRoleUser->find('all',array('conditions'=>array('role_id'=>$coordinator_role,'course_id'=>$project['Course']['id'])));
		foreach($coordinators as $coordinator) {
		    $folder = $this->webdavdir.$coordinator['User']['uqid'].'/'.$projectfolder;	
		    if(file_exists($folder)) {
		    	//check for submissions
		    	$d = dir($folder);
		    	while (false !== ($file = $d->read())) {
		    		if(substr($file, 0, 1) != '.') {
		    			$extn = explode('.',$file);
		    			$extn = array_pop($extn);
		    			if (in_array(strtolower($extn),$allowedExtensions)) {
		    				if($extn == 'pdf') {
		    					$binary = sha1($projectfolder.'/'.$file);
		    					if(!file_exists($binary)) {
		    						if(rename($folder.'/'.$file,$this->binarydir.$binary)) {
		    							$this->Submission->create();
		    							if($this->Submission->save(array('project_id'=>$project['Project']['id']))) {
		    								$this->Attachment->create();
		    								if($this->Attachment->save(array('submission_id'=>$this->Submission->id,'path'=>$binary,'title'=>$file,'type'=>strtoupper($extn)))) {
		    									//echo 'Moved '.$folder.'/'.$file.' to '.$binary;		
		    									$numberofprojectsadded++;
		    								}
		    							}
		    						}
		    					} else {
		    						echo "Not overwriting files";
		    						die();
		    					}
		    				}/* else if($extn == 'ppt') {
		    					$test = array();
		    					putenv('URE_BOOTSTRAP=vnd.sun.star.pathname:/usr/lib64/libreoffice/program/fundamentalrc');
		    					putenv('UNO_PATH=/usr/lib64/libreoffice/program');
		    					putenv('LD_LIBRARY_PATH=/usr/lib64/libreoffice/program:/usr/lib64/libreoffice/basis-link/ure-link/lib');
		    					$conversion = '/var/www/webdav/to_pdf.sh /var/www/jojo/a.ppt';//'.$folder.'/'.$file;
		    					//$conversion = 'unoconv -vvv --listener &';
		    					//$conversion = '/usr/lib64/openoffice/program/soffice.bin -headless -nofirststartwizard -accept="socket,host=localhost,port=8100;urp;StarOffice.Service';
		    					//$conversion = 'which python';
		    					//$conversion = '/usr/bin/unoconv --listener -vvv';
		    					echo $conversion;
		    					$response = exec($conversion);
		    					print_r($jojo);
		    					echo 'done';
		    				}*/
		    			}
		    		}
   		    	}
   		    	$d->close();
		    }
		}
		return $numberofprojectsadded;
	}
	
	function archiveSubmission($submission_id) {
		$submission = $this->Submission->findById($submission_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$project_id = $project['Project']['id'];
		$coursecode = $project['Course']['coursecode'];
		$courseuid = $project['Course']['uid'];
		$states = $this->State->find('list');
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
		    if($this->Ldap->isCourseCoordinator($courseuid)) {
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
			$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
			$path = $this->versionsdir.$submission_id.'/'.$version['Version']['path'].'/';
			unset($submission['Log']);
			$safefilename = str_replace(" ","",$submission['Attachment'][0]['title']);
			$pdffile = $path.$safefilename;
			$annots = $this->annotations($submission_id);
			$zipname = $submission_id.'_archive_'.date('Y-m-d');
			$temp_file = tempnam(sys_get_temp_dir(), $zipname);
			$zip = new ZipArchive;
			$zip->open($temp_file, ZipArchive::CREATE);
			$zip->addFile($pdffile,'/'.$zipname.'/'.$safefilename);
			foreach($annots as $annot) {
				if($annot->type == 'Recording') {
				    $audiopath = $path.'annots/'.$annot->filename;
				    $zip->addFile($audiopath,'/'.$zipname.'/annots/'.$annot->title.'.'.substr($annot->filename,-3));
				}
			}
			$zip->close();
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.$zipname.'.zip');
			header('Content-Length: ' . filesize($temp_file));
			readfile($temp_file);
			die();
		}
		die();
	}

}
