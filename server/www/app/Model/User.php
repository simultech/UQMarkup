<?php
App::uses('AppModel', 'Model');

class User extends AppModel {

	public $useTable = 'Users';

	public $displayField = 'name';
	
	 
    var $validate = array(
        'uqid' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This user already exists'
            ),
            'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'message' => 'Invalid user ID'
            )
        )
    );

	public $hasMany = array(
		'Log' => array(
			'className' => 'Log',
			'foreignKey' => 'user_id',
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

	/*public $hasAndBelongsToMany = array(
		'CourseRole' => array(
			'className' => 'CourseRole',
			'joinTable' => 'Course_Role_Users',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'course_role_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);*/

}
