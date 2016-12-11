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
class Adminuser extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Adminusers';

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

}
