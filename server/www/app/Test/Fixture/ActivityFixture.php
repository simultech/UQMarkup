<?php
/**
 * ActivityFixture
 *
 */
class ActivityFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'Activities';

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'course_role_users_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'state_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'submission_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'meta' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'latin1', 'collate' => 'latin1_swedish_ci', 'engine' => 'MyISAM')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'course_role_users_id' => 1,
			'state_id' => 1,
			'submission_id' => 1,
			'meta' => 1,
			'created' => '2012-08-01 18:14:32',
			'updated' => '2012-08-01 18:14:32'
		),
	);

}
