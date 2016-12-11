<?php
App::uses('Version', 'Model');

/**
 * Version Test Case
 *
 */
class VersionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.version',
		'app.attachment',
		'app.submission',
		'app.project',
		'app.course',
		'app.course_role_user',
		'app.role',
		'app.user',
		'app.log',
		'app.logtype',
		'app.course_role',
		'app.ruberic',
		'app.mark',
		'app.activity',
		'app.course_role_users',
		'app.state',
		'app.tag'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Version = ClassRegistry::init('Version');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Version);

		parent::tearDown();
	}

}
