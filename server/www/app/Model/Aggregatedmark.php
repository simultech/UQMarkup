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
class Aggregatedmark extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Aggregatedmarks';

}
