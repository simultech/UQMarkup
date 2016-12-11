<?php
App::uses('CourseRoleUser', 'Model');

/**
 * CourseRoleUser Test Case
 *
 */
class CourseRoleUserTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.course_role_user',
		'app.role',
		'app.course',
		'app.user'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->CourseRoleUser = ClassRegistry::init('CourseRoleUser');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->CourseRoleUser);

		parent::tearDown();
	}

}
