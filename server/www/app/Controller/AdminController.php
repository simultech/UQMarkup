<?php

require_once("XML/Serializer.php");
App::uses('AppController', 'Controller');

class AdminController extends AppController {

	public $name = 'Admin';

	public $uses = array('Course','CourseRoleUser','Project','Tag','Rubric','Submission','Version','Surveyresult','Attachment','Activity','Log','Aggregatedmark');
	
	public $components = array('Ldap');
	
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
	
	public function moderationapp() {
		
	}
	
	public function userdump() {
		$this->layout = null;
		header("Content-type: text/plain");
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=userdump_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$types = array(
			'No login','Yes','No'
		);
		echo '"User ID","Terms Agreed To"'."\n";
		if($this->Ldap->isAdmin()) {
			$users = $this->User->find('all',array('recursive'=>-1));
			foreach($users as $user) {
				$terms = $user['User']['termsagreed'];
				if($terms != "" && $terms > -1) {
					$terms = $types[$terms];
				} else {
					$terms = $types[0];
				}
				echo '"'.$user['User']['uqid'].'","'.$terms.'"'."\n";
			}
			die();
		}
	}
	
	public function multigrade($project_id) {
		$this->set('project_id',$project_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('project',$project);
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['marks'] = $this->allmarks($submission['Submission']['id']);
					$finalmark = $this->Aggregatedmark->findBySubmissionId($submission['Submission']['id']);
					if(!empty($finalmark)) {
						$submission['finalmarks'] = (array)json_decode($finalmark['Aggregatedmark']['marks']);
					}
					//print_r($submission['marks']);
				}
				$rubrics = $this->Rubric->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$this->set('rubrics',$rubrics);
				$this->set('submissions',$submissions);
				if(!empty($this->data)) {
					foreach($this->data as $submission_id=>$data) {
						$existing = $this->Aggregatedmark->find('first',array('conditions'=>array('submission_id'=>$submission_id)));
						if(!empty($existing)) {
							$this->Aggregatedmark->id = $existing['Aggregatedmark']['id'];
							$this->Aggregatedmark->saveField('marks',json_encode($data));
						} else {
							$newdata = array(
								'submission_id'=>$submission_id,
								'marks'=>json_encode($data)
							);
							$this->Aggregatedmark->create();
							$this->Aggregatedmark->save($newdata);
						}
					}
					//echo '<pre>';
					//print_r($this->data);
					//die();
					$this->flash('Marks updated',$this->referer(),true);
				}
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function multigradecsv($project_id) {
		$this->layout = null;
		header("Content-type: text/plain");
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=multigradecsv_".$project_id."_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$this->set('project_id',$project_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('project',$project);
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['marks'] = $this->allmarks($submission['Submission']['id']);
					$finalmark = $this->Aggregatedmark->findBySubmissionId($submission['Submission']['id']);
					if(!empty($finalmark)) {
						$submission['finalmarks'] = (array)json_decode($finalmark['Aggregatedmark']['marks']);
					}
					//print_r($submission['marks']);
				}
				$rubrics = $this->Rubric->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$this->set('rubrics',$rubrics);
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function replacefiles() {
		$replacedir = '/var/www/webdav/replace';
		if ($handle = opendir($replacedir)) {
			while (false !== ($entry = readdir($handle))) {
				if (substr($entry, strrpos($entry, '.')) == '.pdf') { 
					$frompath = $replacedir.'/'.$entry;
					$topath = '';
					if($topath = $this->getversionpathforsubmissionid($entry)) {
						if(rename($frompath,$topath)) {
							echo 'Replaced: '.$frompath.' => '.$topath.'<br />';
						} else {
							echo 'wtf'.$frompath.'<br />';
						}
					}
				}
			}
		}
		die();
	}
	
	public function getMarks($project_id) {
		ini_set('memory_limit','384M');
		set_time_limit(180);
		$this->set('project_id',$project_id);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['marks'] = $this->allmarks($submission['Submission']['id']);
					//print_r($submission['marks']);
				}
				$this->set('rubrics',$project['Rubric']);
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function getMarksCsv($project_id) {
		ini_set('memory_limit','384M');
		set_time_limit(180);
		$this->set('project_id',$project_id);
		$this->layout = null;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=marks_".$project_id."_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['marks'] = $this->marks($submission['Submission']['id']);
					//print_r($submission['marks']);
				}
				$this->set('rubrics',$project['Rubric']);
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	
	public function multiannotations($project_id) {
		$this->set('project_id',$project_id);
		$this->layout = null;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=multiannotations_".$project_id."_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$submission['annotations'] = $this->allannotations($submission['Submission']['id'],false);
					//print_r($submission['marks']);
				}
				$this->set('submissions',$submissions);
			}
		}
	}
	
	public function audiolengthdata($project_id) {
		$this->set('project_id',$project_id);
		$this->layout = null;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=audiolengths_".$project_id."_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$marks = $this->marks($submission['Submission']['id']);
					$submission['annotations'] = $this->annotations($submission['Submission']['id'],true);
					$submission['markingtime'] = 0;
					if(is_object($marks) && isset($marks->time_spent_marking)) {
						$submission['markingtime'] = $marks->time_spent_marking;
					}
					$readsubmissions = 0;
					$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic','submission_id'=>$submission['Submission']['id']),'recursive'=>-1,'order'=>'created'));
					$submission['reading_time'] = round($this->calculateLogTimeAndSubmissionsRead($autologs,$readsubmissions)/3600,2);
				}
				$this->set('rubrics',$project['Rubric']);
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function multigradedatacount($project_id) {
		ini_set('memory_limit','384M');
		set_time_limit(240);
		$subs = 0;
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			$markerlogs = array();
			$annotationcounts = array();
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$subs = $this->Submission->find('count',array('conditions'=>array('project_id'=>$project_id)));
			}
		}
		echo $subs." submissions";
		die();
	}
	
	public function multigradedata($project_id,$page=0,$ppg=0) {
		$page = $page - 1;
		if($page < 0 && $ppg > 0) {
			echo 'invalid page';
			die();
		}
		ini_set('memory_limit','384M');
		set_time_limit(240);
		$this->set('project_id',$project_id);
		$this->layout = null;
		header("Content-type: text");
		header("Content-type: text/csv");
		if($ppg > 0) {
			header("Content-Disposition: attachment; filename=multigradedata_page_".$page."_".$ppg."_".$project_id."_".date('Y_m_d').".csv");
		} else {
			header("Content-Disposition: attachment; filename=multigradedata_".$project_id."_".date('Y_m_d').".csv");
		}
		header("Pragma: no-cache");
		header("Expires: 0");
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			$markerlogs = array();
			$annotationcounts = array();
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				//$this->Submission->unBindModel(array('hasMany' => array('Log')));
				if($ppg > 0) {
					$offset = ($page*$ppg);
					$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id),'limit'=>$ppg,'offset'=>$offset));
				} else {
					$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
					if($project_id == 34) {
						array_splice($submissions, 90, 1);
					}
				}
				$userlist = $this->User->find('list',array('fields'=>array('id','uqid')));
				foreach($submissions as &$submission) {
					//$marks = $this->marks($submission['Submission']['id']);
					//$submission['annotations'] = $this->annotations($submission['Submission']['id'],true);
					//$submission['markingtime'] = 0;
					//if(is_object($marks) && isset($marks->time_spent_marking)) {
					//	$submission['markingtime'] = $marks->time_spent_marking;
					//}
					//$readsubmissions = 0;
					//$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic','submission_id'=>$submission['Submission']['id']),'recursive'=>-1,'order'=>'created'));
					//$submission['reading_time'] = round($this->calculateLogTimeAndSubmissionsRead($autologs,$readsubmissions)/3600,2);
					foreach($submission["Log"] as &$log) {
						if($log['interaction'] == 'Annotations') {
							$marker = 'Unknown';
							$annotcount = 0;
							$meta = json_decode($log['meta']);
							//echo "AAA";
							
							//Go back through versions, find one that has a similar pattern
							$logsignature = sizeOf($meta->audio);
							//print_r($meta);
							//echo "BBB";
							$versions = $this->Version->find('list',array('fields'=>array('id','meta'),'conditions'=>array('submission_id'=>$submission['Submission']['id']),'recursive'=>-1,'order'=>array('created'=>'desc')));
							$versionkeys = array_keys($versions);
							$stopcount = false;
							foreach($versionkeys as $version) {
								$annots = $this->annotationsForVersion($submission['Submission']['id'],$version,true);
								$versionsig = 0;
								$annotation_id = '';
								foreach($annots as $annot) {
									if($annot->type == 'Recording') {
										if($annotation_id == '') {
											$annotation_id = str_replace('.m4a','',$annot->filename);
										}
										$versionsig++;
										if(!$stopcount) {
											$annotcount++;
										}
									}
								}
								if($logsignature == $versionsig."") {
									$valid = true;
									if($versionsig > 0) {
										$valid = false;
										foreach($meta->audio as $audiofile) {
											if(str_replace('.mp3','',$audiofile->filename) == $annotation_id) {
												$valid = true;
											}
										}
									}
									if($valid) {
										$markingdata = json_decode($versions[$version]);
										if(isset($markingdata->submitted_by)) {
											if($marker == 'Unknown') {
												$marker = $markingdata->submitted_by;	
											} else {
												if(strpos($marker, $markingdata->submitted_by) === false) {
													$marker = $marker.' or '.$markingdata->submitted_by;	
												}
											}
											$stopcount = true;
										}
									}
								}
							}
							$markerlogs[$log['runhash']] = $marker;
							$annotationcounts[$log['runhash']] = $annotcount;
						}
					}
				}
				$this->set('markerlogs',$markerlogs);
				$this->set('annotcounts',$annotationcounts);
				$this->set('submissions',$submissions);
				$this->set('userlist',$userlist);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function testDBTime() {
		ini_set('memory_limit','384M');
		$mtime = microtime(); 
		$mtime = explode(" ",$mtime); 
   		$mtime = $mtime[1] + $mtime[0]; 
   		$starttime = $mtime; 
   		$logs = $this->Log->find('list',array('fields'=>array('created')));
   		$orderedlogs_day = $this->getUsageTime($logs,'days');
   		$orderedlogs_hr = $this->getUsageTime($logs,'hours');
   		$this->set('orderedlogs_day',$orderedlogs_day);
   		$this->set('orderedlogs_hr',$orderedlogs_hr);
   		$mtime = microtime(); 
   		$mtime = explode(" ",$mtime); 
   		$mtime = $mtime[1] + $mtime[0]; 
   		$endtime = $mtime; 
   		$totaltime = ($endtime - $starttime); 
   		echo "This page was created in ".$totaltime." seconds"; 
	}
	
	public function accessgraph($project_id=0) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid) || $project_id == 0) {
				ini_set('memory_limit','384M');
				$project = $this->Project->find('first',array('conditions'=>array('id'=>$project_id),'recursive'=>-1));
				$course = $this->Course->find('first',array('conditions'=>array('id'=>$project['Project']['course_id']),'recursive'=>-1));
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Access Graph');
				$project_name = $course['Course']['coursecode'].' - '.$project['Project']['name'].' ('.$course['Course']['year'].'/'.$course['Course']['semester'].')';
				$submission_ids = $this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id)));
				$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic','submission_id'=>$submission_ids),'recursive'=>-1,'order'=>'created'));
				
				
				$totaltime = 0;
				$currenttime = array();
				$readsubmissions = array();
				$times = array();
				foreach($autologs as $log) {
					if(!in_array($log['Log']['submission_id'], $readsubmissions)) {
						$readsubmissions[] = $log['Log']['submission_id'];
					}
					if($log['Log']['meta'] == '{"state":"opened"}') {
						if(isset($currenttime[$log['Log']['sessionhash']])) {
							if($currenttime[$log['Log']['sessionhash']] != 0) {
								//echo 'BUGGER '.$log['Log']['sessionhash'];
							}
						}
						$currenttime[$log['Log']['sessionhash']] = strtotime($log['Log']['created']);
					}
					if($log['Log']['meta'] == '{"state":"closed"}') {
						$close = strtotime($log['Log']['created']);
						if(isset($currenttime[$log['Log']['sessionhash']])) {
							$addedtime = $close - $currenttime[$log['Log']['sessionhash']];
							if($currenttime[$log['Log']['sessionhash']] != 0) {
								//echo $addedtime.' - from '.date('Y-m-d H:i:s',$currenttime[$log['Log']['sessionhash']]).' to '.$log['Log']['created'].'<br />';
								$times[] = $addedtime;
								$totaltime += $addedtime;
								
								$currenttime[$log['Log']['sessionhash']] = 0;
							} else {
								//echo 'NO START';
							}
						}
					}
				}
				$this->set('times',$times);
		   	} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function audiologs($project_id=0) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid) || $project_id == 0) {
				ini_set('memory_limit','384M');
				//$project = $this->Project->find('first',array('conditions'=>array('id'=>$project_id),'recursive'=>-1));
				if($project_id == 0) {
					$submission_ids = $this->Submission->find('list',array('fields'=>array('id')));
				} else {
					$submission_ids = $this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id)));
				}
				$audiosubmissions = array();
				$audiologs = array();
				$logs = $this->Log->find('all',array('conditions'=>array('submission_id'=>$submission_ids,'interaction'=>'Audio'),'recursive'=>-1));
				foreach($logs as $log) {
					$audiologs[$log['Log']['submission_id']][] = $log;
				}
				foreach($submission_ids as $submission_id) {
					$audiodata = array();
					$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'fields'=>array('path'),'recursive'=>-1,'order'=>array('created'=>'desc')));
					$dir = $this->versionsdir.$submission_id.'/'.$version['Version']['path'];
					if(is_dir($dir)) {
					    if(file_exists($dir.'/annots/annots.json')) {
					    	$data = json_decode(file_get_contents($dir.'/annots/annots.json'));
					    	$audioid = 0;
					    	foreach($data as $dataannot) {
					    		if(isset($dataannot->type)) {
					    			if($dataannot->type == 'Recording' && isset($dataannot->filename)) {
				    					$audiofile = str_replace('.m4a','.mp3',$dir.'/annots/'.$dataannot->filename);
				    					//$audiofile = $dir.'/annots/'.$dataannot->filename;
				    					if(file_exists($audiofile)) {
				    						$audiodata[$audioid] = array('duration'=>$this->getAudioDuration($audiofile),'timespent'=>0,'filename'=>$dataannot->filename,'title'=>$dataannot->title,'max'=>0,'finished'=>0,'logs'=>array());
				    					}
				    					$audioid++;
					    			}
					    		}
					    	}
					    }
					}
					if(!empty($audiodata)) {
						if(isset($audiologs[$submission_id])) {
							foreach($audiologs[$submission_id] as $audiolog) {
								//print_r($audiolog);
								$data = json_decode($audiolog['Log']['meta']);
								if($data->state != 'finished' && $data->currentTime == 0 && $data->duration == 0) {
								} else {
									$audiodata[$data->annotation]['logs'][] = $data; 
								}
							}
							$audiosubmissions[$submission_id] = $audiodata;
						} else {
							//echo $submission_id.' has not been opened';
						}
					}
				}
				foreach($audiosubmissions as &$audiosubmission) {
					foreach($audiosubmission as &$zaudiofile) {
						$max = 0;
						foreach($zaudiofile['logs'] as $audlog) {
							$movement = $audlog->currentTime-$audlog->fromTime;
							if($audlog->state == 'finished') {
								if(isset($zaudiofile['finished'])) {
									$zaudiofile['finished'] = $zaudiofile['finished']+1;
								} else {
									$zaudiofile['finished'] = 1;
								}
							}
							$max = $movement;
						}
						$zaudiofile['max'] = $max;
					}
				}
				$totaltime = 0;
				$listenedtime = 0;
				
				foreach($audiosubmissions as &$audiosubmission) {
					foreach($audiosubmission as &$zaudiofile) {
						if(isset($zaudiofile['duration'])) {
							$totaltime += $zaudiofile['duration'];
							$listenedtime += $zaudiofile['max'];
						}
					}
				}
				return array('totaltime'=>$totaltime,'listenedtime'=>$listenedtime);
		   	} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function accessgraph2($project_id=0) {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid) || $project_id == 0) {
				ini_set('memory_limit','384M');
				$project = $this->Project->find('first',array('conditions'=>array('id'=>$project_id),'recursive'=>-1));
				$course = $this->Course->find('first',array('conditions'=>array('id'=>$project['Project']['course_id']),'recursive'=>-1));
				$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Access Graph');
				$project_name = $course['Course']['coursecode'].' - '.$project['Project']['name'].' ('.$course['Course']['year'].'/'.$course['Course']['semester'].')';
				$this->set('project_name',$project_name);
				$submission_ids = $this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id)));
				$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic','submission_id'=>$submission_ids),'recursive'=>-1,'order'=>'created'));
				
				
				$totaltime = 0;
				$currenttime = array();
				$readsubmissions = array();
				$times = array();
				foreach($autologs as $log) {
					if(!in_array($log['Log']['submission_id'], $readsubmissions)) {
						$readsubmissions[] = $log['Log']['submission_id'];
					}
					if($log['Log']['meta'] == '{"state":"opened"}') {
						if(isset($currenttime[$log['Log']['sessionhash']])) {
							if($currenttime[$log['Log']['sessionhash']] != 0) {
								//echo 'BUGGER '.$log['Log']['sessionhash'];
							}
						}
						$currenttime[$log['Log']['sessionhash']] = strtotime($log['Log']['created']);
					}
					if($log['Log']['meta'] == '{"state":"closed"}') {
						$close = strtotime($log['Log']['created']);
						if(isset($currenttime[$log['Log']['sessionhash']])) {
							$addedtime = $close - $currenttime[$log['Log']['sessionhash']];
							if($currenttime[$log['Log']['sessionhash']] != 0) {
								//echo $addedtime.' - from '.date('Y-m-d H:i:s',$currenttime[$log['Log']['sessionhash']]).' to '.$log['Log']['created'].'<br />';
								$times[] = $addedtime;
								$totaltime += $addedtime;
								
								$currenttime[$log['Log']['sessionhash']] = 0;
							} else {
								//echo 'NO START';
							}
						}
					}
				}
				$this->set('times',$times);
		   	} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function splittimes($project_id) {
		$this->layout = null;
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=splittimes_".$project_id."_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid) || $project_id == 0) {
				ini_set('memory_limit','384M');
				$this->Submission->unBindModel(array('hasMany' => array('Log')));
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				foreach($submissions as &$submission) {
					$annotations = $this->annotations($submission['Submission']['id']);
					$annotationcounts = array();
					$annotationcountpercentages = array();
					foreach($annotations as $annotation) {
						if(!isset($annotationcounts[$annotation->type])) {
							$annotationcounts[$annotation->type] = 0;
						}
						$annotationcounts[$annotation->type]++;
					}
					//$total = sizeOf($annotations);
					foreach($annotationcounts as $type=>$count) {
						$annotationcountpercentages[$type] = $count/sizeOf($annotations);
					}
					$submission['Annotationcounts'] = $annotationcounts;
					$submission['Annotationcountpercentages'] = $annotationcountpercentages;
					$submission['Markingtime'] = $this->getMarkingTime($submission['Submission']['id']);
				}
				$this->set('submissions',$submissions);
			}
		}
	}
	
	public function visualisation($project_id=0,$marker_id='') {
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course) && $project_id != 0) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid) || $project_id == 0) {
				ini_set('memory_limit','384M');
				$mtime = microtime(); 
				$mtime = explode(" ",$mtime); 
		   		$mtime = $mtime[1] + $mtime[0]; 
		   		$starttime = $mtime; 
		   		$logs = array();
		   		$stats = array();
		   		$autologs = array();
				if($project_id == 0) {
					$this->breadcrumbs = array('All Project Statistics');
					$submission_ids = $this->Submission->find('list',array('fields'=>array('id')));
					$submission_ids = $this->Activity->find('list',array('fields'=>array('submission_id'),'conditions'=>array('submission_id'=>$submission_ids,'state_id'=>'6')));
					$logs = $this->Log->find('list',array('fields'=>array('created')));	
					$openedlogs = $this->Log->find('list',array('conditions'=>array('meta'=>'{"state":"opened"}'),'fields'=>array('created')));	
					$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic'),'recursive'=>-1,'order'=>'created'));
					$versionedsubmissions = array_unique(array_values($this->Version->find('list',array('fields'=>array('submission_id'),'conditions'=>array('submission_id'=>$submission_ids,'meta'=>'{"submitted_by":"uqadekke"}')))));
					$stats['count_total'] = sizeOf($submission_ids);
					$stats['count_traditional'] = sizeOf($versionedsubmissions);
					$stats['count_uqmarkup'] = $stats['count_total'] - $stats['count_traditional'];
					$project_name = 'All Projects';
					$this->set('audio_listening',$this->audiologs());
				} else {
					$project = $this->Project->find('first',array('conditions'=>array('id'=>$project_id),'recursive'=>-1));
					$course = $this->Course->find('first',array('conditions'=>array('id'=>$project['Project']['course_id']),'recursive'=>-1));
					$this->breadcrumbs = array('/course/admin/'.$course['Course']['coursecode']=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Project Statistics');
					$project_name = $course['Course']['coursecode'].' - '.$project['Project']['name'].' ('.$course['Course']['year'].'/'.$course['Course']['semester'].')';
					$submission_ids = $this->Submission->find('list',array('fields'=>array('id'),'conditions'=>array('project_id'=>$project_id)));
					$submission_ids = $this->Activity->find('list',array('fields'=>array('submission_id'),'conditions'=>array('submission_id'=>$submission_ids,'state_id'=>'6')));
					if($marker_id != '') {
						$submission_ids = $this->Activity->find('list',array('fields'=>array('submission_id'),'conditions'=>array('submission_id'=>$submission_ids,'state_id'=>'4','meta'=>$marker_id)));
						$this->set('marker_id',$marker_id);
					}
					$logs = $this->Log->find('list',array('fields'=>array('created'),'conditions'=>array('submission_id'=>$submission_ids)));
					$openedlogs = $this->Log->find('list',array('conditions'=>array('meta'=>'{"state":"opened"}','submission_id'=>$submission_ids),'fields'=>array('created')));	
					$autologs = $this->Log->find('all',array('conditions'=>array('interaction'=>'Automatic','submission_id'=>$submission_ids),'recursive'=>-1,'order'=>'created'));
					$versionedsubmissions = array_unique(array_values($this->Version->find('list',array('fields'=>array('submission_id'),'conditions'=>array('submission_id'=>$submission_ids,'meta'=>'{"submitted_by":"uqadekke"}')))));
					$stats['count_total'] = sizeOf($submission_ids);
					$stats['count_traditional'] = sizeOf($versionedsubmissions);
					$stats['count_uqmarkup'] = $stats['count_total'] - $stats['count_traditional'];
					$this->set('audio_listening',$this->audiologs($project_id));
				}
				$this->set('title_for_layout', 'Report_'.$project_name.'_'.date('Y-m-d'));
				$orderedlogs_day = $this->getUsageTime($logs,'days');
		   		$orderedlogs_hr = $this->getUsageTime($logs,'hours');
		   		$this->set('orderedlogs_day',$this->getUsageTime($logs,'days'));
		   		$this->set('orderedlogs_hr',$this->getUsageTime($logs,'hours'));
		   		$this->set('openedlogs_day',$this->getUsageTime($openedlogs,'days'));
		   		$this->set('openedlogs_hr',$this->getUsageTime($openedlogs,'hours'));
		   		$this->set('project_name',$project_name);
		   		
		   		//submission time and annots
		   		$markingtime = 0;
		   		$notrecorded = 0;
		   		$audiotime = 0;
		   		$annots = array('Total'=>0,'Recording'=>0,'Highlight'=>0,'Text'=>0,'Freehand'=>0);
		   		$audiosubmissions = array();
				foreach($submission_ids as $submission_id) {
					//marking time
					$addedmarkingtime = $this->getMarkingTime($submission_id);
					if($addedmarkingtime > 0) {
						$markingtime += $addedmarkingtime;
					} else {
						$notrecorded++;
					}
					//annots
					$this->addAnnotsData($submission_id,$annots,$audiotime,$audiosubmissions);
				}
				$readsubmissions = 0;
				$stats['reading_time'] = round($this->calculateLogTimeAndSubmissionsRead($autologs,$readsubmissions)/3600,2);
				$stats['marking_time'] = round($markingtime/3600,2);
				$stats['marking_notrecorded'] = $notrecorded;
		   		$stats['annots'] = $annots;
		   		$stats['read_submissions'] = $readsubmissions;
		   		$stats['audio_submissions'] = $audiosubmissions;
		   		$stats['audio_time'] = round($audiotime/3600,2);
		   		$this->set('stats',$stats);
				$mtime = microtime(); 
		   		$mtime = explode(" ",$mtime); 
		   		$mtime = $mtime[1] + $mtime[0]; 
		   		$endtime = $mtime; 
		   		$totaltime = ($endtime - $starttime); 
		   		$this->set('totaltime',round($totaltime,2));
		   	} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function calculateLogTimeAndSubmissionsRead($logs,&$readsubmissions) {
		$totaltime = 0;
		$currenttime = array();
		$readsubmissions = array();
		foreach($logs as $log) {
			if(!in_array($log['Log']['submission_id'], $readsubmissions)) {
				$readsubmissions[] = $log['Log']['submission_id'];
			}
			if($log['Log']['meta'] == '{"state":"opened"}') {
				if(isset($currenttime[$log['Log']['sessionhash']])) {
					if($currenttime[$log['Log']['sessionhash']] != 0) {
						//echo 'BUGGER '.$log['Log']['sessionhash'];
					}
				}
				$currenttime[$log['Log']['sessionhash']] = strtotime($log['Log']['created']);
			}
			if($log['Log']['meta'] == '{"state":"closed"}') {
				$close = strtotime($log['Log']['created']);
				if(isset($currenttime[$log['Log']['sessionhash']])) {
					$addedtime = $close - $currenttime[$log['Log']['sessionhash']];
					if($currenttime[$log['Log']['sessionhash']] != 0) {
						//echo $addedtime.' - from '.date('Y-m-d H:i:s',$currenttime[$log['Log']['sessionhash']]).' to '.$log['Log']['created'].'<br />';
						$totaltime += $addedtime;
						$currenttime[$log['Log']['sessionhash']] = 0;
					} else {
						//echo 'NO START';
					}
				}
			}
			//print_r($log);
		}
		return $totaltime;
	}
	
	function getMarkingTime($submission_id) {
		$markingtime = 0;
		$dir = $this->versionsdir.$submission_id.'/';
		if(is_dir($dir)) {
			if ($handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
					$latestdir = '';
					$latestdirmod = 0;
					if($entry != '.' && $entry != '..') {
						$versiondir = $dir.$entry;
						if(filemtime($versiondir) > $latestdirmod) {
							$latestdirmod = filemtime($versiondir);
							$latestdir = $versiondir;
						} 
					}
					if($latestdir != '') {
						if(file_exists($latestdir.'/marks.json')) {
							$data = json_decode(file_get_contents($latestdir.'/marks.json'));
							if(isset($data->time_spent_marking)) {
								$markingtime = $data->time_spent_marking;
							} else {
								$markingtime = -1;
							}
						}
						//read the file
					}
				}
			}
		}
		return $markingtime;
	}
	
	function addAnnotsData($submission_id,&$annots,&$audiotime,&$audiosubmissions) {
		$markingtime = 0;
		
		$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'fields'=>array('path'),'recursive'=>-1,'order'=>array('created'=>'desc')));
		$dir = $this->versionsdir.$submission_id.'/'.$version['Version']['path'];
		if(is_dir($dir)) {
		    if(file_exists($dir.'/annots/annots.json')) {
		    	$data = json_decode(file_get_contents($dir.'/annots/annots.json'));
		    	foreach($data as $dataannot) {
		    		if(isset($dataannot->type)) {
		    			$annots[$dataannot->type] = $annots[$dataannot->type]+1;
		    			$annots['Total'] = $annots['Total']+1;
		    			if($dataannot->type == 'Recording') {
		    				if(!in_array($submission_id, $audiosubmissions)) {
			    				$audiosubmissions[] = $submission_id;
		    				}
		    				if(isset($dataannot->filename)) {
			    				$audiofile = str_replace('.m4a','.mp3',$dir.'/annots/'.$dataannot->filename);
			    				//$audiofile = $dir.'/annots/'.$dataannot->filename;
			    				if(file_exists($audiofile)) {
				    				$audiotime += $this->getAudioDuration($audiofile).',';
				    			}
				    		}
		    			}
		    		}
			    }
			}
		}
	}
	
	function getUsageTime($logs,$timescale) {
		$orderedlogs = array();
		foreach($logs as $log) {
			$log = date('Y-m-d H:i:s',strtotime($log)-time()+strtotime("-10 hours"));
			$timeperiod = substr($log,0,10);
			$dateformat = 'Y-m-d';
			if($timescale == 'hours') {
				$timeperiod = substr($log,11,2).':00:00';
				$dateformat = 'Ha';
			}
			$timeperiod = date($dateformat,strtotime($timeperiod));
			if(isset($orderedlogs[$timeperiod])) {
				$orderedlogs[$timeperiod] = $orderedlogs[$timeperiod]+1;
			} else {
				$orderedlogs[$timeperiod] = 1;
			}
		}
		ksort($orderedlogs);
		if($timescale == 'days') {
			$keys = array_keys($orderedlogs);
			$shortest = strtotime($keys[0]);
			$longest = strtotime($keys[sizeOf($keys)-1]);
			$current = $shortest;
			$timeout = 0;
			while($current < $longest) {
				$fmdate = date('Y-m-d',$current);
				if(!isset($orderedlogs[$fmdate])) {
					$orderedlogs[$fmdate] = 0;
				}
				$current = $current+24*3600;
				$timeout++;
				if($timeout > 200) {
					echo 'boo';
					die();
				}
			}
		} else {
			for($i=0; $i<24; $i++) {
				$newtime = date('Ha',strtotime($i.':00:00'));
				if(!isset($orderedlogs[$newtime])) {
					$orderedlogs[$newtime] = 0;
				}
			}
		}
		ksort($orderedlogs);
		return $orderedlogs;
	}
	
	public function getversionpathforsubmissionid($filename) {
		$attachment = $this->Attachment->find('all',array('conditions'=>array('title'=>$filename)));
		if(sizeOf($attachment) > 1) {
			echo 'too many';
			return null;
		}
		$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$attachment[0]['Attachment']['submission_id']),'order'=>array('Version.created'=>'desc')));
		print_r($version);
		if(!empty($version)) {
			$fullpath = '/var/www/webdav/versions/'.$attachment[0]['Attachment']['submission_id'].'/'.$version['Version']['path'].'/'.$attachment[0]['Attachment']['title'];
			return $fullpath;
		}
		return null;
	}
	
	public function projectstats($project_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Project Statistics');
				$this->set('project',$project);
				$this->set('submissions',$this->organiseProjectData($project_id));
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function audiolist($project_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$recording = 0;
				$total = 0;
				$users = array();
				$usersmarkcount = array();
				$usersaudiocount = array();
				$totalaudioannotations = 0;
				$submissionseachaudio = array();
				$this->breadcrumbs = array('/course/admin/'.$courseuid=>'Manage '.$course['Course']['coursecode'],'/projects/admin/'.$project_id=>'Manage '.$project['Project']['name'],'/admin/projectstats/'.$project_id=>'Project Statistics');
				$this->set('project',$project);
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id),'recursive'=>-1));
				foreach($submissions as &$submission) {
					$audiocount = 0;
					$submission['marker'] = $this->Activity->find('first',array('fields'=>array('meta'),'conditions'=>array('state_id'=>2,'submission_id'=>$submission['Submission']['id'])));
					$annotations = $this->annotations($submission['Submission']['id']);
					
					if(!isset($users[$submission['marker']['Activity']['meta']])) {
						$users[$submission['marker']['Activity']['meta']] = 0;
					}
					if(!isset($usersmarkcount[$submission['marker']['Activity']['meta']])) {
						$usersmarkcount[$submission['marker']['Activity']['meta']] = 0;
					}
					if(!isset($usersaudiocount[$submission['marker']['Activity']['meta']])) {
						$usersaudiocount[$submission['marker']['Activity']['meta']] = 0;
					}
					
					foreach($annotations as $annotation) {
						if(!isset($submission['annot'][$annotation->type])) {
							$submission['annot'][$annotation->type] = 0;
						}
						if($annotation->type == 'Recording') {
							$totalaudioannotations++;
							$audiocount++;
							$usersaudiocount[$submission['marker']['Activity']['meta']]++;
						}
						$submission['annot'][$annotation->type] += 1;
					}
					
					if(isset($submission['annot']['Recording'])) {
						$recording++;
						$users[$submission['marker']['Activity']['meta']]++;
					}
					
					
					$usersmarkcount[$submission['marker']['Activity']['meta']]++;
					$total++;
					$submissionseachaudio[] = $audiocount;
				}
				$numaudio = 0;
				$audiosum = 0;
				foreach($submissionseachaudio as $submissionseachaudiothing) {
					if($submissionseachaudiothing > 0) {
						$numaudio++;
					}
					$audiosum += $submissionseachaudiothing;
				}
				//print_r($submissionseachaudio);
				echo 'something'.$this->sd($submissionseachaudio);
				echo 'average: '.($audiosum/$numaudio);
				echo 'final recording '.$totalaudioannotations.'..';
				foreach($usersaudiocount as $username=>$audiototal) {
					$av = $audiototal/$usersmarkcount[$username];
					echo '<p>User: '.$username.': '.$audiototal.' total audio annotations (Average: '.$av.')'."</p>";
				}
				echo $recording.' of '.$total.' submissions have audio annotations';
				die();
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function sd_square($x, $mean) { return pow($x - $mean,2); }
	
	function sd($array) {
		return sqrt(array_sum(array_map(array($this, "sd_square"), $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}
	
	public function rawprojectstats($project_id,$type='data') {
		ini_set('memory_limit','256M');
		$format = 'json';
		$pretty = false;
		if(isset($_GET['pretty']) && $_GET['pretty'] == 'true') {
			$pretty = true;
		}
		if(isset($_GET['format']) && $_GET['format'] == 'xml') {
			$format = 'xml';
		}
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				if($type == 'meta') {
					$thedata = $project;
				} else {
					$thedata = $this->organiseProjectData($project_id);
				}
				header('Cache-Control: no-cache, must-revalidate');
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				switch($format) {
					case 'json':
						header('Content-type: application/json');
						$formatteddata = json_encode($thedata);
						if($pretty) {
							$formatteddata = $this->json_format($formatteddata);
						}
						echo $formatteddata;
						break;
					case 'xml':
						header('Content-type: application/xml');
						$formatteddata = $this->json_to_xml(json_encode($thedata));
						if($pretty) {
							$formatteddata = $this->xml_pretty($formatteddata);
						}
						echo $formatteddata;
						break;
				}
				die();
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	function json_pretty($json) { 
	    $tab = "  "; 
	    $new_json = ""; 
	    $indent_level = 0; 
	    $in_string = false; 
	    $json_obj = json_decode($json); 
	    if($json_obj === false) 
	        return false; 
	    $json = json_encode($json_obj); 
	    $len = strlen($json); 
	    for($c = 0; $c < $len; $c++) { 
	        $char = $json[$c]; 
	        switch($char) { 
	            case '{': 
	            case '[': 
	                if(!$in_string) { 
	                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
	                    $indent_level++; 
	                } else { 
	                    $new_json .= $char; 
	                } 
	                break; 
	            case '}': 
	            case ']': 
	                if(!$in_string) { 
	                    $indent_level--; 
	                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
	                } else { 
	                    $new_json .= $char; 
	                } 
	                break; 
	            case ',': 
	                if(!$in_string) { 
	                    $new_json .= ",\n" . str_repeat($tab, $indent_level); 
	                } else { 
	                    $new_json .= $char; 
	                } 
	                break; 
	            case ':': 
	                if(!$in_string) { 
	                    $new_json .= ": "; 
	                } else { 
	                    $new_json .= $char; 
	                } 
	                break; 
	            case '"': 
	                if($c > 0 && $json[$c-1] != '\\') { 
	                    $in_string = !$in_string; 
	                } 
	            default: 
	                $new_json .= $char; 
	                break;                    
	        } 
	    } 
	    return $new_json; 
	} 
	
	function xml_pretty($xml, $html_output=false) {
	    $xml_obj = new SimpleXMLElement($xml);
	    $level = 4;
	    $indent = 0; // current indentation level
	    $pretty = array();
	    // get an array containing each XML element
	    $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

	    // shift off opening XML tag if present
	    if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
		    $pretty[] = array_shift($xml);
		}
		foreach ($xml as $el) {
			if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
				// opening tag, increase indent
				$pretty[] = str_repeat(' ', $indent) . $el;
				$indent += $level;
			} else {
				if (preg_match('/^<\/.+>$/', $el)) {            
					$indent -= $level;  // closing tag, decrease indent
				}
				if ($indent < 0) {
					$indent += $level;
				}
				$pretty[] = str_repeat(' ', $indent) . $el;
			}
		}   
		$xml = implode("\n", $pretty);   
		return ($html_output) ? htmlentities($xml) : $xml;
	}
	
	function json_to_xml($json) {
    	$serializer = new XML_Serializer();
	    $obj = json_decode($json);
	    if ($serializer->serialize($obj)) {
        	return $serializer->getSerializedData();
        }
	    else {
        	return null;
        }
    }
	
	function organiseProjectData($project_id) {
		$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
		foreach($submissions as &$submission) {
		    $submission['data'] = $this->getSubmissionActivityData($submission);
		    foreach($submission['Activity'] as $activity) {
		    	if ($activity['state_id'] == 1) {
		    		$submission['data']['student_id'] = $activity['meta'];
		    		//$submission['data']['Student'] = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>-1));
		    		$submission['data']['surveyresponses'] = $this->Surveyresult->find('count',array('conditions'=>array('project_id'=>$project_id,'user_id'=>$submission['data']['submittedby']['id'])));
		    	}
		    	if ($activity['state_id'] == 4) {
		    		$submission['data']['marker'] = $activity['meta'];
		    	}
		    }
		    $pages = 'Unrecorded';
		    $listeningtime = 0;
		    $viewingtime = 0;
		    $viewingtimestring = '';
		    $laststarttime = '';
		    $sessions = 0;
		    $lastsessionhash = '';
		    usort($submission['Log'],array($this,'sortentries'));
		    foreach($submission['Log'] as $log) {
		    	if($log['sessionhash'] != $lastsessionhash) {
		    		$lastsessionhash = $log['sessionhash'];
		    		$sessions++;
		    	}
		    	if($log['interaction'] == 'Automatic') {
		    		if($log['meta'] == '{"state":"closed"}') {
		    			if($laststarttime != '') {
		    				$viewingtime += strtotime($log['created']) - strtotime($laststarttime);
		    				$viewingtimestring .= 'Closing '.$log['created'].' ('.(strtotime($log['created']) - strtotime($laststarttime)).' seconds)<br />';
		    			}
		    			$laststarttime = '';
		    		} else if($log['meta'] == '{"state":"opened"}') {
		    			$laststarttime = $log['created'];
		    			$viewingtimestring .= 'Opening '.$log['created'].'<br />';
		    		}
		    	}
		    	if($log['interaction'] == 'Details' && $pages == 'Unrecorded') {
		    		$details = json_decode($log['meta']);
		    		$pages = $details->pages;
		    	}
		    }
		    $audiotime = 0;
		    $submission['data']['numaudio'] = sizeOf($submission['data']['annotations']);
		    $numofaudio = 0;
		    foreach($submission['data']['annotations'] as $annot) {
		    	if($annot->type == 'Recording') {
		    		$audiotime += $annot->duration;
		    		$numofaudio++;
		    	}
		    }
		    $submission['data']['numaudio'] .= ' ( '.$numofaudio.' audio annotations )';
		    $submission['data']['audiotime'] = $audiotime;
		    $submission['data']['listeningtime'] = $listeningtime;
		    $submission['data']['viewingtime'] = $viewingtime;
		    $submission['data']['viewingtimestring'] = $viewingtimestring;
		    $submission['data']['pages'] = $pages;
		    $submission['data']['sessions'] = $sessions;
		}
		return $submissions;
	}
	
	public function stats($project_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$project_id)));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('project',$project);
				$submissions = $this->Submission->find('all',array('conditions'=>array('project_id'=>$project_id)));
				$this->set('submissions',$submissions);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function submissionstats_OLD($submission_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submission['data'] = $this->getSubmissionActivityData($submission);
				$submission['sessions'] = $this->getSessions($submission);
				$this->set('project',$project);
				$this->set('submission',$submission);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function submissionstats($submission_id) {
		$this->set('rubrictypes',$this->rubrictypes);
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$submission['data'] = $this->getSubmissionActivityData($submission);
				$submission['sessions'] = $this->getSessionsNew($submission);
				$this->set('project',$project);
				$this->set('submission',$submission);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function surveystats($submission_id) {
		$submission = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id)));
		$project = $this->Project->find('first',array('conditions'=>array('Project.id'=>$submission['Project']['id'])));
		$courseuid = $project['Course']['uid'];
		if($this->courseadmin) {
			$course = $this->Course->findByUid($courseuid);
			if(empty($course)) {
				$this->permissionDenied('Not a valid course');
			}
			//check if they are a course coordinator
			if($this->Ldap->isCourseCoordinator($courseuid)) {
				$this->set('project',$project);
				$student = array();
				foreach($submission['Activity'] as $activity) {
					if($activity['state_id'] == 1) {
						$student = $this->CourseRoleUser->find('first',array('conditions'=>array('CourseRoleUser.id'=>$activity['course_role_users_id'])));
					}
				}
				$survey_id = 1;
				$responseData = $this->Surveyresult->find('all',array('conditions'=>array('user_id'=>$student['User']['id'],'project_id'=>$submission['Project']['id'],'survey_id'=>$survey_id)));
				$existingresponses = array();
				foreach ($responseData as $rdata) {
					$existingresponses[$rdata['Surveyresult']['question_id']] = $rdata['Surveyresult']['answer'];
				}
				$surveypath = $this->surveydir.$submission['Project']['id'].'_'.$survey_id.'.csv';
				ini_set("auto_detect_line_endings", 1);
				if(file_exists($surveypath)) {
					$questions = $this->get2DArrayFromCsv($surveypath,",");
					$this->set('questions',$questions);
				}
				$this->set('existingresponses',$existingresponses);
				$this->set('student',$student['User']['uqid'].' ('.$student['User']['name'].')');
				$this->set('submission',$submission);
			} else {
				$this->permissionDenied('You are not a coordinator for this course');
			}
		} else {
			$this->permissionDenied('Not an authorised administrator');
		}
	}
	
	public function tutorsubmissioncount($courseuid) {
		ini_set('memory_limit','384M');
		set_time_limit(180);
		$this->layout = null;
		header("Content-type: text/plain");
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=tutorsubmissioncount_".date('Y_m_d').".csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		$courses = $this->Course->find('all',array('order'=>array('year','semester')));
		foreach($courses as $course) {
			$markers = array();
			foreach($course['CourseRoleUser'] as $courseroleuser) {
			    if($courseroleuser['role_id'] > 1) {
			    	$marker = array();
			    	$markers[] = $courseroleuser['user_id'];
			    }
			}
			$this->User->unBindModel(array('hasMany' => array('Log')));
			$markers = $this->User->find('all',array('conditions'=>array('id'=>$markers)));
			$projects = $course['Project'];
			$submissions = array();
			$projectmarkcounts = array();
			foreach($projects as $project) {
			    $projectmarkcount = array();
			    $submissions = $this->Submission->find('list',array('conditions'=>array('project_id'=>$project['id'])));
			    $markedbys = $this->Activity->find('all',array('conditions'=>array('submission_id'=>$submissions,'state_id'=>'4'),'recursive'=>-1));
			    foreach($markedbys as $markedby) {
			    	if(!isset($projectmarkcount[$markedby['Activity']['meta']])) {
			    		$projectmarkcount[$markedby['Activity']['meta']] = 0;
			    	}
			    	$projectmarkcount[$markedby['Activity']['meta']]++;
			    }
			    $projectmarkcounts[$project['id']] = $projectmarkcount;
			
			}
			echo 'Tutor,';
			$projectidlistorder = array();
			foreach($projects as $project) {
			    echo $course['Course']['uid'].':'.$project['name'].',';
			    $projectidlistorder[] = $project['id'];
			}
			echo "\n";
			foreach($markers as $marker) {
			    echo $marker['User']['uqid'].",";
			    foreach($projectidlistorder as $projectid) {
			    	if(!isset($projectmarkcounts[$projectid][$marker['User']['uqid']])) {
			    		echo '0,';
			    	} else {
			    		echo $projectmarkcounts[$projectid][$marker['User']['uqid']].",";
			    	}
			    }
			    echo "\n";
			}
		}
		//print_r($projectmarkcounts);
		die();
	}
	
	public function index() {
		
	}
	
	function sortentries($a,$b) {
		$al = $a['created'];
		$bl = $b['created'];
		if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
	}
	
	function getSessionsNew($submission) {
		$sessions = array();
		foreach($submission['Log'] as $interaction) {
			@$sessions[$interaction['sessionhash']][$interaction['runhash']]['Interactioncount']++;
			switch($interaction['interaction']) {
				case 'Automatic':
					if($interaction['meta'] == '{"state":"closed"}') {
						$sessions[$interaction['sessionhash']][$interaction['runhash']]['Closed'] = $interaction['created'];
					} else if($interaction['meta'] == '{"state":"opened"}') {
						$sessions[$interaction['sessionhash']][$interaction['runhash']]['Opened'] = $interaction['created'];
						$userdetails = $this->User->find('first',array('conditions'=>array('User.id'=>$interaction['user_id']),'recursive'=>-1));
						$sessions[$interaction['sessionhash']][$interaction['runhash']]['User'] = $userdetails['User'];
					}
					break;
				case 'Details':
					$sessions[$interaction['sessionhash']][$interaction['runhash']]['Details'] = json_decode($interaction['meta']);
					break;
				case 'Annotations':
					$sessions[$interaction['sessionhash']][$interaction['runhash']]['Annotations'] = json_decode($interaction['meta']);
					break;
				default:
					$interaction['meta'] = json_decode($interaction['meta']);
					$sessions[$interaction['sessionhash']][$interaction['runhash']]['Logs'][$interaction['interaction']][] = $interaction;
			}
		}
		return $sessions;
	}
	
	function getSessions($submission) {
		$sessions = array();
		foreach($submission['Log'] as $interaction) {
			$sessions[$interaction['sessionhash']]['entries'][] = $interaction;
		}
		foreach($sessions as $sessionid=>&$session) {
			if(isset($session['entries'])) {
				usort($session['entries'],array($this,'sortentries'));
			}
		}
		foreach($sessions as $sessionid=>&$session) {
			$session['runs'] = array();
			$currentrun = array();
			if(isset($session['entries'])) {
				echo 'new session';
			    foreach($session['entries'] as &$entry) {
			    	$entry['created'] = date("Y-m-d H:i:s",strtotime($entry['created'])-(60*60*10));
			    	//closed
			    	$entry['meta'] = json_decode($entry['meta']);
			    	if($entry['interaction'] == 'Automatic' && $entry['meta']->state == 'opened') {
			    		if(!isset($currentrun['data']['Audio'])) {
			    			$currentrun['data']['Audio'] = array();
			    		}
			    		if(!isset($currentrun['data']['Scroll'])) {
			    			$currentrun['data']['Scroll'] = array();
			    		}
			    		if(isset($currentrun['data']['Details'])) {
			    			$currentrun['pages'] = $currentrun['data']['Details'][0]['meta']->pages;
			    			$currentrun['pagelengths'] = explode(',',$currentrun['data']['Details'][0]['meta']->pagelengths);
			    		}
			    		if(!empty($currentrun)) {
			    			$session['runs'][] = $currentrun;
			    		}
			    		$currentrun = array();
			    		$currentrun['starttime'] = $entry['created'];
			    		$currentrun['user_id'] = $entry['user_id'];
			    		$currentrun['platform'] = $entry['platform'];
			    	} else if($entry['interaction'] == 'Automatic' && $entry['meta']->state == 'closed') {
			    		$currentrun['endtime'] = $entry['created'];
			    	} else {
			    		$currentrun['data'][$entry['interaction']][] = $entry;
			    	}
			    }
			    unset($session['entries']);
			    if(!empty($currentrun)) {
			    	if(!isset($currentrun['data']['Audio'])) {
			    		$currentrun['data']['Audio'] = array();
			    	}
			    	if(!isset($currentrun['data']['Scroll'])) {
			    		$currentrun['data']['Scroll'] = array();
			    	}
			    	if(isset($currentrun['data']['Details'])) {
			    		$currentrun['pages'] = $currentrun['data']['Details'][0]['meta']->pages;
			    		$currentrun['pagelengths'] = explode(',',$currentrun['data']['Details'][0]['meta']->pagelengths);
			    	}
			    	$session['runs'][] = $currentrun;
			    }
			}
		}
		return $sessions;
	}
	
	function getSubmissionActivityData($submission) {
		$data = array();
		$data['uri'] = $this->baseURL.'/assessment/view/'.$this->encodeSubmissionID($submission['Submission']['id']);
		//$version = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission['Submission']['id']),'order'=>array('created'=>'desc'),'recursive'=>-1));
		$data['annotations'] = $this->annotations($submission['Submission']['id']);
		$data['marks'] = $this->marks($submission['Submission']['id']);
		$data['rubricmarks'] = $this->getRubricMarks($submission,$data['marks']);
		foreach($submission['Activity'] as $activity) {
			if($activity['state_id'] == 1) {
				$user = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>1));
				if(isset($data['submittedby']) && !isset($data['submittedbygroup'])) {
					$data['submittedbygroup'][] = $data['submittedby'];
				}
				$data['submittedby'] = $user['User'];
				$data['submittedbygroup'][] = $user['User'];
			}
			if($activity['state_id'] == 4) {
				$user = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>1));
				if(isset($data['markedby']) && !isset($data['markedbygroup'])) {
					$data['markedbygroup'][] = $data['markedby'];
				}
				$data['markedby'] = $user['User'];
				$data['markedbygroup'][] = $user['User'];
			}
			if($activity['state_id'] == 5) {
				$user = $this->User->find('first',array('conditions'=>array('uqid'=>$activity['meta']),'recursive'=>1));
				$data['moderated'] = $user['User'];
			}
		}
		return $data;
	}
	
	function getRubricMarks($submission,$marks) {
		$rubrics = $this->Rubric->find('all',array('conditions'=>array('project_id'=>$submission['Project']['id'])));
		$rubricmarks = array();
		foreach($rubrics as $rubric) {
			$themark = '';
			if(isset($marks->marks)) {
				foreach($marks->marks as $mark) {
					if($rubric['Rubric']['id'] == $mark->rubric_id) {
						$themark = $mark->value;
					}
				}
			}
			if($rubric['Rubric']['type'] == 'table') {
				$rubricset = json_decode($rubric['Rubric']['meta']);
				if(isset($rubricset[$themark])) {
					$themark = $rubricset[$themark]->name.' ('.$rubricset[$themark]->description.')';
				} else {
					$themark = 'Unrecorded';
				}
			}
			$rubricmarks[] = array('name'=>$rubric['Rubric']['name'],'value'=>$themark);
		}
		return $rubricmarks;
	}
	
	function annotations($submission_id,$onlyaudio=false) {
	    $file = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
		$file = $path.'/annots/annots.json';
		if($file) {
		    if (file_exists($file) && is_readable ($file)) {
		    	$annotationdata = json_decode(file_get_contents($file));
		    	foreach($annotationdata as $key=>&$annotationdatafile) {
			    	if(isset($annotationdatafile->type) && isset($annotationdatafile->filename) && $annotationdatafile->type == 'Recording') {
			    		$audiofile = str_replace('.m4a','.mp3',$path.'/annots/'.$annotationdatafile->filename);
			    		if(file_exists($audiofile)) {
						   	$annotationdatafile->duration = $this->getAudioDuration($audiofile);
						}
			   		} else {
			   			if($onlyaudio) {
				   			unset($annotationdata[$key]);
				   		}
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
	
	function allannotations($submission_id,$onlyaudio=false) {
	    $potentialversions = $this->Version->find('all',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'asc')));
	    $versions = array();
	    foreach($potentialversions as $potentialversion) {
	    	$marker = json_decode($potentialversion['Version']['meta']);
		    $versions[$marker->submitted_by] = $potentialversion;
	    }
	    $annotations = array();
	    foreach($versions as $marker=>$version) {
		    $newannots = $this->annotationsForVersion($submission_id,$version['Version']['id'],$onlyaudio);
		    foreach($newannots as &$newannot) {
			    $newannot->marker = $marker;
		    }
		    $annotations = array_merge($annotations,$newannots);
	    }
	    return $annotations;
	}
	
	function annotationsForVersion($submission_id,$version_id,$onlyaudio=false) {
	    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$version_id,'submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
		$path = $this->versionsdir.$submission_id.'/'.$file['Version']['path'];
		$file = $path.'/annots/annots.json';
		if($file) {
		    if (file_exists($file) && is_readable ($file)) {
		    	$annotationdata = json_decode(file_get_contents($file));
		    	foreach($annotationdata as $key=>&$annotationdatafile) {
			    	if(isset($annotationdatafile->type) && isset($annotationdatafile->filename) && $annotationdatafile->type == 'Recording') {
			    		$audiofile = str_replace('.m4a','.mp3',$path.'/annots/'.$annotationdatafile->filename);
			    		if(file_exists($audiofile)) {
						   	$annotationdatafile->duration = $this->getAudioDuration($audiofile);
						}
			   		} else {
			   			if($onlyaudio) {
				   			unset($annotationdata[$key]);
				   		}
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
	
	function marks($submission_id,$file_id=false) {
		if($file_id) {
		    $file = $this->Version->find('first',array('conditions'=>array('Version.id'=>$file_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
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
	
	function allmarks($submission_id) {
		$sub = $this->Submission->find('first',array('conditions'=>array('Submission.id'=>$submission_id),'recursive'=>0));
		$versions = array();
		if($sub['Project']['option_multiple_markers'] == 1) {
			$versiondatas = $this->Version->find('all',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
			foreach($versiondatas as $version) {
				$submitted = json_decode($version['Version']['meta']);
				if(isset($submitted->submitted_by)) {
					$versions[$submitted->submitted_by] = $version;
				}
			}
		} else {
			$versiondata = $this->Version->find('first',array('conditions'=>array('submission_id'=>$submission_id),'recursive'=>-1,'order'=>array('Version.updated'=>'desc')));
			$submitted = json_decode($versiondata['Version']['meta']);
			if(isset($submitted->submitted_by)) {
				$versions[$submitted->submitted_by] = $versiondata;
			} else {
				$versions[] = $versiondata;
			}
		}
		$marks = array();
		foreach($versions as $name=>$version) {
			$path = $this->versionsdir.$submission_id.'/'.$version['Version']['path'];
			$file = $path.'/marks.json';
			if($file) {
			    if (file_exists($file) && is_readable ($file)) {
				    $marks[$name] = json_decode(file_get_contents($file));
			    } else {
			    	$marks[$name] = new Object();
			    }
			} else {
				$marks[$name] = new Object();
			}
		}
		return $marks;
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
