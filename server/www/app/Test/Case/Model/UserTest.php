<?php
App::uses('User', 'Model');

/**
 * User Test Case
 *
 */
class UserTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user',
		'app.log',
		'app.logtype',
		'app.submission',
		'app.project',
		'app.course',
		'app.course_role_user',
		'app.role',
		'app.ruberic',
		'app.mark',
		'app.activity',
		'app.course_role_users',
		'app.state',
		'app.tag',
		'app.attachment',
		'app.version',
		'app.course_role'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('User');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->User);

		parent::tearDown();
	}

}
