<?php

class LdapComponent extends Component {
	
	var $controller;
	var $userdetails = array();
	var $loggedin = false;
	var $loginroute = '/users/login';
	var $referer = '/';
	var $admins = array('uqsngo1');
	var $adminemails = array();
	var $basedn = '';
	var $ldapmin = array();
	var $semester = 2;
	var $sessionhash = '';
	var $ldapserver = '';
	
	function initialize(Controller $controller) {

		$this->adminemails = Configure::read('ldap_adminemails');
		$this->basedn = Configure::read('base_dn');
		$this->ldapmin = Configure::read('ldap_ldapmin');
		$this->ldapserver = Configure::read('ldap_server');
		
		$this->controller = $controller;
		if($this->controller->Session->check('Auth.userdetails')) {
			$this->loggedin = true;
			$this->userdetails = $this->controller->Session->read('Auth.userdetails');
			$this->sessionhash = $this->controller->Session->read('Auth.sessionhash');
		}
	}
	
	public function isCourseCoordinator($courseuid) {
		$user_id = $this->getUserIDForUQLogin($this->getUQID());
		$role_id = $this->controller->getRoleID('Course Coordinator');
		$course = $this->controller->Course->find('first',array('conditions'=>array('uid'=>$courseuid),'recursive'=>-1));
		$course_id = $course['Course']['id'];
		
		$count = $this->controller->CourseRoleUser->find('count',array('conditions'=>array('role_id'=>$role_id,'course_id'=>$course_id,'user_id'=>$user_id)));
		if($count > 0) {
			return true;
		}
		return false;
	}
	
	public function getUserIDForUQLogin($uqid) {
		$existinguser = $this->controller->User->find('first',array('conditions'=>array('uqid'=>$uqid)));
		//If a user exists
		if(!empty($existinguser)) {
			return $existinguser['User']['id'];
		} else {
			//Create the user
			$details = $this->lookupuser($uqid);
			if($details['count'] > 0) {
				$userdata = array();
				$userdata['uqid'] = $uqid;	
				$userdata['name'] = $details[0]['cn'][0];
				$userdata['email'] = $details[0]['mail'][0];
				$this->controller->User->create();
				if($this->controller->User->save($userdata)) {
					return $this->controller->User->id;
				}
			}
			return -1;
		}
		return -1;
	}
	
	public function isAdmin() {
		if(in_array($this->getUQID(), $this->admins)) {
			if($this->getUQID() == 'uqadekke' && $this->controller->debugmode) {
				$this->controller->debug = true;
			}
			return true;
		} else {
			return false;
		}
	}
	
	public function getUQID() {
		if(isset($this->userdetails[0])) {
			if(is_object($this->userdetails[0])) {
				return $this->userdetails[0]->uid->{0};
			}
			return $this->userdetails[0]['uid'][0];
		} else {
			return '';
		}
	}
	
	public function getUserID() {
		return $this->getUserIDForUQLogin($this->getUQID());
	}
	
	public function referer() {
		if($this->controller->Session->check('Auth.referer')) {
			return $this->controller->Session->read('Auth.referer');
		} else {
			return $this->referer;
		}
	}
	
	public function auth() {
		if(!$this->loggedin()) {
			$this->setReferrer();
			$this->controller->redirect($this->loginroute);
		}
	}
	
	public function setReferrer() {
		if(strpos($this->controller->request->here, "/users/login") === false && strpos($this->controller->request->here, "/pages/ethicalclearance") === false) {
			$this->controller->Session->write('Auth.referer',str_replace($this->controller->request->base,"",$this->controller->request->here));
		}
	}
	
	public function loggedin() {
		if(!$this->loggedin) {
			$this->setReferrer();
		}
		$this->controller->set('loggedIn',true);
		$this->controller->set('userid',$this->getUQID());
		return $this->loggedin;
	}
	
