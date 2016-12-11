<?php
App::uses('Tag', 'Model');

/**
 * Tag Test Case
 *
 */
class TagTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.tag',
		'app.project',
		'app.course',
		'app.course_role_user',
		'app.role',
		'app.user',
		'app.ruberic',
		'app.mark',
		'app.activity',
		'app.course_role_users',
		'app.state',
		'app.submission',
		'app.attachment',
		'app.version',
		'app.log',
		'app.logtype'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Tag = ClassRegistry::init('Tag');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Tag);

		parent::tearDown();
	}

}
