<?php
App::uses('AppModel', 'Model');
/**
 * Ruberic Model
 *
 * @property Project $Project
 * @property Mark $Mark
 * @property Tag $Tag
 */
class Rubric extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Rubrics';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
	
	public $order = 'Rubric.id';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'project_id',
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
	/*public $hasMany = array(
		'Mark' => array(
			'className' => 'Mark',
			'foreignKey' => 'rubric_id',
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
	);*/

}
