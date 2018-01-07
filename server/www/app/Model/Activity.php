<?php
App::uses('AppModel', 'Model');
/**
 * Activity Model
 *
 * @property CourseRoleUsers $CourseRoleUsers
 * @property State $State
 * @property Submission $Submission
 * @property Mark $Mark
 */
class Activity extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Activities';
	

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CourseRoleUser' => array(
			'className' => 'CourseRoleUser',
			'foreignKey' => 'course_role_users_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'State' => array(
			'className' => 'State',
			'foreignKey' => 'state_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Submission' => array(
			'className' => 'Submission',
			'foreignKey' => 'submission_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Mark' => array(
			'className' => 'Mark',
			'foreignKey' => 'activity_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	public function beforeSave($options = array()) {
		if(isset($this->data['Activity']['meta'])) {
			$this->data['Activity']['meta'] = strtolower($this->data['Activity']['meta']);
		}
		return true;
	}

}
