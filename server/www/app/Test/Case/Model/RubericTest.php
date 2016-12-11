<?php
App::uses('Ruberic', 'Model');

/**
 * Ruberic Test Case
 *
 */
class RubericTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.ruberic',
		'app.project',
		'app.course',
		'app.course_role_user',
		'app.role',
		'app.user',
		'app.submission',
		'app.tag',
		'app.mark',
		'app.activity',
		'app.course_role_users',
		'app.state'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Ruberic = ClassRegistry::init('Ruberic');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Ruberic);

		parent::tearDown();
	}

}
