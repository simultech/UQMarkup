<?php
App::uses('AppModel', 'Model');
/**
 * CourseRoleUser Model
 *
 * @property Role $Role
 * @property Course $Course
 * @property User $User
 */
class CourseRoleUser extends AppModel {

/**
 * Use table
 *
 * @var mixed False or table name
 */
	public $useTable = 'Course_Role_Users';


	public $validate = array( 
		"user_id"=>array( 
        	"unique"=>array( 
            	"rule"=>array("checkUnique", array("user_id", "course_id")), 
                "message"=>"This user is already associated with this course" 
            ) 
        ) 
    );
	

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Role' => array(
			'className' => 'Role',
			'foreignKey' => 'role_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Course' => array(
			'className' => 'Course',
			'foreignKey' => 'course_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	function checkUnique($data, $fields) { 
                if (!is_array($fields)) { 
                        $fields = array($fields); 
                } 
                foreach($fields as $key) { 
                        $tmp[$key] = $this->data[$this->name][$key]; 
                } 
                if (isset($this->data[$this->name][$this->primaryKey])) { 
                        $tmp[$this->primaryKey] = "<>".$this->data[$this->name][$this->primaryKey]; 

                } 
                return $this->isUnique($tmp, false); 
        } 
}


