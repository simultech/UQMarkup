<?php
App::uses('Submission', 'Model');

/**
 * Submission Test Case
 *
 */
class SubmissionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.submission',
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
		'app.tag',
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
		$this->Submission = ClassRegistry::init('Submission');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Submission);

		parent::tearDown();
	}

}
