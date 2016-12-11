<?php
App::uses('AppModel', 'Model');
/**
 * Mark Model
 *
 * @property Activity $Activity
 * @property Ruberic $Ruberic
 */
class Mark extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Marks';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Activity' => array(
			'className' => 'Activity',
			'foreignKey' => 'activity_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Rubric' => array(
			'className' => 'Rubric',
			'foreignKey' => 'rubric_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
