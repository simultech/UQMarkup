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
class Assignedstudent extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Assignedstudents';
	
	public $belongsTo = array(
		'Marker' => array(
			'className' => 'User',
			'foreignKey' => 'marker_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Student' => array(
			'className' => 'CourseRoleUser',
			'foreignKey' => 'courseroleuser_id',
			'conditions' => '',
			'fields' => 'user_id',
			'order' => ''
		)
	);


}
