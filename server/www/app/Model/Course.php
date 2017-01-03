<?php
App::uses('AppModel', 'Model');
/**
 * Course Model
 *
 * @property CourseRoleUser $CourseRoleUser
 * @property Project $Project
 */
class Course extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Courses';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';


	var $validate = array(
		'coursecode' => array(
			'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'message' => 'Invalid course ID'
            ),
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Required'
            )
		),
		'year' => array(
			'numeric' => array(
				'rule' => 'numeric',
				'message' => 'Must be a valid year',
			),
		),
		'semester' => array(
			'numeric' => array(
				'rule' => 'numeric',
				'message' => 'Must be a valid numeric semester',
			),
		),
        'uid' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This course already exists'
            ),
            'notBlank' => array(
                'rule' => 'notBlank',
                'message' => 'Required'
            )
        ),
        'name' => array(
            'rule'    => 'notBlank',
            'message' => 'Required'
        ),
    );

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'CourseRoleUser' => array(
			'className' => 'CourseRoleUser',
			'foreignKey' => 'course_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Project' => array(
			'className' => 'Project',
			'foreignKey' => 'course_id',
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

}