	public function getClassList($coursecode,$year,$semester) {
		$connection = ldap_connect($this->ldapserver);
		$ldapusers = array();
	 	if(ldap_bind($connection,$this->ldapmin['dn'],$this->ldapmin['pw'])) {
	 		$result = ldap_search($connection,$this->basedn,"(classes=$coursecode/$year/$semester)");
	 		$ldapusers = ldap_get_entries($connection,$result);
	 		ldap_unbind($connection);
	 	}
	 	unset($ldapusers['count']);
	 	$users = array();
	 	foreach($ldapusers as $ldapuser) {
	 		$user = array();
	 		$user['uqid'] = $ldapuser['uid'][0];
	 		$user['email'] = $ldapuser['mail'][0];
	 		$user['name'] = $ldapuser['cn'][0];
	 		$users[] = $user;
	 	}
	 	return $users;
	}
	
	function lookupuser($uqid) {
		$connection = ldap_connect($this->ldapserver);
		$userdetails = array();
	 	if(ldap_bind($connection,$this->ldapmin['dn'],$this->ldapmin['pw'])) {
	 		$result = ldap_search($connection,$this->basedn,"(uid=$uqid)");
		 	$userdetails = ldap_get_entries($connection,$result);
	 		ldap_unbind($connection);
	 	}
	 	return $userdetails;
	}
	
