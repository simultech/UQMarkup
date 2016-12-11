<?php
App::uses('Logtype', 'Model');

/**
 * Logtype Test Case
 *
 */
class LogtypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.logtype',
		'app.log',
		'app.user',
		'app.submission'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Logtype = ClassRegistry::init('Logtype');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Logtype);

		parent::tearDown();
	}

}
