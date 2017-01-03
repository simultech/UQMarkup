<?php
App::uses('AppModel', 'Model');
/**
 * Project Model
 *
 * @property Course $Course
 * @property Ruberic $Ruberic
 * @property Submission $Submission
 * @property Tag $Tag
 */
class Project extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Projects';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';
	
	var $validate = array(
        'name' => array(
            'rule'    => 'notBlank',
            'message' => 'Must have a project name'
        ),
        'description' => array(
            'rule'    => 'notBlank',
            'message' => 'Must have a project description'
        ),
        'start_date' => array(
        	'rule'       => array('date', 'dmy'),
        	'message'    => 'Enter a valid date in DD-MM-YY format.',
        	'allowEmpty' => false
        ),
        'submission_date' => array(
        	'rule'       => array('date', 'dmy'),
        	'message'    => 'Enter a valid date in DD-MM-YY format.',
        	'allowEmpty' => false
        ),
        'end_date' => array(
        	'rule'       => array('date', 'dmy'),
        	'message'    => 'Enter a valid date in DD-MM-YY format.',
        	'allowEmpty' => false
        ),
    );


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id',
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
		'Rubric' => array(
			'className' => 'Rubric',
			'foreignKey' => 'project_id',
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
		'Submission' => array(
			'className' => 'Submission',
			'foreignKey' => 'project_id',
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
		'Tag' => array(
			'className' => 'Tag',
			'foreignKey' => 'project_id',
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
		if (!empty($this->data['Project']['start_date'])) {
	    	$this->data['Project']['start_date'] = $this->dateFormatBeforeSave($this->data['Project']['start_date']);
    	}
    	if (!empty($this->data['Project']['submission_date'])) {
	    	$this->data['Project']['submission_date'] = $this->dateFormatBeforeSave($this->data['Project']['submission_date']);
    	}
    	if (!empty($this->data['Project']['end_date'])) {
	    	$this->data['Project']['end_date'] = $this->dateFormatBeforeSave($this->data['Project']['end_date']);
    	}
		return true;
	}

	function dateFormatBeforeSave($dateString) {
		return date('Y-m-d', strtotime($dateString)); // Direction is from 
	}

}