	public function login($username,$password,$rememberme=false) {
	
	
		if (Configure::read('ldap_fake')) {
			$dummydata = '{"count":1,"0":{"modifytimestamp":{"count":1,"0":"20120808061650Z"},"0":"modifytimestamp","modifiersname":{"count":1,"0":"uid=bb_admin,ou=special,o=the university of queensland,c=au"},"1":"modifiersname","title":{"count":1,"0":"UQ Research Scholarship"},"2":"title","classgroupcode":{"count":1,"0":"Scholar\/Scholar"},"3":"classgroupcode","positionnumber":{"count":1,"0":"2080952"},"4":"positionnumber","employmentlevel":{"count":1,"0":"Scholar"},"5":"employmentlevel","uqohsstatus":{"count":2,"0":"OHSB01-20120302104342","1":"OHSB09-20120302000000"},"6":"uqohsstatus","sipassupdate":{"count":1,"0":"0"},"7":"sipassupdate","uqpasswordchange":{"count":1,"0":"2012-04-29"},"8":"uqpasswordchange","uqpasswordexpirywarning":{"count":1,"0":" "},"9":"uqpasswordexpirywarning","uqpasswordexpired":{"count":1,"0":"0"},"10":"uqpasswordexpired","mailquota":{"count":1,"0":"4096"},"11":"mailquota","firstname":{"count":1,"0":"Andrew"},"12":"firstname","edupersonorgunitdn":{"count":1,"0":"ou=Information Technology and Electrical Engineering,ou=Engineering Physical Sciences and Architecture,ou=Executive Dean Engineering Physical Sciences and Architecture,ou=Senior Deputy Vice-Chancellor,ou=Vice-Chancellor,o=The University of Queensland,c=AU"},"13":"edupersonorgunitdn","memberof":{"count":1,"0":"ou=Information Technology and Electrical Engineering,ou=Engineering Physical Sciences and Architecture,ou=Executive Dean Engineering Physical Sciences and Architecture,ou=Senior Deputy Vice-Chancellor,ou=Vice-Chancellor,o=The University of Queensland,c=AU"},"14":"memberof","edupersonprimaryorgunitdn":{"count":1,"0":"ou=Information Technology and Electrical Engineering,ou=Engineering Physical Sciences and Architecture,ou=Executive Dean Engineering Physical Sciences and Architecture,ou=Senior Deputy Vice-Chancellor,ou=Vice-Chancellor,o=The University of Queensland,c=AU"},"15":"edupersonprimaryorgunitdn","uqrhdemployeenumber":{"count":1,"0":"40124591"},"16":"uqrhdemployeenumber","businesscategory":{"count":2,"0":"Staff Download","1":"StaffInternet"},"17":"businesscategory","mailhost":{"count":1,"0":"exchange.uq.edu.au"},"18":"mailhost","mailhsmstate":{"count":1,"0":"Hard Off"},"19":"mailhsmstate","mailhsmsla":{"count":1,"0":"hsm_holding"},"20":"mailhsmsla","nickname":{"count":1,"0":"Andrew"},"21":"nickname","pub-displayname":{"count":1,"0":"Mr Andrew Dekker"},"22":"pub-displayname","cn":{"count":1,"0":"Mr Andrew Dekker"},"23":"cn","ouname":{"count":1,"0":"Information Technology and Electrical Engineering"},"24":"ouname","ou":{"count":1,"0":"Information Technology and Electrical Engineering"},"25":"ou","createtimestamp":{"count":1,"0":"20080218212848Z"},"26":"createtimestamp","creatorsname":{"count":1,"0":"uid=si_colldb_admin,ou=special,o=the university of queensland,c=au"},"27":"creatorsname","prism":{"count":1,"0":"205389"},"28":"prism","mailuserstatus":{"count":1,"0":"active"},"29":"mailuserstatus","mailservice":{"count":1,"0":"EXN"},"30":"mailservice","gender":{"count":1,"0":"Male"},"31":"gender","mail":{"count":1,"0":"a.dekker@uq.edu.au"},"32":"mail","uqmail":{"count":1,"0":"a.dekker@uq.edu.au"},"33":"uqmail","labeleduri":{"count":1,"0":"http:\/\/dingo.uq.edu.au\/~uqsngo1\/"},"34":"labeleduri","personaltitle":{"count":1,"0":"Mr"},"35":"personaltitle","sn":{"count":1,"0":"Dekker"},"36":"sn","employeenumber":{"count":1,"0":"0000048933"},"37":"employeenumber","givenname":{"count":1,"0":"Andrew James"},"38":"givenname","pub-email":{"count":1,"0":"a.dekker@uq.edu.au"},"39":"pub-email","pub-description":{"count":1,"0":"Staff"},"40":"pub-description","pub-sn":{"count":1,"0":"Dekker"},"41":"pub-sn","uid":{"count":1,"0":"uqsngo1"},"42":"uid","gidnumber":{"count":1,"0":"50"},"43":"gidnumber","mailalternateaddress":{"count":3,"0":"a.dekker@uq.edu.au","1":"uqsngo1@uq.edu.au","2":"uqsngo1@newmailbox.uq.edu.au"},"44":"mailalternateaddress","maildeliveryoption":{"count":1,"0":"mailbox"},"45":"maildeliveryoption","employeetype":{"count":2,"0":"Staff","1":"StaffVisiting"},"46":"employeetype","dateofbirth":{"count":1,"0":"1983-08-06"},"47":"dateofbirth","objectclass":{"count":19,"0":"nswcalUser","1":"nsmessagingserveruser","2":"mailrecipient","3":"ipuser","4":"inetUser","5":"inetMailUser","6":"inetLocalMailRecipient","7":"userPresenceProfile","8":"nsManagedPerson","9":"top","10":"posixAccount","11":"uqSecurity","12":"uqPerson","13":"organizationalPerson","14":"uqStaff","15":"eduPerson","16":"inetOrgPerson","17":"person","18":"uqNetworkUser"},"48":"objectclass","bbcommunity":{"count":1,"0":"TEDI28C_5660_ESCS"},"49":"bbcommunity","postaladdress":{"count":1,"0":"INFO TECH AND ELECT ENG - St Lucia Campus"},"50":"postaladdress","departmentnumber":{"count":1,"0":"456"},"51":"departmentnumber","pub-ouname":{"count":1,"0":"Information Technology and Electrical Engineering"},"52":"pub-ouname","homequota":{"count":1,"0":"20971520"},"53":"homequota","loginshell":{"count":1,"0":"\/bin\/true"},"54":"loginshell","edupersonprimaryaffiliation":{"count":1,"0":"staff"},"55":"edupersonprimaryaffiliation","uqparentfaculty":{"count":1,"0":"EPSA"},"56":"uqparentfaculty","edupersonprincipalname":{"count":1,"0":"uqsngo1@uq.edu.au"},"57":"edupersonprincipalname","edupersonscopedaffiliation":{"count":1,"0":"staff@uq.edu.au"},"58":"edupersonscopedaffiliation","edupersonaffiliation":{"count":1,"0":"staff"},"59":"edupersonaffiliation","edupersonorgdn":{"count":1,"0":"o=The University of Queensland,c=AU"},"60":"edupersonorgdn","homedirectory":{"count":1,"0":"\/export\/home\/staff\/86\/uqsngo1"},"61":"homedirectory","uidnumber":{"count":1,"0":"304186"},"62":"uidnumber","uqorgrole":{"count":1,"0":"All ESS Users"},"63":"uqorgrole","securityworkgroup":{"count":1,"0":"EPSA_STAFF"},"64":"securityworkgroup","employmenttype":{"count":1,"0":"FULL"},"65":"employmenttype","mobile":{"count":1,"0":"0402 095 786"},"66":"mobile","superfund":{"count":1,"0":"UNISGC"},"67":"superfund","superfundname":{"count":1,"0":"UniSuper Accumulation 1 (Casual Only)"},"68":"superfundname","pub-phone":{"count":1,"0":"+61 7 3365 1657"},"69":"pub-phone","count":70,"dn":"prism=205389,ou=Staff,ou=People,o=The University of Queensland,c=AU"}}';
		
			$fakeinfo = get_object_vars(json_decode($dummydata));

			$this->loggedin = true;
			$this->userdetails = $fakeinfo;
			$this->sessionhash = substr(hash("sha512",rand()),0,44);
			$this->controller->Session->write('Auth.userdetails', $this->userdetails);
			$this->controller->Session->write('Auth.loggedin', $this->loggedin);
			$this->controller->Session->write('Auth.sessionhash', $this->sessionhash);
			return array($this->userdetails);
		}
	
	 	$connection = ldap_connect($this->ldapserver);
	 	if(ldap_bind($connection)) {

		 	$result = ldap_search($connection,$this->basedn,"(uid=$username)");
		 	$info = ldap_get_entries($connection,$result);
		 	ldap_unbind($connection);
		 	if($info['count'] > 0) {
			 	$userdn = $info[0]['dn'];
			 	$connection = ldap_connect($this->ldapserver);
			 	if(@ldap_bind($connection,$userdn,$password)) {
			 		if($rememberme) {
				 		Configure::write('Session.timeout',43200);
			 		}
			 		$result = ldap_search($connection,$this->basedn,"(uid=$username)");
			 		$info = ldap_get_entries($connection,$result);
			 		$this->loggedin = true;
			 		$this->userdetails = $info;
			 		//print_r(json_encode($info));
			 		//die();
			 		$this->sessionhash = substr(hash("sha512",rand()),0,44);
			 		$this->controller->Session->write('Auth.userdetails', $this->userdetails);
			 		$this->controller->Session->write('Auth.loggedin', $this->loggedin);
			 		$this->controller->Session->write('Auth.sessionhash', $this->sessionhash);
			 		return array($this->userdetails);
			 	} else {
			 		$this->destroysession();
				 	return array('error'=>'Invalid password');
			 	}
			 	//$result = ldap_search($connection,$this->basedn,"(prism=89133)");
			 	//$info = ldap_get_entries($connection,$result);
		 	} else {
		 		$this->destroysession();
			 	return array('error'=>'Invalid user');
		 	}
	 	} else {
	 		$this->destroysession();
	 		return array('error'=>'Could not connect');
	 	}
 	}
 	
 	function logout() {
	 	$this->destroysession();
 	}
 	
 	function destroysession() {
 		$this->loggedin = false;
 		$this->userdetails = array();
	 	$this->controller->Session->delete('Auth');
 	}
	
	function startup(Controller $controller) {
		
	}
	
	function beforeRender(Controller $controller) {
		
	}
	
	function shutdown(Controller $controller) {
		
	}
	
}

?>
